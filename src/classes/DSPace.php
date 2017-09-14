<?php

use Zend\Http\Client;
use Zend\Http\Header\Cookie;
use Zend\Http\Request;

require 'Subject.php';

class DSPace implements Repository, Subject {

	const ITEM_KEY_FIELD = "dc.identifier.other";
	private $baseCommunity;
	private $collections;
	private $dspaceURL;
	private $dspaceRestCookie;
	private $dspaceUsername;
	private $dspacePassword;

	function __construct($dspaceURL, $dspaceUsername, $dspacePassword) {
		$this->dspaceURL = $dspaceURL;
		$this->dspaceUsername = $dspaceUsername;
		$this->dspacePassword = $dspacePassword;
	}

	//returns all items available of a given year
	public function getAllItems($year) {
		//TODO: implement this
	}

	//returns a item with a given id
	public function getItem($id) {
		//TODO
	}

	public function setBaseCommunity($c) {
		$this->baseCommunity = $c;
	}

	public function notify($event) {
		//TODO: implement observer
	}

	private function generateXml($t) {
		$doc = new DOMDocument();
		$doc->version = '1.0';
		$doc->encoding = 'UTF-8';
		$doc->standalone = "no";
		$dublinCoreElement = $doc->createElement('dublin_core');
		$dublinCoreAttribute = $doc->createAttribute('schema');
		$dublinCoreAttribute->value = 'dc';
		$dublinCoreElement->appendChild($dublinCoreAttribute);
		$doc->appendChild($dublinCoreElement);

		foreach ($t->getMedatataFields() as $field) {
			$value = $t->getMetadata($field);
			$dcvalueElement = $doc->createElement('dcvalue', $value);
			$elements = explode('.', $field);
			foreach ($elements as $key => $value) {
				$attr = "";
				switch ($key) {
				case 0:
					continue; //ignore dc
				case 1:
					$attr = "element";
					break;
				case 2:
					$attr = "qualifier";
					break;
				case 3:
					$attr = "language";
					break;
				default:
					continue;
				}
				if ($attr != "" && $value != "") {
					$dcvalueAttribute = $doc->createAttribute($attr);
					$dcvalueAttribute->value = $value;
					$dcvalueElement->appendChild($dcvalueAttribute);
					$dublinCoreElement->appendChild($dcvalueElement);
				}
			}
		}
		$doc->formatOutput = true;
		return $doc;
	}

	private function saveUsingSimpleArchiveFormat($t) {
		$xml = $this->generateXml($t);
		print_r($xml);
		$xml->save('teste.xml');
		$xml->save($diretorio_import . "$i/" . 'dublin_core.xml');
		//TODO: finish this implementation
	}

	private function restGet($url) {
		$html = implode('', file($url)); // Return a String.
		$phpNative = Zend\Json\Encoder::encodeUnicodeString($html); // Encodes the String $html
		return Zend\Json\Json::decode($phpNative, Zend\Json\Json::TYPE_ARRAY);
	}

	private function restAuth() {
		if (!isset($this->dspaceRestCookie)) {
			$url = $this->dspaceURL . "/rest/login";
			$data = "email=" . $this->dspaceUsername . "&password=" . $this->dspacePassword;
			$response = $this->restPost($url, $data);
			if ($response->getStatusCode() == 200) {
				$cookie = $response->getHeaders()->get('setcookie')->current()->getFieldValue();
				$this->dspaceRestCookie = substr($cookie, strpos($cookie, '=') + 1, strpos($cookie, ';') - strpos($cookie, '=') - 1);
			} else {
				throw new Exception("Fail login in rest API: " . $response->getContent());
			}
		}
	}

	private function restPost($url, $data) {
		//echo "URL: " . $url . PHP_EOL;
		//echo "Data: " . $data . PHP_EOL;
		$request = new Request();
		$request->setMethod(Request::METHOD_POST);
		$request->setUri($url);
		$request->setContent($data);
		$client = new Zend\Http\Client();
		if (isset($this->dspaceRestCookie)) {
			$request->getHeaders()->addHeader(new Cookie(array('JSESSIONID' => $this->dspaceRestCookie)));
			$request->getHeaders()->addHeaders(array(
				'accept' => 'application/json',
				'Content-Type' => 'application/json',
			));
		}
		$response = $client->send($request);
		if ($response->getStatusCode() != 200) {
			throw new Exception("Error Sending POST rest request at " . $url . " Error message: " . $response->getContent());

		}
		return $response;
	}

	private function getCollections() {
		$url = $this->dspaceURL . "/rest/communities/" . $this->baseCommunity . "/collections";
		return $this->restGet($url);
	}

	private function getCollection($name) {
		if (!isset($this->collections)) {
			$this->collections = $this->getCollections();
		}

		foreach ($this->collections as $collection) {
			if ($collection['name'] == $name) {
				return $collection['uuid'];
			}
		}
		return "";
	}

	private function getItemByField($field, $value) {
		$url = $this->dspaceURL . "/rest/items/find-by-metadata-field";
		$data = '{"key":"' . $field . '", "value":"' . $value . '"}';
		$response = $this->restPost($url, $data);
		$response = Zend\Json\Json::decode($response->getContent(), Zend\Json\Json::TYPE_ARRAY);
		return $response;
	}

	private function createItem($collectionUUID, $name) {
		$url = $this->dspaceURL . "/rest/collections/" . $collectionUUID . "/items";
		$data = '{"name":"' . $name . '"}';
		$response = $this->restPost($url, $json);
		$response = Zend\Json\Json::decode($response->getContent(), Zend\Json\Json::TYPE_ARRAY);
		return $response['uuid'];
	}

	private function updateItemMetadata($itemUUID, $newMetadata) {
		$url = $this->dspaceURL . "/rest/items/" . $itemUUID . "/metadata";
		$response = $this->restPost($url, $newMetadata);
		print_r($response);
		die;
		$response = Zend\Json\Json::decode($response->getContent(), Zend\Json\Json::TYPE_ARRAY);
		return $response['uuid'];
	}

	private function createCollection($name) {
		$url = $this->dspaceURL . "/rest/communities/" . $this->baseCommunity . "/collections";
		$data = '{"name":"' . $name . '"}';
		$response = $this->restPost($url, $data);

		//Invalidate collections cache
		unset($this->collections);
		$response = Zend\Json\Json::decode($response->getContent(), Zend\Json\Json::TYPE_ARRAY);
		$this->notify("Collection created:" . $response['uuid']);
		return $response['uuid'];
	}

	private function saveUsingRestApi($t) {
		if (!isset($this->dspaceURL)) {
			throw new Exception("Variable dspaceURL is not set.");
		}

		//Auth
		$this->restAuth();

		//Check target collection
		$collectionUUID = $this->getCollection($t->getCollection());
		if ($collectionUUID == "") {
			$collectionUUID = $this->createCollection($t->getCollection());
		}

		//Check if item exists
		$items = $this->getItemByField(self::ITEM_KEY_FIELD, $t->getId());
		if (count($items) == 0) {
			$itemUUID = $this->createItem($collectionUUID, $t->toString());
			$this->notify("Item created:" . $itemUUID);
		} else {
			//TODO: support many items
			$itemUUID = $items[0]['uuid'];
		}
		//update item metadata
		$this->updateItemMetadata($itemUUID, $t->metadataToString());

		//TODO: update item bitstreams

	}

	//save a Thesis
	public function saveItem($t) {
		if (!isset($this->baseCommunity)) {
			throw new Exception("Variable baseCommunity is not set. Use setBaseCommunity");
		}

		$this->saveUsingRestApi($t);

	}
}
?>