<?php
$interval = new DateInterval('P1Y7M'); 


$data = new DateTime();
$data = $data->add($interval)->format('Y-m-d');

echo $data;



