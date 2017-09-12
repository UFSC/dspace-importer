<?php

interface Repository{
	//returns all thesis available of a given year
	public function getAllThesis($year);

	//returns a Thesis with a given id
	public function getThesis($id);

	//save a Thesis
	public function saveThesis($t);
}
?>