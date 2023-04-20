<?php
// $interval = new DateInterval('P0Y2M'); 


// $data = new DateTime();
// $data = $data->add($interval)->format('Y-m-d');

// echo $data;

//----------------------

$nome = "Caio Palermo Lemos";

$nome = strtolower($nome);

//implode e explode function para split e join

echo $nome;

function gravar($user, $password, $expirationDate) {
    $arquivo = "usuarios.txt";

    $texto = "";
    $texto .= "\n\nUsuário - $user\n";
    $texto .= "Senha - $password\n";
    $texto .= "Data de Expiração - $expirationDate\n";
    $texto .= "Descrição - $expirationDate\n\n";
    $texto .= "--------------------------";


    $fp = fopen($arquivo, "a+");

    fwrite($fp, $texto);

    fclose($fp);

    // echo "<script>console.log('Console: " . $expirationDate . "' );</script>";
}
?>