<?php

interface Item {
	//Returns a metadata field
	function getMetadata($metadataField);
	//add metadata field
	function addMetadata($metadataField, $value);
	//Set a metadata field
	function setMetadata($metadataField, $array);
	//Get all metadata fields
	function getMedatataFields();
	function hasMetadataField($field);

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