<?php

use Zend\Http\Client;
use Zend\Http\Header\Cookie;
use Zend\Http\Request;

require 'Subject.php';

class DSPace implements Repository, Subject {

	const ITEM_KEY_FIELD = "dc.identifier.other";
	const ITEM_TITLE_FIELD = 'dc.title';
	private $baseCommunity;
	private $collections;
	private $dspaceURL;
	private $dspaceRestCookie;
	private $dspaceUsername;
	private $dspacePassword;
	private $observers;

	function __construct($dspaceURL, $dspaceUsername, $dspacePassword) {
		$this->dspaceURL = $dspaceURL;
		$this->dspaceUsername = $dspaceUsername;
		$this->dspacePassword = $dspacePassword;
		$this->observers = Array();
	}

	//returns all items available of a given year
	public function getItemsByYear($year) {
		//TODO: implement this
	}

	public function getAllItems() {
	}

	//returns a item with a given id
	public function getItem($id) {
		//TODO
	}

	public function setBaseCommunity($c) {
		$this->baseCommunity = $c;
	}

	public function register($observer) {
		$this->observers[] = $observer;
	}

	public function notify($event) {
		foreach ($this->observers as $obs) {
			$obs->update($event);
		}
	}

	/*private function generateXml($t) {
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
	}*/

	private function saveUsingSimpleArchiveFormat($t) {
		$xml = $this->generateXml($t);
		print_r($xml);
		$xml->save('teste.xml');
		$xml->save($diretorio_import . "$i/" . 'dublin_core.xml');
		//TODO: finish this implementation
	}

	private function restGet($url) {
		//Disable paging
		$url = $url . "?limit=10000000&itemsLimit=10000000";
		$html = implode('', file($url)); // Return a String.
		$phpNative = Zend\Json\Encoder::encodeUnicodeString($html); // Encodes the String $html
		return Zend\Json\Json::decode($phpNative, Zend\Json\Json::TYPE_ARRAY);
	}

	private function restGetZend($url) {
		$request = new Request();
		$request->setMethod(Request::METHOD_GET);
		//Disable paging
		$url = $url . "?limit=0&itemsLimit=0";
		$request->setUri($url);
		//$request->setContent($data);
		$client = new Zend\Http\Client();

		$request->getHeaders()->addHeaders(array(
			'limit' => '1000',
			'itemsLimit' => '1000',
		));

		$response = $client->send($request);
		if ($response->getStatusCode() != 200) {
			throw new Exception("Error Sending GET rest request at " . $url . " Error message: " . $response->getContent());
		}
		print_r($response);
		die;
		return Zend\Json\Json::decode($response->getContent(), Zend\Json\Json::TYPE_ARRAY);
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
		$request = new Request();
		$request->setMethod(Request::METHOD_POST);
		$request->setUri($url);
		if ($data != "") {
			//echo "Data: " . $data . PHP_EOL;
			$request->setContent($data);
		}
		$client = new Zend\Http\Client();
		$client->setOptions(array(
			'maxredirects' => 0,
			'timeout' => 600,
		));
		if (isset($this->dspaceRestCookie)) {
			$request->getHeaders()->addHeader(new Cookie(array('JSESSIONID' => $this->dspaceRestCookie)));
			$request->getHeaders()->addHeaders(array(
				'accept' => 'application/json',
			));
			$request->getHeaders()->addHeaders(array(
				'Content-Type' => 'application/json',
			));
		}
		$response = $client->send($request);
		if ($response->getStatusCode() != 200) {
			echo "Data: " . $data . PHP_EOL;
			throw new Exception("Error Sending POST rest request at " . $url . " Error message: " . $response->getContent() . PHP_EOL);
		}
		return $response;
	}

	private function restUpload($url, $file) {
		/* CURL version of upload
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Cookie: JSESSIONID=' . $this->dspaceRestCookie,
			));

			if (function_exists('curl_file_create')) {
				// php 5.5+
				$cFile = curl_file_create($file);
			} else {
				//
				$cFile = '@' . realpath($file);
			}
			$post = array('extra_info' => '123456', 'file_contents' => $cFile);
			echo "URL: " . $url . PHP_EOL;
			echo "Uploading File: " . $file . PHP_EOL;
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

			$result = curl_exec($ch);
			curl_close($ch);
			//echo PHP_EOL . "Resultado: " . $result . PHP_EOL;
		*/

		//echo "URL: " . $url . PHP_EOL;
		$client = new Zend\Http\Client();
		$client->setMethod(Request::METHOD_POST);
		$client->setUri($url);

		$client->setHeaders(array(
			'accept' => 'application/json',
		));
		if (isset($this->dspaceRestCookie)) {
			$client->addCookie('JSESSIONID', $this->dspaceRestCookie);
		}
		//echo "File: " . $file . PHP_EOL;
		$client->setFileUpload($file, basename($file));
		$response = $client->send();
		if ($response->getStatusCode() != 200) {
			throw new Exception("Error Sending POST UPLOAD rest request at " . $url . " Error message: " . $response->getContent());
		}
		return $response;
	}

	private function restDelete($url) {
		$request = new Request();
		$request->setMethod(Request::METHOD_DELETE);
		$request->setUri($url);
		$client = new Zend\Http\Client();
		if (isset($this->dspaceRestCookie)) {
			$request->getHeaders()->addHeader(new Cookie(array('JSESSIONID' => $this->dspaceRestCookie)));
		}
		$response = $client->send($request);
		if ($response->getStatusCode() != 200) {
			throw new Exception("Error Sending DELETE rest request at " . $url . " Error message: " . $response->getContent());
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
		$this->notify("Finding item '" . $value . "' by field '" . $field . "'");
		$url = $this->dspaceURL . "/rest/items/find-by-metadata-field";
		$data = '{"key":"' . $field . '", "value":"' . $value . '"}';
		$response = $this->restPost($url, $data);
		$response = Zend\Json\Json::decode($response->getContent(), Zend\Json\Json::TYPE_ARRAY);
		return $response;
	}

	private function deleteItem($itemUUID) {
		$url = $this->dspaceURL . "/rest/items/" . $itemUUID;
		$response = $this->restDelete($url);
		return $response;
	}

	private function createItem($collectionUUID, $data) {
		$url = $this->dspaceURL . "/rest/collections/" . $collectionUUID . "/items";
		$response = $this->restPost($url, $data);
		$response = Zend\Json\Json::decode($response->getContent(), Zend\Json\Json::TYPE_ARRAY);
		return $response['uuid'];
	}

	private function addItemMetadata($itemUUID, $newMetadata) {
		$url = $this->dspaceURL . "/rest/items/" . $itemUUID . "/metadata";
		$response = $this->restPost($url, $newMetadata);
		$response = Zend\Json\Json::decode($response->getContent(), Zend\Json\Json::TYPE_ARRAY);
		return $response['uuid'];
	}

	private function addItemBitstream($itemUUID, $file) {
		//Create bitstream
		$url = $this->dspaceURL . "/rest/items/" . $itemUUID . '/bitstreams?name=' . basename($file);
		$tempDir = "./tmp";
		if (!file_exists($tempDir)) {
			mkdir($tempDir);
		}
		$tempFile = $tempDir . "/" . basename($file);
		$filePDF = fopen($tempFile, "w+");
		fwrite($filePDF, file_get_contents($file));
		fclose($filePDF);

		$response = $this->restUpload($url, $tempFile);
		$response = Zend\Json\Json::decode($response->getContent(), Zend\Json\Json::TYPE_ARRAY);
		$bitstreamUUID = $response['uuid'];
		unlink($tempFile);
		return $bitstreamUUID;
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

	private function generateItemJson($i) {
		$output = "{\"metadata\":[";
		$first = 0;
		foreach ($i->getMedatataFields() as $field) {
			foreach ($i->getMetadata($field) as $value) {
				if ($first == 0) {
					$first = 1;
				} else {
					$output = $output . ',';
				}
				//Check if field has language component
				$language = "";
				if (substr_count($field, '.') > 2) {
					$pos = strrpos($field, '.');
					$language = substr($field, $pos + 1);
					$field = substr($field, 0, $pos);
				}

				$output = $output . '{"key":"' . $field . '","value":"' . str_replace("\t", '\t', str_replace("\n", '\n', str_replace('"', '\"', $value))) . '"';
				if ($language != "") {
					$output = $output . ',"language":"' . $language . '"';
				}
				$output = $output . '}';
			}
		}
		$output = $output . "]}";
		return $output;
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
			$this->notify("Creating item:" . $t->getId());
			$itemUUID = $this->createItem($collectionUUID, $this->generateItemJson($t));
			$this->notify("Item created:" . $itemUUID . " on collection " . $collectionUUID);

			//add bitstreams
			foreach ($t->getFiles() as $file) {
				$bitstreamUUID = $this->addItemBitstream($itemUUID, $file);
				$this->notify("Item bitstream added:" . $bitstreamUUID);
			}
		} else {
			//TODO: update item metadata
			$itemUUID = $items[0]['uuid'];
			//$this->addItemMetadata($itemUUID, $t->metadataToString());
		}

	}

	//save a Thesis
	public function saveItem($t) {
		if (!isset($this->baseCommunity)) {
			throw new Exception("Variable baseCommunity is not set. Use setBaseCommunity");
		}
		try {
			$this->saveUsingRestApi($t);
		} catch (Exception $e) {
			$this->notify("Error saving item: " . $e->getMessage());
		}

	}
}
?>