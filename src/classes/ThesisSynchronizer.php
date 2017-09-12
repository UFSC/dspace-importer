<?php

interface ThesisSynchronizer{
	//geters and seters for the repository from where the thesis will be extracted
	public function getOriginRepository();
	public function setOriginRepository($o);

	//geters and seters for the repository to where the thesis will be imported
	public function getTargetRepository();
	public function setTargetRepository($t);

	//synchronize repositories of a specific year
	public function syncRepos($year);
}
?>