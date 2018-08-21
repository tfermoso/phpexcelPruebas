<?php
session_start();
include "db.php";
include "vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php";

$jsondata = array();

if (isset($_POST['columnas'])) {

    $columnas = $_POST['columnas'];
    $jsondata[0] = $columnas[0];
    $jsondata[1] = $columnas[1];
    if ($_SESSION['file']) {
        $file = $_SESSION['file'];

        try {
            //Load the excel(.xls/.xlsx) file
            $objPHPExcel = PHPExcel_IOFactory::load($file);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($file, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        //An excel file may contains many sheets, so you have to specify which one you need to read or work with.
        $sheet = $objPHPExcel->getSheet(0);
        //It returns the highest number of rows
        $total_rows = $sheet->getHighestRow();
        //It returns the highest number of columns
        $total_columns = $sheet->getHighestColumn();
        $columns_db = "";
        $index = array();
        $index_ID = -1;
        $updates = "";
        for ($i = 0; $i < count($columnas); $i++) {
            if ($columnas[$i] != "") {
                if ($columnas[$i] == "id") {
                    $index_ID = $i;
                }
                $columns_db .= $columnas[$i] . ",";
                array_push($index, $i);
            }
        }
        $columns_db = substr($columns_db, 0, -1);

        $query = "insert into `usuarios` (#__columnas) VALUES ";
        //Loop through each row of the worksheet
        $query = str_replace('#__columnas', $columns_db, $query);
        $query_update = "UPDATE `usuarios` SET #_columnas WHERE `usuarios`.`id` = #_ID";
        $num_inserts = 0;
        $num_updates = 0;
        for ($row = 2; $row <= $total_rows; $row++) {
            //Read a single row of data and store it as a array.
            //This line of code selects range of the cells like A1:D1
            $single_row = $sheet->rangeToArray('A' . $row . ':' . $total_columns . $row, null, true, false);


            if ($index_ID >= 0) {
                $id_usuario = $single_row[0][$index_ID];
                $consulta = "Select * from usuarios where id=" . mysqli_real_escape_string($con, $id_usuario);
                mysqli_query($con, $consulta);
                if (mysqli_affected_rows($con) > 0) {
//Update
                    $set_column = "";
                    foreach ($single_row[0] as $key => $value) {
                        if (in_array($key, $index) && $index_ID != $key) {
                            $set_column .= "" . $columnas[$key] . "='" . mysqli_real_escape_string($con, $value) . "',";
                        }
                    }
                    $set_column = substr($set_column, 0, -1);
                    $upd = str_replace("#_ID", $id_usuario, $query_update);
                    $upd = str_replace("#_columnas", $set_column, $upd);
                    mysqli_query($con, $upd);
                    if (mysqli_affected_rows($con) > 0) {
                        $num_updates++;
                    }
                    
                } else {
//Insert
                    $query .= "(";
                    foreach ($single_row[0] as $key => $value) {
                        if (in_array($key, $index)) {
                            $query .= "'" . mysqli_real_escape_string($con, $value) . "',";
                        }
                    }
                    $query = substr($query, 0, -1);
                    $query .= "),";
                    $num_inserts++;
                }
            } else {
                $query .= "(";
                foreach ($single_row[0] as $key => $value) {
                    if (in_array($key, $index)) {
                        $query .= "'" . mysqli_real_escape_string($con, $value) . "',";
                    }
                }
                $query = substr($query, 0, -1);
                $query .= "),";
                $num_inserts++;
            }

        }
        $updates = substr($updates, 0, -1);
        $query = substr($query, 0, -1);
        if ($num_inserts > 0) {
            mysqli_query($con, $query);
            if (mysqli_affected_rows($con) > 0) {
                $jsondata["resultInsert"] = '<span class="msg">Database table updated! '.$num_inserts.'</span>';
            } else {
                $jsondata["resultInsert"] = '<span class="msg">Can\'t update database table! '.$num_inserts.' try again.</span>';
            }
        }
        $jsondata["resultUpdate"]='<span class="msg">Database updated! Número de actualizaciones: '.$num_updates.'</span>';
        $jsondata[2] = $query;
        $jsondata[3] = $updates;
    }
    //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($jsondata);
    exit();

}
