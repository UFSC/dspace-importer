<?php

require 'Repository.php';
use Zend\Json;

class Pergamum implements Repository {
	const MARC_COLLECTION_FIELD = '710-b-1';

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
			$url = $url . "?" . $args;
		}
		$html = implode('', file($url)); // Return a String.
		$phpNative = Zend\Json\Encoder::encodeUnicodeString($html); // Encodes the String $html
		$teses = Zend\Json\Json::decode($phpNative, Zend\Json\Json::TYPE_ARRAY);
		$r = Array();
		for ($i = 0; $i <= sizeof($teses); $i++) {
			if (array_key_exists($i, $teses)) {
				$t = new ItemImpl();
				$acervo = $teses[$i]["cod_acervo"];
				$t->setId($acervo);
				if (array_key_exists("links", $teses[$i]) && $teses[$i]["links"] != "") {
					$t->addFile($teses[$i]["links"]);
				}
				//metadados
				$html = implode('', file("http://setic.sites.ufsc.br/pergamumws/marc.php?cod_acervo=" . $acervo)); // Returns a String.
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
					$value = html_entity_decode($camposMarc[$j]["descricao"]);
					$t->setMetadata($field, $value);
				}
				if ($t->hasMetadataField(self::MARC_COLLECTION_FIELD)) {
					$t->setCollection($t->getMetadata(self::MARC_COLLECTION_FIELD));
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