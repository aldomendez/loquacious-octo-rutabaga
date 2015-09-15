<?php

include "database.php";
ini_set('display_errors','off');
ini_set('date.timezone', 'America/Mexico_City');
ini_set('display_errors', '1');
error_reporting(E_ALL ^ E_NOTICE);

$db = new MxApps();
if ( isset($_GET['action']) ){
  if (function_exists($_GET['action'])) {
    $_GET['action']();
  }
}

function ingresar()
{

  global $db;

  $query = <<<QUERY
INSERT INTO temp_cycle_oven_log(
PROCESO,PRODUCTO,CARRIER,EMPLEADO,FECHA,NUM_CICLOS,TIEMPO_POR_CICLO,FLUJO_NIT,TEMP_HORNO,TEMP_CONGELADOR,COMENTARIOS,STATUS)
values('ENTRADA DEL MATERIAL','LR4','%carrier%','%empleado%',SYSDATE,'0','30 MIN','0','0','0','','RUNNING')
QUERY;
  
  if ( $_GET['carrier'] != '' && $_GET['empleado'] ){
    $query = str_replace('%carrier%', $_GET['carrier'], $query);
    $query = str_replace('%empleado%', $_GET['empleado'], $query);
    $ans = array('info' => 'ingresado');
    echo json_encode($ans);
    $db->insert($query);
    // echo $db->json();
  }    
}

function sacar()
{

  global $db;

  if ( $_GET['carrier'] != '' && $_GET['empleado'] ){
  
    $insert = <<<QUERY
INSERT INTO temp_cycle_oven_log(
PROCESO,PRODUCTO,CARRIER,EMPLEADO,FECHA,NUM_CICLOS,TIEMPO_POR_CICLO,FLUJO_NIT,TEMP_HORNO,TEMP_CONGELADOR,COMENTARIOS,STATUS)
values('SALIDA DEL MATERIAL','LR4','%carrier%','%empleado%',SYSDATE,'0','30 MIN','0','0','0','','COMPLETE')
QUERY;
    $insert = str_replace('%carrier%', $_GET['carrier'], $insert);
    $insert = str_replace('%empleado%', $_GET['empleado'], $insert);
    // $db->insert($insert);

    $insert = <<<QUERY
update temp_cycle_oven_log
set status = 'COMPLETE'
where carrier = '%carrier%'
and   proceso = 'ENTRADA DEL MATERIAL'
QUERY;
    $insert = str_replace('%carrier%', $_GET['carrier'], $insert);
    $ans = array('info' => 'sacado');
    echo json_encode($ans);
    $db->insert($insert);
  } 
}

function saveNextLoadData()
{
  global $db;

  if (file_put_contents('nextLoad.json', json_encode($_GET['data'])))
  {
    $ans = array('info' => "la informacion se proceso");
    echo json_encode($ans);
  }
}

function getNextLoadData()
{
  global $db;

  if ($ans = file_get_contents('nextLoad.json')) {
    echo $ans;
  } else {
    header('HTTP/1.1 500 Internal Server Error');
    $ans = array('error' => 'no se pudo cargar el archivo');
  }
  

}