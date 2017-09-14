<?php

interface Repository {
	//returns all thesis available of a given year
	public function getAllItems($year);

	//returns a Thesis with a given id
	public function getItem($id);

	//save a Thesis
	public function saveItem($t);
}
?>