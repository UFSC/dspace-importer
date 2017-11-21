<?php

require 'Repository.php';
require 'PHPExcel\Classes\PHPExcel.php';
require 'PHPExcel\Classes\PHPExcel\IOFactory.php';

class XLS implements Repository {

	const XLS_COLLECTION_COLUMN = 'A';
	const XLS_ID_COLUMN = 'B';
	const XLS_DIR_COLUMN = 'H';
	const XLS_FILE_COLUMN = 'I';

	private $defaultCollection;

	public function getItemsByYear($year) {
		return $this->getItemsFromXLS("ano=" . $year);
	}

	public function getAllItems() {
		return $this->getItemsFromXLS();
	}

	function setDefaultCollection($collection) {
		$this->defaultCollection = $collection;
	}

	function __construct($path) {
		if (file_exists($path)) {
			$this->xlsPath = $path;
		} else {
			throw new Exception("XLS File does not exist: " . $path);
		}

	}

	private function getItemsFromXLS($filter_field = null, $filter_value = null) {
		$objPHPExcel = PHPExcel_IOFactory::load($this->xlsPath);
		$r = Array();
		foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
			//echo 'Worksheet - ', $worksheet->getTitle() . PHP_EOL;
			//skip empty wordksheet
			if ($worksheet->getCell('A1')->getCalculatedValue() == "") {
				continue;
			}
			foreach ($worksheet->getRowIterator() as $row) {
				//echo '    Row number - ', $row->getRowIndex() . PHP_EOL;
				//skip header
				if ($row->getRowIndex() == 1) {
					continue;
				}
				$t = new ItemImpl();
				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
				foreach ($cellIterator as $cell) {
					if (!is_null($cell)) {
						$value = $cell->getCalculatedValue();
						if ($value != "") {
							$t->addMetadata(substr($cell->getCoordinate(), 1, 1), $value);
						}
						//echo '       Cell - ', $cell->getCoordinate(), ' - ', $cell->getCalculatedValue(), EOL;
					}
				}
				$dir = $worksheet->getCell(self::XLS_DIR_COLUMN . $row->getRowIndex())->getCalculatedValue();
				$file = $worksheet->getCell(self::XLS_FILE_COLUMN . $row->getRowIndex())->getCalculatedValue();
				$t->addFile($dir . '/' . $file);

				$t->setCollection("TCC " . $worksheet->getCell(self::XLS_COLLECTION_COLUMN . $row->getRowIndex())->getCalculatedValue());
				$t->setId($worksheet->getCell(self::XLS_ID_COLUMN . $row->getRowIndex())->getCalculatedValue());
				array_push($r, $t);
			}
		}

		return $r;
	}

	//returns a Thesis with a given id
	public function getItem($id) {
		return $this->getItemsFromXLS("acervo=" . $id);
	}

	//save a Thesis
	public function saveItem($t) {
		throw new Exception("Saving items on XLS is not available");
	}
}
?>