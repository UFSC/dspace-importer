<?php
require 'classes\ItemMetadataConverter.php';

class Pergamum2DSPaceThesisMetadataConverter extends ItemMetadataConverter {
	const MARC_TITLE = '245-a-1';
	const MARC_SUBTITLE = '245-b-1';
	const DC_TITLE = 'dc.title';

	const MARC_FORMAT_A = '300-a-1';
	const MARC_FORMAT_B = '300-b-1';
	const DC_FORMAT = 'dc.format.extent';

	//join MARC title and subtitle fields in a single dublin core field
	private function convertMarcTitle2DC($marc, $dc) {
		$tesesTitleA = $marc->getMetadata(self::MARC_TITLE);
		$tesesTitleB = $marc->getMetadata(self::MARC_SUBTITLE);
		if ($tesesTitleB != '') {
			$fullTitle = $tesesTitleA . ": " . $tesesTitleB;
		} else {
			$fullTitle = $tesesTitleA;
		}
		$dc->setMetadata(self::DC_TITLE, $fullTitle);
	}

	//join MARC format fields in a single dublin core field
	private function convertMarcFormat2DC($marc, $dc) {
		$formatA = $marc->getMetadata(self::MARC_FORMAT_A);
		$formatB = $marc->getMetadata(self::MARC_FORMAT_B);
		$dc->setMetadata(self::DC_FORMAT, $formatA . "| " . $formatB);
	}

	//returns all thesis available of a given year
	public function convert($object) {
		$r = parent::convert($object);
		//fix title
		$this->convertMarcTitle2DC($object, $r);
		$this->convertMarcFormat2DC($object, $r);
		return $r;
	}
}
?>