<?php
$interval = new DateInterval('P0Y2M'); 


$data = new DateTime();
$data = $data->add($interval)->format('Y-m-d');

echo $data;

//----------------------