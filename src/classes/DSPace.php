<?php

class DSPace implements Repository{
	
	private $thesisCommunity;
	private $thesisCollections;
	private $dspaceURL;

	function __construct($dspaceURL) {
		$this->dspaceURL=$dspaceURL;
	}

	//returns all thesis available of a given year
	public function getAllThesis($year){

	}
	
	//returns a Thesis with a given id
	public function getThesis($id){

	}

	public function setThesisCommunity($c){
		$this->thesisCommunity=$c;
	}

	private function generateXml($t){
		$doc             = new DOMDocument();
		$doc->version    = '1.0';
        $doc->encoding   = 'UTF-8';
        $doc->standalone = "no";
        $dublinCoreElement          = $doc->createElement('dublin_core');
        $dublinCoreAttribute        = $doc->createAttribute('schema');
        $dublinCoreAttribute->value = 'dc';
        $dublinCoreElement->appendChild($dublinCoreAttribute);
        $doc->appendChild($dublinCoreElement);

        foreach ($t->getMedatataFields() as $field) {
        	$value = $t->getMetadata($field);
            $dcvalueElement          = $doc->createElement('dcvalue', $value);
            $elements=explode('.', $field);
            foreach ($elements as $key => $value) {
            	$attr="";
            	switch ($key) {
            		case 0:
            			continue; //ignore dc
            		case 1:
            			$attr="element";
            			break;
            		case 2:
            			$attr="qualifier";
            			break;
            		case 3:
            			$attr="language";
            			break;
            		default:
            			continue;
            	}
            	if ($attr!="" && $value!=""){
	            	$dcvalueAttribute = $doc->createAttribute($attr);
	            	$dcvalueAttribute->value=$value;
	                $dcvalueElement->appendChild($dcvalueAttribute);
	                $dublinCoreElement->appendChild($dcvalueElement);
            	}
            }
        }
        $doc->formatOutput = true;
        return $doc;
	}

	private function saveUsingSimpleArchiveFormat($t){
		$xml=$this->generateXml($t);
		print_r($xml);
		$xml->save('teste.xml');
		$xml->save($diretorio_import . "$i/" . 'dublin_core.xml');
		//TODO: finish this implementation
	}

	private function restGet($url){
		$html = implode('', file($url)); // Return a String.
		$phpNative = Zend\Json\Encoder::encodeUnicodeString($html); // Encodes the String $html
		return Zend\Json\Json::decode($phpNative, Zend\Json\Json::TYPE_ARRAY); // Decode the encode returning an array with a thesis for each index.
	}
	
	private function getThesisCollections(){
		$url=$this->dspaceURL."/rest/communities/".$this->thesisCommunity."/collections";
		return $this->restGet($url);
	}
	
	
	private function getThesisCollection($name){
		if (!isset($this->thesisCollections)){
			$this->thesisCollections=$this->getThesisCollections();
		}
		
		foreach ($this->thesisCollections as $collection){
			if ($collection['name']==$name){
				return $collection['uuid'];
			}
		}
		return "";
	}


	private function createThesisCollection($name){
		$url=$this->dspaceURL."/rest/communities/".$this->thesisCommunity."/collections";
		$data='
{
  "name":"'.$name.'"
}';
		return restPost($url,$data);
	}	
	
	private function saveUsingRestApi($t){
		if (!isset($this->dspaceURL)){
			throw new Exception("Variable dspaceURL is not set.");
		}
		$collectionUUID=$this->getThesisCollection($t->getCollection());
		if ($collectionUUID==""){
			$collectionUUID=$this->createThesisCollection($t->getCollection());
		}
	}

	//save a Thesis
	public function saveThesis($t){
		if (!isset($this->thesisCommunity)){
			throw new Exception("Variable thesisCommunity is not set. Use setThesisCommunity");
		}
		
		$this->saveUsingRestApi($t);
		
	}
}
?>