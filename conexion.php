<?php
date_default_timezone_set('America/Bogota');
$host = 'localhost';
$user = 'root';
$pass = '';
$bd = 'bdtiendaproductos';

$conn = new mysqli($host,$user,$pass,$bd);

if ($conn->connect_error) {
    die("Conexion Fallida".$conex->connect_error);
}

?>