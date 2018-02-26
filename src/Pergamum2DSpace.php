<?php

	//set_include_path('/usr/share/php/libzend-framework-php/');
	//require_once 'Zend/Loader/Autoloader.php';
	require_once realpath('/home/dspace/vendor/autoload.php');
	require 'Pergamum2DSpaceThesisSynchronizer.php';


	function printUsage(){
		echo "Usage: Pergamum2DSpace.php -c <config file>".PHP_EOL;
	}

	$configFile="";
	$opts=getopt("c:");
	if (array_key_exists("c", $opts)){
		$configFile=$opts["c"];
	}else{
		printUsage();
		die;
	}

	$sync = new Pergamum2DSpaceThesisSynchronizer($configFile);
	$sync->syncReposByYear(date("Y")-1);
	$sync->syncReposByYear(date("Y"));
?>
