<?php
function num_columns($arg)
{
    $letras=['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
    $v=0;
    $total=0;
    for ($i=strlen($arg)-1; $i >= 0; $i--) { 
        $pos=array_search($arg[$i], $letras);
        $total=$total+(($pos+1)*(pow(26,$v)));
        $v++;   
    }
    return $total;
}


?>