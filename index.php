<?php
session_start();
require 'functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="js/index.js"></script>
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.css">
</head>
<body>
    <h1>Importador datos excel</h1>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    Upload excel file :
    <input type="file" name="uploadFile" value="" />
    <input type="submit" name="submit" value="Upload" />
    </form>



<?php

if (isset($_POST['submit'])) {
    if (isset($_FILES['uploadFile']['name']) && $_FILES['uploadFile']['name'] != "") {

        $allowedExtensions = array("xls", "xlsx");
        $ext = pathinfo($_FILES['uploadFile']['name'], PATHINFO_EXTENSION);
        if (in_array($ext, $allowedExtensions)) {

            $file_size = $_FILES['uploadFile']['size'] / 1024;
            if ($file_size < 50) {
                $file = "uploads/" . $_FILES['uploadFile']['name'];
                $isUploaded = copy($_FILES['uploadFile']['tmp_name'], $file);
                if ($isUploaded) {
                    //Guardamos ruta fichero en sesión.
                    $_SESSION['file'] = $file;
                    include "db.php";
                    include "vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php";
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

                    echo '<h4>Data from excel file</h4>';
                    echo '<table id="excel" cellpadding="5" cellspacing="1" border="1" class="responsive">';
                    echo '<thead>';

                    $consulta_columnas="SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='usuariosexcel' AND `TABLE_NAME`='usuarios'";             
                    $result=mysqli_query($con, $consulta_columnas);
                    if(!$result) {
                        die("Database query failed: ".$con->error );
                    } 
                    $options="";               
                    while ($row = mysqli_fetch_array($result)) {
                        $options .='<option value="'.$row["COLUMN_NAME"].'">'.$row["COLUMN_NAME"].'</option> ' ;
                    }
                    
                    $select = '<select id="column" name="column">
                    <option value="" selected>Seleccionar uno...</option>
                    #_options
                    </select>';
                    $select=str_replace("#_options",$options,$select);
                    $columns = num_columns($total_columns);
                    for ($i = 0; $i < $columns; $i++) {                        
                        $select1 = str_replace("column", "column".$i, $select);
                        echo '<th>' . $select1 . '</th>';
                    }
                    echo '</thead>';
                    $query = "insert into `usuarios` (`id`, `usuario`, `nombre`,`apellido1`) VALUES ";
                    //Loop through each row of the worksheet
                    for ($row = 2; $row <= 3; $row++) {
                        //Read a single row of data and store it as a array.
                        //This line of code selects range of the cells like A1:D1
                        $single_row = $sheet->rangeToArray('A' . $row . ':' . $total_columns . $row, null, true, false);
                        echo "<tr>";
                        //Creating a dynamic query based on the rows from the excel file
                        $query .= "(";
                        //Print each cell of the current row                    
                        foreach ($single_row[0] as $key => $value) {
                            echo "<td>" . $value ." -".$key. "</td>";
                            $query .= "'" . mysqli_real_escape_string($con, $value) . "',";
                        }
                        $query = substr($query, 0, -1);
                        $query .= "),";
                        echo "</tr>";
                    }
                    $query = substr($query, 0, -1);
                    echo '</table>';
                    echo '<br><button id="btnEnviar">Cargar datos</button><br>';
                    // echo $query;
                    echo '<div id="validaciones"></div>';
                    // At last we will execute the dynamically created query an save it into the database
                    //mysqli_query($con, $query);
                    // if (mysqli_affected_rows($con) > 0) {
                    //     echo '<span class="msg">Database table updated!</span>';
                    // } else {
                    //     echo '<span class="msg">Can\'t update database table! try again.</span>';
                    // }
                    // Finally we will remove the file from the uploads folder (optional)
                    // unlink($file);
                } else {
                    echo '<span class="msg">File not uploaded!</span>';
                }
            } else {
                echo '<span class="msg">Maximum file size should not cross 50 KB on size!</span>';
            }
        } else {
            echo '<span class="msg">This type of file not allowed!</span>';
        }
    } else {
        echo '<span class="msg">Select an excel file first!</span>';
    }
}
?>


</body>
</html>