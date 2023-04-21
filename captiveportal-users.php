<?php
// $interval = new DateInterval('P0Y2M'); 


// $data = new DateTime();
// $data = $data->add($interval)->format('Y-m-d');

// echo $data;

//----------------------



function gravar($ra, $username, $course, $expirationDate) {

    $username = strtolower($username);
    $usernameArray = explode(" ",$username);

    $user = array();
    $user = "$usernameArray[0].";
    $user .= $usernameArray[1];

    $arquivo = "captiveportal-usuarios.txt";

    $course = strtoupper($course);

    $username = ucwords($username);

    $texto = "";
    $texto .= "\n\nUsuário: $user\n";
    $texto .= "Senha: $ra\n";
    $texto .= "Data de Expiração: $expirationDate\n";
    $texto .= "Descrição: $username - $ra - $course\n\n";
    $texto .= "--------------------------";


    $fp = fopen($arquivo, "a+");

    fwrite($fp, $texto);

    fclose($fp);

    // echo "<script>console.log('Console: " . $expirationDate . "' );</script>";
}
?>