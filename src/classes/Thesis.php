<?php


interface Thesis {
	//Returns a metadata field
	function getMetadata($metadataField);
	//Set a metadata field
	function setMetadata($metadataField, $value);
	//Get all metadata fields
	function getMedatataFields();

	//set and get for collection
	function getCollection();
	function setCollection($collection);

	//set and get for id
	function getId();
	function setId($id);

	//set and get for files
	function getFiles();
	function addFile($path);
}

?>