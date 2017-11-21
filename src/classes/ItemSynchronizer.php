<?php

interface ItemSynchronizer {
	//geters and seters for the repository from where the items will be extracted
	public function getOriginRepository();
	public function setOriginRepository($o);

	//geters and seters for the repository to where the items will be imported
	public function getTargetRepository();
	public function setTargetRepository($t);

	//synchronize repositories of a specific year
	public function syncReposByYear($year);
	//Sync entire repositories
	public function syncRepos();
}
?>