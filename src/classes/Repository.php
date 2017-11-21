<?php

interface Repository {
	//returns all thesis available of a given year
	public function getItemsByYear($year);

	//returns all items available on the repository
	public function getAllItems();

	//returns a Thesis with a given id
	public function getItem($id);

	//save a Thesis
	public function saveItem($t);
}
?>