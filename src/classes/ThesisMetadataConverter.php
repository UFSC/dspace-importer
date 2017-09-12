<?php
require 'MetadataConverter.php';
require 'Thesis.php';
require 'ThesisImpl.php';
use Zend\Json;

class ThesisMetadataConverter implements MetadataConverter{
	
	private $map;

	function __construct($mapfile) {
		$reader = new Zend\Config\Reader\Json();
    	$this->map= $reader->fromFile($mapfile);
   }

   private function getConversion($field){
   		if (array_key_exists($field, $this->map)){
   				return $this->map[$field];
   		}else{
   			return "";
   		}
   }

	//returns all thesis available of a given year
	public function convert($object){
		$r = new ThesisImpl();
		if ($object instanceof ThesisImpl){
			foreach ($object->getMedatataFields() as $originField) {
				$targetField=$this->getConversion($originField);
				if ($targetField!=""){
					$r->setMetadata($targetField, $object->getMetadata($originField));
				}
			}
			return $r;
		}else{
			throw new Exception("ThesisMetadataConverter can convert only Thesis objects");
		}
	}
}
?>