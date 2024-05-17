<?php
$mysqli = new mysqli("localhost","root","admin123", "dolibarr");

$salida ="";
$query ="SELECT * FROM llx_propal ORDER BY rowid";

if(isset($_POST['consulta'])){
	$q = $mysqli->real_escape_string($_POST['consulta']);
	$query =  "SELECT ref, ref_client, datec, total FROM llx_propal
WHERE ref LIKE '%".$q."%' OR ref_client LIKE '%".$q."%' OR datec LIKE '%".$q."%'";
}

$resultado = $mysqli->query($query);

if($resultado->num_rows > 0){

	$salida.="<table class='tabla_datos'>
<thead>
<tr>
<td>ref</td>
<td>ref client</td>
<td>date</td>
<td>total</td>
</tr>
</thead>
<tbody>";

	$con = 0;
	$prop[][] = null;
	foreach($resultado AS $values){
		foreach($values As $value){
			$prop[$con][].=$value;}
		$con= $con+1;
	}
    for($a=0; $a <= $resultado->num_rows; $a++)
	{
	//while($fila = $resultado->fetch_assoc()){
		$salida.="<tr>
                     <td>".$prop[$a][0]."</td>
                     <td>".$prop[$a][1]."</td>
         </tr>";
	}

	$salida.="</tbody></table>";

} else {
	 $salida.="No data";

}

print $salida;

$mysqli->close();
?>
