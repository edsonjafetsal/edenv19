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

//////////////// VALORES INICIALES ///////////////////////

$tabla="";
$query="SELECT co.ref AS co,cat.label AS categorie, CONCAT(u.firstname,' ',u.lastname) AS salesrep, (cod.buy_price_ht*cod.qty) AS extension, cod.total_ht AS total_line,  p.ref AS product, ex.ref AS shipment, eba.batch AS lot_shipment, CASE(p.tobatch) WHEN '0' THEN 'NO' WHEN '1' THEN 'YES' END AS is_lot, soc.nom AS third,  p.description AS prod_desc,
cod.subprice AS sell_pri, cod.buy_price_ht AS buy_pri,
co.date_commande AS co_date, cod.qty As qty,
CONCAT(ROUND(100-(((cod.buy_price_ht*cod.qty)* 100)/cod.total_ht) , 0), '%') AS margin2
FROM  llx_commande AS co
LEFT JOIN llx_commandedet AS cod ON co.rowid = cod.fk_commande
LEFT JOIN llx_commande_extrafields AS coex ON co.rowid = coex.fk_object
LEFT JOIN llx_expeditiondet AS exde ON  cod.rowid = exde.fk_origin_line
LEFT JOIN llx_expedition AS ex ON  exde.fk_expedition = ex.rowid
LEFT JOIN llx_expeditiondet_batch AS eba ON exde.rowid = eba.fk_expeditiondet
LEFT JOIN llx_product AS p ON  cod.fk_product = p.rowid
LEFT JOIN llx_societe AS soc ON co.fk_soc = soc.rowid
  LEFT JOIN llx_element_contact as ec ON ec.element_id = co.rowid and ec.fk_c_type_contact in (91)
LEFT JOIN llx_c_type_contact AS ct ON ec.fk_c_type_contact = ct.rowid AND ct.element LIKE 'commande'
                left join llx_user as u ON u.rowid = ec.fk_socpeople
  LEFT JOIN llx_categorie_product cats ON cats.fk_product = p.rowid
                    LEFT JOIN llx_categorie cat ON cat.rowid = cats.fk_categorie
WHERE 1 = 1  AND p.ref IS NOT NULL
GROUP BY  cod.rowid";

///////// LO QUE OCURRE AL TECLEAR SOBRE EL INPUT DE BUSQUEDA ////////////
if(isset($_POST['alumnos']))
{
	$q=$conexion->real_escape_string($_POST['alumnos']);
	$query="SELECT co.ref AS co,cat.label AS categorie, CONCAT(u.firstname,' ',u.lastname) AS salesrep, (cod.buy_price_ht*cod.qty) AS extension, cod.total_ht AS total_line,  p.ref AS product, ex.ref AS shipment, eba.batch AS lot_shipment, CASE(p.tobatch) WHEN '0' THEN 'NO' WHEN '1' THEN 'YES' END AS is_lot, soc.nom AS third,  p.description AS prod_desc,
cod.subprice AS sell_pri, cod.buy_price_ht AS buy_pri,
co.date_commande AS co_date, cod.qty As qty,
CONCAT(ROUND(100-(((cod.buy_price_ht*cod.qty)* 100)/cod.total_ht) , 0), '%') AS margin2
FROM  llx_commande AS co
LEFT JOIN llx_commandedet AS cod ON co.rowid = cod.fk_commande
LEFT JOIN llx_commande_extrafields AS coex ON co.rowid = coex.fk_object
LEFT JOIN llx_expeditiondet AS exde ON  cod.rowid = exde.fk_origin_line
LEFT JOIN llx_expedition AS ex ON  exde.fk_expedition = ex.rowid
LEFT JOIN llx_expeditiondet_batch AS eba ON exde.rowid = eba.fk_expeditiondet
LEFT JOIN llx_product AS p ON  cod.fk_product = p.rowid
LEFT JOIN llx_societe AS soc ON co.fk_soc = soc.rowid
  LEFT JOIN llx_element_contact as ec ON ec.element_id = co.rowid and ec.fk_c_type_contact in (91)
LEFT JOIN llx_c_type_contact AS ct ON ec.fk_c_type_contact = ct.rowid AND ct.element LIKE 'commande'
                left join llx_user as u ON u.rowid = ec.fk_socpeople
  LEFT JOIN llx_categorie_product cats ON cats.fk_product = p.rowid
                    LEFT JOIN llx_categorie cat ON cat.rowid = cats.fk_categorie
WHERE 1 = 1  AND p.ref IS NOT NULL AND p.tobatch LIKE '%".$q."%'
GROUP BY  cod.rowid";
}

$buscarAlumnos=$conexion->query($query);
if ($buscarAlumnos->num_rows > 0)
{
	$tabla.=
	'<table class="table">
		<tr class="bg-primary">
			<td>CO</td>
			<td>Date</td>
			<td>Categorie</td>
			<td>Sales Representative</td>
			<td>Shipment</td>
			<td>Lot #</td>
			<td>Product</td>
			<td>Selling Price</td>
			<td>Buying Price</td>
			<td>Quantity</td>
			<td>is lot?</td>
			<td>Product Description</td>
			<td>Customer</td>
			<td>Margin</td>
		</tr>';


	
	while($filaAlumnos= $buscarAlumnos->fetch_assoc())
	{
		$tabla.=
		'<tr>
			<td>'.$filaAlumnos['co'].'</td>
			<td>'.$filaAlumnos['co_date'].'</td>
			<td>'.$filaAlumnos['categorie'].'</td>
			<td>'.$filaAlumnos['salesrep'].'</td>
			<td>'.$filaAlumnos['shipment'].'</td>
			<td>'.$filaAlumnos['lot_shipment'].'</td>
			<td>'.$filaAlumnos['product'].'</td>
			<td>'.$filaAlumnos['sell_pri'].'</td>
			<td>'.$filaAlumnos['buy_pri'].'</td>
			<td>'.$filaAlumnos['qty'].'</td>
			<td>'.$filaAlumnos['is_lot'].'</td>
			<td>'.$filaAlumnos['prod_desc'].'</td>
			<td>'.$filaAlumnos['third'].'</td>
			<td>'.$filaAlumnos['margin2'].'</td>
		 </tr>
		';
	}

	$tabla.='</table>';
} else
	{
		$tabla="No se encontraron coincidencias con sus criterios de búsqueda.";
	}


echo $tabla;
?>
