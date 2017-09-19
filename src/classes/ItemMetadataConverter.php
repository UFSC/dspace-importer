<?php
require 'MetadataConverter.php';
require 'Item.php';
require 'ItemImpl.php';
use Zend\Json;

class ItemMetadataConverter implements MetadataConverter {

	private $map;

	function __construct($mapfile) {
		$reader = new Zend\Config\Reader\Json();
		$this->map = $reader->fromFile($mapfile);
	}

	private function getConversion($field) {
		if (array_key_exists($field, $this->map)) {
			return $this->map[$field];
		} else {
			return "";
		}
	}

	//returns all items available of a given year
	public function convert($object) {
		$r = new ItemImpl();
		if ($object instanceof ItemImpl) {
			foreach ($object->getMedatataFields() as $originField) {
				$targetField = $this->getConversion($originField);
				if ($targetField != "") {
					foreach ($object->getMetadata($originField) as $value) {
						$r->addMetadata($targetField, $value);
					}
				}
			}
			return $r;

		} else {
			throw new Exception("ItemMetadataConverter can convert only Item objects");
		}
	}
}
?>