<?php

include "database.php";
ini_set('display_errors','off');
ini_set('date.timezone', 'America/Mexico_City');
ini_set('display_errors', '1');
error_reporting(E_ALL ^ E_NOTICE);

$db = new MxApps();
// echo "<pre>";
// print_r($_SERVER);

getData();

function getData()
{
    // Toma el valor maximo de la base de datos y lo devuelve como ID
    global $db;

    $query = <<<QUERY
SELECT
      proceso
    , PRODUCTO
    , carrier
    , empleado
    , To_Char(fecha,'Mon-Dd-YY'--, 'nls_date_language = spanish'
        ) fecha
    , To_Char(fecha,'HH24:MI') hora
    , num_ciclos
    , tiempo_por_ciclo
    , flujo_nit
    , temp_horno
    , temp_congelador
    , comentarios
    , status
    , Round(to_number(fecha- to_date('01-JAN-1970','DD-MON-YYYY'))* (24 * 60 * 60 * 1000)) sss
    , Round((SYSDATE - fecha)*1440) minutos
FROM
    TEMP_CYCLE_OVEN_LOG l 
WHERE
    STATUS = 'RUNNING'


QUERY;

    // $query = str_replace('%carrier%', $_GET['carrier'], $query);
    $db->query($query);
    // file_put_contents('actualData.json', $db->json());
    echo $db->json();
}