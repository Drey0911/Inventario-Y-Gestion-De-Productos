<?php
date_default_timezone_set('America/Bogota');
$host = 'localhost';
$user = 'root';
$pass = '';
$bd = 'bdproductostienda';

$conn = new mysqli($host,$user,$pass,$bd,3306);

if ($conn->connect_error) {
    die("Conexion Fallida".$conex->connect_error);
}

?>