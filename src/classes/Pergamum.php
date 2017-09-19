<?php

require 'Repository.php';
use Zend\Json;

class Pergamum implements Repository {
	const MARC_COLLECTION_FIELD = '710-b-1';
	const MARC_PARAGRAFO_ABSTRACT = "520";

	private $wsURL;
	private $defaultCollection;

	public function getAllItems($year) {
		return $this->getItemsFromWS("ano=" . $year);
	}

	function setDefaultCollection($collection) {
		$this->defaultCollection = $collection;
	}

	function __construct($wsURL) {
		$this->wsURL = $wsURL;
	}

	private function getItemsFromWS($args) {
		$url = $this->wsURL;
		if ($args != "") {
			$url = $url . "teses.php?" . $args;
		}
		$html = implode('', file($url)); // Return a String.
		$phpNative = Zend\Json\Encoder::encodeUnicodeString($html); // Encodes the String $html
		$teses = Zend\Json\Json::decode($phpNative, Zend\Json\Json::TYPE_ARRAY);
		$r = Array();
		for ($i = 0; $i <= sizeof($teses); $i++) {
			if (array_key_exists($i, $teses)) {
				$t = new ItemImpl();

				//id
				$acervo = $teses[$i]["cod_acervo"];
				$t->setId($acervo);

				//outros metadados no registro principal
				foreach ($teses[$i] as $key => $value) {
					$t->addMetadata($key, $value);
				}

				//arquivos
				if (array_key_exists("links", $teses[$i]) && $teses[$i]["links"] != "") {
					$t->addFile($teses[$i]["links"]);
				}

				//metadados MARC
				$html = implode('', file($this->wsURL . "marc.php?cod_acervo=" . $acervo)); // Returns a String.
				$phpNative = Zend\Json\Encoder::encodeUnicodeString($html);
				$camposMarc = Zend\Json\Json::decode($phpNative, Zend\Json\Json::TYPE_ARRAY);

				for ($j = 0; $j <= sizeof($camposMarc) - 1; $j++) {
					$field = $camposMarc[$j]["paragrafo"];
					if ($camposMarc[$j]["secao"] != '') {
						$field = $field . "-" . $camposMarc[$j]["secao"];
					}
					if ($camposMarc[$j]["seq_paragrafo"] != '') {
						$field = $field . "-" . $camposMarc[$j]["seq_paragrafo"];
					}
					//Abstract comes from another database field
					if ($camposMarc[$j]["paragrafo"] == self::MARC_PARAGRAFO_ABSTRACT) {
						$value = html_entity_decode($camposMarc[$j]["texto_descricao"]);
						//remove CR/LF from abstract
						$value = str_replace(chr(13), '', $value);
						$value = str_replace(chr(10), '', $value);
						$value = str_replace("", '', $value);
					} else {
						$value = html_entity_decode($camposMarc[$j]["descricao"]);
					}
					$t->addMetadata($field, $value);
				}

				//coleção
				if ($t->hasMetadataField(self::MARC_COLLECTION_FIELD)) {
					$t->setCollection(array_values($t->getMetadata(self::MARC_COLLECTION_FIELD))[0]);
				} else if ($this->defaultCollection != "") {
					$t->setCollection($this->defaultCollection);
				} else {
					throw new Exception("Item does not have default collection field '" . self::MARC_COLLECTION_FIELD . "' and there is no default collection configured. Use setDefaultCollection");
				}

				array_push($r, $t);
			}
		}
		return $r;
	}

	//returns a Thesis with a given id
	public function getItem($id) {
		return $this->getItemFromWS("acervo=" . $id);
	}

	//save a Thesis
	public function saveItem($t) {
		//TODO: implement this
		throw new Exception("Saving items on Pergamum not available yet");
	}
}
?>