<?php

class ItemImpl implements Item {
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
	function getMetadata($metadataField) {
		if (in_array($metadataField, $this->fields)) {
			if (!is_array($this->metadata[$metadataField])) {
				throw new Exception("Field '" . $metadataField . "' is not an array");

			}
			return $this->metadata[$metadataField];
		} else {
			throw new Exception("Field '" . $metadataField . "' not found");
		}

	}
	//Set a metadata field
	function setMetadata($metadataField, $array) {
		if (!in_array($metadataField, $this->fields)) {
			array_push($this->fields, $metadataField);
		}
		$this->metadata[$metadataField] = $array;
	}

	function addMetadata($metadataField, $value) {
		if (!in_array($metadataField, $this->fields)) {
			array_push($this->fields, $metadataField);
			$this->metadata[$metadataField] = Array();
		}
		array_push($this->metadata[$metadataField], $value);
	}

	//Get all metadata fields
	function getMedatataFields() {
		return $this->fields;
	}

	function hasMetadataField($field) {
		return in_array($field, $this->fields);
	}

	function getCollection() {
		return $this->collection;
	}
	function setCollection($collection) {
		$this->collection = $collection;
	}

	//set and get for id
	function getId() {
		return $this->id;
	}

	function setId($id) {
		$this->id = $id;
	}

	//set and get for files
	function getFiles() {
		return $this->files;
	}
	function addFile($path) {
		array_push($this->files, $path);
	}

	function metadataToString() {
		$output = "{\"metadata\":[";
		$first = 0;
		foreach ($this->getMedatataFields() as $field) {
			foreach ($this->getMetadata($field) as $value) {
				if ($first == 0) {
					$first = 1;
				} else {
					$output = $output . ',';
				}
				$output = $output . '{"key":"' . $field . '","value":"' . str_replace('"', '\"', $value) . '"}';
			}
		}
		$output = $output . "]}";
		return $output;
	}
}

?>