<?php
$interval = new DateInterval('P0Y2M'); 


$data = new DateTime();
$data = $data->add($interval)->format('Y-m-d');

echo $data;

//----------------------

function gravar($user, $password, $expirationDate) {
    $arquivo = "teste.txt";

    $texto = "";
    $texto .= "\n\nUsuário - $user\n";
    $texto .= "Senha - $password\n";
    $texto .= "Data de Expiração - $expirationDate\n\n";
    $texto .= "--------------------------";


    $fp = fopen($arquivo, "a+");

    fwrite($fp, $texto);

    fclose($fp);
}

gravar("joao", "123", "2022-04-09");

?>