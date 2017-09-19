<?php
require 'classes\ItemMetadataConverter.php';

//Customizações para conversão entre Pergamum e DSpace
class Pergamum2DSPaceThesisMetadataConverter extends ItemMetadataConverter {
	const MARC_TITLE = '245-a-1';
	const MARC_SUBTITLE = '245-b-1';
	const DC_TITLE = 'dc.title';

	const MARC_FORMAT_A = '300-a-1';
	const MARC_FORMAT_B = '300-b-1';
	const DC_FORMAT = 'dc.format.extent';
	const DC_TYPE_FIELD = 'dc.type';

	//join MARC title and subtitle fields in a single dublin core field
	private function convertMarcTitle2DC($marc, $dc) {
		$fullTitle = array_values($marc->getMetadata(self::MARC_TITLE))[0];
		if ($marc->hasMetadataField(self::MARC_SUBTITLE)) {
			$fullTitle = $fullTitle . ": " . array_values($marc->getMetadata(self::MARC_SUBTITLE))[0];
		}

		$dc->setMetadata(self::DC_TITLE, array($fullTitle));
	}

	//join MARC format fields in a single dublin core field
	private function convertMarcFormat2DC($marc, $dc) {
		if ($marc->hasMetadataField(self::MARC_FORMAT_A)) {
			$format = array_values($marc->getMetadata(self::MARC_FORMAT_A))[0];
			if ($marc->hasMetadataField(self::MARC_FORMAT_B)) {
				$format = $format . "| " . array_values($marc->getMetadata(self::MARC_FORMAT_B))[0];
			}

			$dc->setMetadata(self::DC_FORMAT, array($format));
		}
	}

	private function formatType($r) {
		if ($r->hasMetadataField(self::DC_TYPE_FIELD)) {
			$type = array_values($r->getMetadata(self::DC_TYPE_FIELD))[0];
			$endIndex = strpos($type, ")");
			$type = substr($type, 0, $endIndex + 1);
			$r->setMetadata(self::DC_TYPE_FIELD, array($type));
		}
	}

	//returns all thesis available of a given year
	public function convert($object) {
		$r = parent::convert($object);
		//fix title
		$this->convertMarcTitle2DC($object, $r);
		//fix format field
		$this->convertMarcFormat2DC($object, $r);
		//format type field
		$this->formatType($r);
		return $r;
	}
}
?>