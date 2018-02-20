<?php

require 'classes'.DIRECTORY_SEPARATOR.'GenericItemSynchronizer.php';
require 'Pergamum2DSPaceThesisMetadataConverter.php';
require 'classes'.DIRECTORY_SEPARATOR.'Pergamum.php';
require 'classes'.DIRECTORY_SEPARATOR.'DSPace.php';
require 'classes'.DIRECTORY_SEPARATOR.'Observer.php';
use Zend\Config;

class Pergamum2DSpaceThesisSynchronizer extends GenericItemSynchronizer implements Observer {
	private $config;

	public function update($event) {
		echo $event . PHP_EOL;
	}

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
		$pergamum = new Pergamum($this->config['pergamum-ws-url']);
		$pergamum->setDefaultCollection($this->config['pergamum-default-collection']);
		$this->setOriginRepository($pergamum);
		$dspace = new DSpace($this->config['dspace-url'], $this->config['dspace-username'], $this->config['dspace-password']);
		$dspace->setBaseCommunity($this->config['dspace-thesis-community']);
		$dspace->register($this);
		$this->setTargetRepository($dspace);
	}
}
?>
