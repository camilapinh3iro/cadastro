<?php
// $interval = new DateInterval('P0Y2M'); 


// $data = new DateTime();
// $data = $data->add($interval)->format('Y-m-d');

// echo $data;

//----------------------



function writeUser($ra, $username, $course, $expirationDate) {

    $username = strtolower($username);
    $usernameArray = explode(" ",$username);

    $user = array();
    $user = "$usernameArray[0].";
    $user .= $usernameArray[1];

    $file = "captiveportal-usuarios.txt";

    $course = strtoupper($course);

    $username = ucwords($username);

    $userData = "";
    $userData .= "\n\nUsuário: $user\n";
    $userData .= "Senha: $ra\n";
    $userData .= "Data de Expiração: $expirationDate\n";
    $userData .= "Descrição: $username - $ra - $course\n\n";
    $userData .= "--------------------------";


    $fileOpen = fopen($file, "a+");

    fwrite($fileOpen, $userData);

    fclose($fileOpen);

    // echo "<script>console.log('Console: " . $expirationDate . "' );</script>";
}

function deleteRa($ra) {

    $fileContent = file_get_contents('captiveportal-contribuintes.txt');

    $fileContent = str_replace("$ra,", "", $fileContent);

    // Remove a linha em branco que aparecerá após a remoção
    $fileContent = preg_replace('/^\h*\v+/m', '', $fileContent);

    file_put_contents('captiveportal-contribuintes.txt', $fileContent);

}

// deleteRa('789');

?>