<?php

require 'classes\GenericItemSynchronizer.php';
require 'Pergamum2DSPaceThesisMetadataConverter.php';
require 'classes\Pergamum.php';
require 'classes\DSpace.php';
use Zend\Config;

class Pergamum2DSpaceThesisSynchronizer extends GenericItemSynchronizer {
	private $config;

	private function loadConfig($configFile) {
		if (!file_exists($configFile)) {
			throw new Exception($configFile . " does not exist");
		}
		$zc = new Zend\Config\Reader\Ini();
		$this->config = $zc->fromFile($configFile);
	}

	private function getMapFile() {
		$mapfile = $this->config['mapfile'];
		if ($mapfile == "") {
			throw new Exception("config.ini must have a mapfile specified");
		}
		if (!file_exists($mapfile)) {
			throw new Exception("Map file '" . $mapfile . "' does not exist");
		}
		return $mapfile;
	}

	function __construct($configFile) {
		$this->loadConfig($configFile);
		$this->setMetadataConverter(new Pergamum2DSPaceThesisMetadataConverter($this->getMapFile()));
		$this->setOriginRepository(new Pergamum($this->config['pergamum-ws-url']));
		$dspace = new DSpace($this->config['dspace-url'], $this->config['dspace-username'], $this->config['dspace-password']);
		$dspace->setBaseCommunity($this->config['dspace-thesis-community']);
		$this->setTargetRepository($dspace);
	}
}
?>