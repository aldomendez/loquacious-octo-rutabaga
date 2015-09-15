<?php

include "database.php";
ini_set('display_errors','off');
ini_set('date.timezone', 'America/Mexico_City');
ini_set('display_errors', '1');
error_reporting(E_ALL ^ E_NOTICE);

$db = new MxOptix();
// echo "<pre>";
// print_r($_SERVER);

if (isset($_GET['carrier']) && $_GET['carrier'] !== ''){

	getData();

}


function getData()
{
	// Toma el valor maximo de la base de datos y lo devuelve como ID
	global $db;

	$query = <<<QUERY
	SELECT
  system_id,
  serial_num,
  pass_fail,
  process_date,
  step_name,
  cycle_time,
  carrier_serial_num,
  carrier_site,
  part_code_name,
  LENS_POS_T,
  LENS_POS_X,
  LENS_POS_Y,
  LENS_POS_Z,
  part_num
FROM lr4_shim_assembly 
WHERE carrier_serial_num = '%carrier%'
QUERY;

	$query = str_replace('%carrier%', $_GET['carrier'], $query);
	$db->query($query);
	echo $db->json();
}
