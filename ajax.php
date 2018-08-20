<?php

$jsondata = array();

if( isset($_POST['columnas']) ) {

    $datos=$_POST['columnas'];
    $jsondata[0]=$datos[0];
    $jsondata[1]=$datos[1];

    //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($jsondata);
    exit();

}