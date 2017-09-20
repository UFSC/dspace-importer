<?
error_reporting(32);
$server = "IP:INSTANCEPORT";
$database = "pergamarc";
$user = 'syspergamum';
$password = 'senhabdpergamum';

$db = mssql_connect($server, $user, $password) or die('MSSQL error: ' . mssql_get_last_message());

mssql_select_db( $database , $db ) or die ("Problema ao setar o DB do Pergamum: " . mssql_get_last_message());

foreach( $_GET as $key => $value )
  $$key = $value;


