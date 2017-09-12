<?php
require 'ThesisSynchronizer.php';
class GenericThesisSynchronizer implements ThesisSynchronizer{
	private $origin;
	private $target;
	private $metadataConverter;
	//geters and seters for the repository from where the thesis will be extracted
	public function getOriginRepository(){
		return $this->origin;
	}
	public function setOriginRepository($o){
		$this->origin=$o;
	}

	//geters and seters for the repository to where the thesis will be imported
	public function getTargetRepository(){
		return $this->target;
	}
	public function setTargetRepository($t){
		$this->target=$t;
	}

	//geters and seters for the metadata converter
	public function getMetadataConverter(){
		return $this->metadataConverter;
	}
	public function setMetadataConverter($m){
		$this->metadataConverter=$m;
	}

	//synchronize repositories of a specific year
	public function syncRepos($year){
		if (!isset($this->metadataConverter)){
			throw Exception ("Metadata converter is not set. Use setMetadataConverter before calling this method");
		}

		$thesesList=$this->origin->getAllThesis($year);
		foreach ($thesesList as $thesisOrigin) {
			$thesisTarget = $this->metadataConverter->convert($thesisOrigin);
			$thesisTarget->setId($thesisOrigin->getId());
			$thesisTarget->setCollection($thesisOrigin->getCollection());
			foreach ($thesisOrigin->getFiles() as $file) {
				$thesisTarget->addFile($file);
			}
			$this->target->saveThesis($thesisTarget);
		}
	}
}
?>