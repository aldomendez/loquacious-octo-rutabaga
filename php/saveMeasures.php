<?php

include "database.php";
ini_set('display_errors','off');
ini_set('date.timezone', 'America/Mexico_City');
ini_set('display_errors', '1');
error_reporting(E_ALL ^ E_NOTICE);

$db = new MxApps();
// echo "<pre>";
// print_r($_POST);

if (isset($_POST['data']) && isset($_POST['action']) ){
  
  if ($_POST['action'] == 'save') {
    save_data();
  } else {
    update_data();
  }
}

function save_data()
{

  global $db;
  // Construye, los numeros de carrier que usaremos
  $values = array();
  foreach ($_POST['data'] as $key => $value) {
    array_push($values, array());
    array_push($values[$key], $value['SERIAL_NUM']);
    array_push($values[$key], $value['PART_CODE_NAME'] == 'LR4TosaGen2 Lens Only' ? 'TOSA' : 'ROSA');
    array_push($values[$key], $value['SYSTEM_ID']);
    array_push($values[$key], $value['CARRIER_SERIAL_NUM']);
    array_push($values[$key], $value['CARRIER_SITE']);
    array_push($values[$key], $value['PASS_FAIL']);
    array_push($values[$key], $value['MEAS_X']);
    array_push($values[$key], $value['MEAS_Y']);
    array_push($values[$key], $value['MEAS_T']);
  }

	$query = <<<QUERY

insert into medicion_alps
(
  serial_num,process,system_id,carrier,carrier_site,passfail,MEAS_X,MEAS_Y,MEAS_T
)
values(%values%)
QUERY;

  foreach ($values as $key => $value) {
    // print_r($value);
    $qry = str_replace('%values%', "'" . implode("','", $value) . "'", $query);
    echo $qry;
    $db->insert($qry);
    // echo $db->json();
  }
	
}
