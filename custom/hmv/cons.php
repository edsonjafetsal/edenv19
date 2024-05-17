<?php
/////// CONEXIÓN A LA BASE DE DATOS /////////
$host = 'localhost';
$basededatos = 'dolibarr';
$usuario = 'root';
$contraseña = 'admin123';

$conexion = new mysqli($host, $usuario, $contraseña, $basededatos);
if ($conexion -> connect_errno)
{
	die("Fallo la conexion:(".$conexion -> mysqli_connect_errno().")".$conexion-> mysqli_connect_error());
}





if($_POST['crowid']) $crowid   =   $_POST['crowid'];

//////////////// VALORES INICIALES ///////////////////////


$query5="UPDATE llx_commande_extrafields AS t2
SET t2.proc = 1
WHERE t2.fk_object =".$_POST['crowid'].";";


$qur = $conexion->query($query5);
if($qur){}



?>
