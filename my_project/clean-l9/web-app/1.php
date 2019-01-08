<?php

$token = "vtJ1YmMCPHtkXRIwcToX5WW3X1oHtvkTDgAVODnScLQm/qILPi/FEMarnBGyYu0V";

$a = decrypt($token,"ULY4SHIBEY7KGKGS","JGZQPI1352OPXQJV");


function decrypt($str, $key, $iv)
{   

    $str = base64_decode($str);
    $result = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $str, MCRYPT_MODE_CBC, $iv);

    var_dump($result);
    die();
    return $result;
}


var_dump($a);




   


?>