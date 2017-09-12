<?php


class ThesisImpl implements Thesis {
	protected $fields;
	protected $metadata;
	protected $collection;
	protected $id;
	protected $files;

	function __construct() {
		$this->fields = Array();
		$this->metadata = Array();
		$this->files = Array();
	}
	//Returns a metadata field
	function getMetadata($metadataField){
		if (in_array($metadataField,$this->fields)){
			return $this->metadata[$metadataField];
		}else
			throw new Exception("Field '".$metadataField. "' not found");
			
	}
	//Set a metadata field
	function setMetadata($metadataField, $value){
		if (!in_array($metadataField,$this->fields)){
			array_push($this->fields, $metadataField);
		}
		$this->metadata[$metadataField]=$value;
	}
	//Get all metadata fields
	function getMedatataFields(){
		return $this->fields;
	}


	function getCollection(){
		return $this->collection;
	}
	function setCollection($collection){
		$this->collection=$collection;
	}

	//set and get for id
	function getId(){
		return $this->id;
	}

	function setId($id){
		$this->id=$id;
	}

	//set and get for files
	function getFiles(){
		return $this->files;
	}
	function addFile($path){
		array_push($this->files, $path);
	}
}

?>