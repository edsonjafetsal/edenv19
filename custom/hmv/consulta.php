<?php
/////// CONEXIÓN A LA BASE DE DATOS /////////
///
///


header("Expires: Fri, 14 Mar 1980 20:53:00 GMT"); //la pagina expira en fecha pasada
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); //ultima actualizacion ahora cuando la cargamos
header("Cache-Control: no-cache, must-revalidate"); //no guardar en CACHE
header("Pragma: no-cache"); //PARANOIA, NO GUARDAR EN CACHE


// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");


global $db, $conf;

/*
$host = 'localhost';
$basededatos = 'dolibarr_nj';
$usuario = 'root';
$contraseña = 'root';

$db = new mysqli($host, $usuario, $contraseña, $basededatos);
if ($db -> connect_errno)
{
	die("Fallo la conexion:(".$db -> mysqli_connect_errno().")".$db-> mysqli_connect_error());
}

*/


if (isset($_POST['producto'])) {
	$producto = $_POST['producto'];
}
if (isset($_POST['cate'])) $cate = $_POST['cate'];
if (isset($_POST['sales'])) $sales = $_POST['sales'];
if (isset($_POST['cliente'])) $cliente = $_POST['cliente'];
if (isset($_POST['date1'])) $date1 = $_POST['date1'];
if (isset($_POST['date2'])) $date2 = $_POST['date2'];
if (isset($_POST['prc'])) $prc = $_POST['prc'];
/*$nowarray = getdate($date1);
$day = $nowarray['mday'];
$month = $nowarray['mon'];
$year = $nowarray['year'];
$hours = $nowarray['hours'];
$minutes = $nowarray['minutes'];
$seconds = $nowarray['seconds'];
$date_day  = $year.'-'.$month.'-'.$day. ' '.$hours.':'.$minutes.':'.$seconds.'' ;
*/
/*
date1
cliente
product
cat
rep

*/
//////////////// VALORES INICIALES ///////////////////////

$tabla = "";


// $query = "SELECT co.rowid as id, co.ref AS co,cat.label AS categorie,ex.rowid AS shid, CONCAT(u.firstname,' ',u.lastname) AS salesrep, FORMAT(cod.buy_price_ht*cod.qty, 2) AS extension, FORMAT(cod.total_ht, 2) AS total_line,   p.ref AS product, ex.ref AS shipment, eba.batch AS lot_shipment, CASE WHEN(p.tobatch='1') THEN 'YES' ELSE 'NO' END AS is_lot, soc.nom AS third,  p.description AS prod_desc,
// 		FORMAT(cod.subprice, 2) AS sell_pri, FORMAT(cod.buy_price_ht, 2) AS buy_pri,
// 		co.date_commande AS co_date, cod.qty As qty, po.ref AS po,
// 		CONCAT(ROUND(100-(((cod.buy_price_ht*cod.qty)* 100)/cod.total_ht) , 0), '%') AS margin2, co.rowid AS id, CASE WHEN(coex.proc='1') THEN 'YES' ELSE 'NO' END AS prc
// 		,(select pos.ref  from llx_commande_fournisseur as pos
// 	left join llx_element_element as ele on pos.rowid = ele.fk_target and ele.sourcetype like 'commande' and ele.targettype like  'order_supplier'
// 	where co.rowid = ele.fk_source and pos.fk_statut in (1,2,3,4,5)and pos.fk_statut in (1,2,3,4,5)) AS PO
//         ,co.note_public As notes
//         ,(select pos.rowid  from llx_commande_fournisseur as pos
// 	left join llx_element_element as ele on pos.rowid = ele.fk_target and ele.sourcetype like 'commande' and ele.targettype like  'order_supplier'
// 	where co.rowid = ele.fk_source) AS  POid
// 		FROM  llx_commande AS co
// 		LEFT JOIN llx_commandedet AS cod ON co.rowid = cod.fk_commande
// 		LEFT JOIN llx_commande_extrafields AS coex ON co.rowid = coex.fk_object
// 		LEFT JOIN llx_expeditiondet AS exde ON  cod.rowid = exde.fk_origin_line
// 		LEFT JOIN llx_expedition AS ex ON  exde.fk_expedition = ex.rowid
// 		LEFT JOIN llx_expeditiondet_batch AS eba ON exde.rowid = eba.fk_expeditiondet
// 		LEFT JOIN llx_product AS p ON  cod.fk_product = p.rowid
// 		LEFT JOIN llx_societe AS soc ON co.fk_soc = soc.rowid
// 		  LEFT JOIN llx_element_contact as ec ON ec.element_id = co.rowid and ec.fk_c_type_contact in (91)
// 		LEFT JOIN llx_c_type_contact AS ct ON ec.fk_c_type_contact = ct.rowid AND ct.element LIKE 'commande'
// 						left join llx_user as u ON u.rowid = ec.fk_socpeople
// 		  LEFT JOIN llx_categorie_product cats ON cats.fk_product = p.rowid
// 							LEFT JOIN llx_categorie cat ON cat.rowid = cats.fk_categorie
// 		LEFT JOIN llx_element_element AS el ON co.rowid =  el.fk_source AND el.sourcetype LIKE 'commande'
//         LEFT JOIN llx_commande_fournisseur AS po ON el.fk_target = po.rowid AND el.targettype LIKE 'order_supplier'
// 		WHERE 1 = 1 and co.fk_statut in (1,2,3) group by co.ref;";
/*
 * CABECERAS
 * */
$query1 = "SELECT co.rowid as id, co.ref AS co,cat.label AS categorie,ex.rowid AS shid, CONCAT(u.firstname,' ',u.lastname) AS salesrep, FORMAT(cod.buy_price_ht*cod.qty, 2) AS extension, FORMAT(cod.total_ht, 2) AS total_line,   p.ref AS product, ex.ref AS shipment, eba.batch AS lot_shipment, CASE WHEN(p.tobatch='1') THEN 'YES' ELSE 'NO' END AS is_lot, soc.nom AS third,  p.description AS prod_desc,
		FORMAT(cod.subprice, 2) AS sell_pri, FORMAT(cod.buy_price_ht, 2) AS buy_pri,
		co.date_commande AS co_date, cod.qty As qty, po.ref AS po,
		CONCAT(ROUND(100-(((cod.buy_price_ht*cod.qty)* 100)/cod.total_ht) , 0), '%') AS margin2, co.rowid AS id, CASE WHEN(coex.proc='1') THEN 'YES' ELSE 'NO' END AS prc
		,(select pos.ref  from llx_commande_fournisseur as pos
	left join llx_element_element as ele on pos.rowid = ele.fk_target and ele.sourcetype like 'commande' and ele.targettype like  'order_supplier'
	where co.rowid = ele.fk_source and pos.fk_statut in (1,2,3,4,5)and pos.fk_statut in (1,2,3,4,5)) AS PO
        ,co.note_public As notes
        ,(select pos.rowid  from llx_commande_fournisseur as pos
	left join llx_element_element as ele on pos.rowid = ele.fk_target and ele.sourcetype like 'commande' and ele.targettype like  'order_supplier'
	where co.rowid = ele.fk_source) AS POid
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
		LEFT JOIN llx_element_element AS el ON co.rowid =  el.fk_source AND el.sourcetype LIKE 'commande'
        LEFT JOIN llx_commande_fournisseur AS po ON el.fk_target = po.rowid AND el.targettype LIKE 'order_supplier'
		WHERE 1 = 1 and co.fk_statut in (1,2,3)
";

if (isset($_POST['producto']) and $_POST['producto'] != '') {
	$query1 .= "	AND p.rowid = " . $_POST['producto'];
}
$isproductornot=array();
if(isset($_POST['isproduct'])){
	$isproductornot[]=$_POST['isproduct']=='true'?0:2;
	$isproductornot[]=$_POST['isservice']=='true'?1:2;
	$query1.= " AND p.fk_product_type IN ( ".implode(",",$isproductornot).")";
}
$isdropshipped=array();
if (isset($_POST['isdropshipped'])){
	$isdropshipped[]=$_POST['isdropshipped']=='true'?1:2;
	//$isdropshipped[]=$_POST['isnotdropshipped']=='true'?NULL:0;
	//$isdropshipped[]=$_POST['isnotdropshipped']=='true'?NULL:0;


	//$query1.=" and  (coex.dropship in (" .implode(",",$isdropshipped) .") or coex.dropship IS NULL )";
	if($_POST['isdropshipped']=='true' && $_POST['isnotdropshipped'] == 'false'){
		$query1.=" and  (coex.dropship in (" .implode(",",$isdropshipped) .")  )";

	}
if($_POST['isnotdropshipped'] == 'true' && $_POST['isdropshipped']=='true' ){
	$query1.=" and  (coex.dropship in (" .implode(",",$isdropshipped) .")  or ( coex.dropship IS NULL ) )";
}


}
if($_POST['isnotdropshipped'] == 'true'  && ($_POST['isdropshipped']=='false'  || !isset($_POST['isdropshipped']) )){
	$query1.=" and  ( coex.dropship IS NULL )";
}





if ((isset($_POST['date1']) and $_POST['date1'] != '') and (isset($_POST['date2']) and $_POST['date2'] != '')) {
	$query1 .= " and co.date_commande between '".$date1."' and '".$date2."' ";

}


if (isset($_POST['cliente']) and $_POST['cliente'] != '') {
	$query1 .= " AND co.fk_soc = " . $_POST['cliente'];
}
if (isset($_POST['prc']) and $_POST['prc'] != '') {
	$query1 .= " AND coex.proc = " . $_POST['prc'];
}
if (isset($_POST['sales']) and $_POST['sales'] != '') {
	$query1 .= "  AND u.rowid = " . $_POST['sales'];
}
$query1 .= "	GROUP BY  co.ref ORDER BY co.date_commande DESC";

/*
 *  FIN CABECERAS
 * */
if (isset($_POST['cat'])) {
	$cat = $db->real_escape_string($_POST['cat']);
}

///////// LO QUE OCURRE AL TECLEAR SOBRE EL INPUT DE BUSQUEDA ////////////
if (isset($_POST['cat'])) {
	$q = $db->real_escape_string($_POST['alan']);
	$query = "SELECT co.ref AS co,cat.label AS categorie, ex.rowid AS shid,CONCAT(u.firstname,' ',u.lastname) AS salesrep, FORMAT(cod.buy_price_ht*cod.qty, 2) AS extension, FORMAT(cod.total_ht, 2) AS total_line,   p.ref AS product, ex.ref AS shipment, eba.batch AS lot_shipment, CASE(p.tobatch) WHEN '0' THEN 'NO' WHEN '1' THEN 'YES' END AS is_lot, soc.nom AS third,  p.description AS prod_desc,
	FORMAT(cod.subprice, 2) AS sell_pri, FORMAT(cod.buy_price_ht, 2) AS buy_pri,
	co.date_commande AS co_date, cod.qty As qty,
	CONCAT(ROUND(100-(((cod.buy_price_ht*cod.qty)* 100)/cod.total_ht) , 0), '%') AS margin2, co.rowid AS id, coex.proc, cod.remise_percent  as Discount
	,po.ref PO
        ,co.note_public As notes
        ,po.rowid As POid
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
	WHERE 1 = 1  AND p.ref IS NOT NULL AND co.ref LIKE '%" . $q . "%'
	GROUP BY  cod.rowid";

	$query201 = "SELECT co.ref AS co,cat.label AS categorie,ex.rowid AS shid, CONCAT(u.firstname,' ',u.lastname) AS salesrep, FORMAT(cod.buy_price_ht*cod.qty, 2) AS extension, FORMAT(cod.total_ht, 2) AS total_line,   p.ref AS product, ex.ref AS shipment, eba.batch AS lot_shipment, CASE(p.tobatch) WHEN '0' THEN 'NO' WHEN '1' THEN 'YES' END AS is_lot, soc.nom AS third,  p.description AS prod_desc,
	FORMAT(cod.subprice, 2) AS sell_pri, FORMAT(cod.buy_price_ht, 2) AS buy_pri,
	co.date_commande AS co_date, cod.qty As qty,
	CONCAT(ROUND(100-(((cod.buy_price_ht*cod.qty)* 100)/cod.total_ht) , 0), '%') AS margin2, co.rowid AS id, coex.proc, cod.remise_percent  as Discount
	,po.ref PO
        ,co.note_public As notes
        ,po.rowid As POid
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
	WHERE 1 = 1  AND p.ref IS NOT NULL AND co.ref LIKE '%" . $q . "%'
	GROUP BY  co.ref";

}


//$buscarAlumnos=$db->query($query);
//$cabecerasCO=$db->query($query1);
$cabecerasCO = $db->query($query1);

$num = $cabecerasCO->num_rows;
$processall = '<br><div class="form-check" style="float: right;"><input class="form-check-input" type="checkbox" value=""  id="processallordercheck" style="-ms-transform: scale(2);-moz-transform: scale(2);-webkit-transform: scale(2);-o-transform: scale(2); background-color: #c4c6ca; " onchange="processallorder(this)"> <label class="form-check-label" for="flexCheckDefault" style="padding-left: 10px;">Process All Orders</label></div>';
if ($num > 0) {
	print '<br><b>Total Register:'.$num.' </b><br>';
	$numline = 1;
	while ($CABECERACO = $cabecerasCO->fetch_assoc()) {
		$checkedprocess = $CABECERACO['prc'] == 'Yes' ? 'checked' : 'unchecked';
		$estiloprocessed = $CABECERACO['prc'] == 'Yes' ? 'style="color:blue;font-weight: bold;"' : 'style="color:red;font-weight: bold;"';
		//print '<pre>product ';print_r($query1);print '</pre>';
		$tabla .=

			'<table class="tablacabecera">
				  <tr>
				  </tr><tr>
					<td><b>REF: </b></td>
					<td colspan="2"><a target="blank_" href=' . $root_url . '/dolibarr/commande/card.php?id=' . $CABECERACO['id'] . '>' . $CABECERACO['co'] . '</a></td>
					<td><b>&nbsp;&nbsp;PO: </b> </td>
					<td><a target="blank_" href=' . $root_url . '/dolibarr/fourn/commande/card.php?id=' . $CABECERACO['POid'] . '>' . $CABECERACO['PO'] . '</a></td>
					<td >&nbsp;&nbsp;</td>
					<td rowspan="6" style="vertical-align: top"> <p><b>NOTES: </b></p>' . $CABECERACO['notes'] . '</td>
				  </tr>
				  <tr>
					<td><b>Shipment:</b> </td>
					<td colspan="2"><a target="blank_" href=' . $root_url . '/dolibarr/expedition/card.php?id=' . $CABECERACO['shid'] . '>' . $CABECERACO['shipment'] . '</a></td>
				  </tr>
				  <tr>
					<td><b>Customer: </b></td>
					<td colspan="2">' . $CABECERACO['third'] . '</td>
				  </tr>
				  <tr>
					<td><b>CO date: </b></td>
					<td colspan="2">' . $CABECERACO['co_date'] . '</td>
				  </tr>
				  <tr>
					<td><b>Sales Rep.: </b></td>
					<td colspan="2">' . $CABECERACO['salesrep'] . '</td>
				  </tr>

				  <tr>
					<td><b>Processed: </b></td>
					<td colspan="2"><span '.$estiloprocessed.' id="processedvalue' . $CABECERACO['id'] . '"> ' . $CABECERACO['prc'] . '</span></td>
				  </tr>
				</table>
				<br>
				<div class="form-check">
				<input class="form-check-input checkforselect" type="checkbox" ' . $checkedprocess . ' id="processordercheck' . $CABECERACO['id'] . '" data-value="' . $CABECERACO['prc'] . '" style="-ms-transform: scale(2);-moz-transform: scale(2);-webkit-transform: scale(2);-o-transform: scale(2); background-color: #c4c6ca; " onclick="processValue(this)"> <label class="form-check-label" for="flexCheckDefault" style="padding-left: 10px;">Processed Order</label></div>';                //CABECERA COMMANDE  Y FECHA

		/*
		 * INICIO  FILTRADO X   CO
		 * */
		//$COMMANDE = $CABECERACO['co'];
		$COMMANDE = $CABECERACO['id'];



		$query2 = "SELECT co.rowid as id, ex.rowid AS shid, co.ref AS co,cat.label AS categorie, CONCAT(u.firstname,' ',u.lastname) AS salesrep,  FORMAT(cod.buy_price_ht*cod.qty, 2) AS extension, FORMAT(cod.total_ht, 2) AS total_line,  p.ref AS product, ex.ref AS shipment, eba.batch AS lot_shipment, CASE WHEN(p.tobatch='1') THEN 'YES' ELSE 'NO' END AS is_lot, soc.nom AS third,  cod.description AS prod_desc,
		FORMAT(cod.subprice, 2) AS sell_pri, FORMAT(cod.buy_price_ht, 2) AS buy_pri,
		co.date_commande AS co_date, cod.qty As qty, CASE WHEN(coex.dropship='1') THEN 'YES' ELSE 'NO' END AS drp,
		CONCAT(ROUND(100-(((cod.buy_price_ht*cod.qty)* 100)/cod.total_ht) , 0), '%') AS margin2, co.rowid AS id, coex.proc, cod.remise_percent  as Discount
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
		WHERE 1 = 1  ";
		$query2 .= " ";
		$isproductornot=array();
		if(isset($_POST['isproduct'])){
			$isproductornot[]=$_POST['isproduct']=='true'?0:2;
			$isproductornot[]=$_POST['isservice']=='true'?1:2;
			$query2 .= " AND p.fk_product_type IN ( ".implode(",",$isproductornot).")";
		}

		$query2 .=" AND co.rowid ='" . $COMMANDE . "' ".$querysql;
		$query2 .=" GROUP BY cod.rowid;";

		$buscarAlumnos2 = $db->query($query2);

		$tabla .=
			'<table class="table table-striped table-hover tablageneral" >
              <thead>
				<tr >
					<td><b><p>Product</p></b></td>
					<td><b><p>Category</p></b></td>
					<td><b><p>Product Description</p></b></td>
					<td><b><p>Lot Shipment</p></b></td>
					<td><b><p style="text-align:center;">Item Price</p></b></td>
					<td><b><p style="text-align:center;">Cost Price</p></b></td>
					<td><b><p>Quantity</p></b></td>
					<td><b><p>Items Total</p></b></td>
					<td><b><p>Discounts</p></b></td>
					<td><b><p>Extension</p></b></td>
					<td><b><p>Margin</p></b></td>
					<td><b><p>Dropshipped</p></b></td>

				</tr></thead>';
		$tbody = "<tbody><div style='width:100%'>";
		while ($filaAlumnos2 = $buscarAlumnos2->fetch_assoc()) {
			$root_url = "http://" . $_SERVER['HTTP_HOST'];
			$tabla .=
				'<tr>
				    <td>' . $filaAlumnos2['product'] . '</td>
					<td>' . $filaAlumnos2['categorie'] . '</td>
					<td>' . $filaAlumnos2['prod_desc'] . '</td>
					<td>' . $filaAlumnos2['lot_shipment'] . '</td>
					<td><p style="text-align:right;">' . $filaAlumnos2['sell_pri'] . '</p></td>
					<td><p style="text-align:right;">' . $filaAlumnos2['buy_pri'] . '</p></td>
					<td><p style="text-align:center;">' . $filaAlumnos2['qty'] . '</td>
					<td><p style="text-align:center;">' . $filaAlumnos2['total_line'] . '</td>
					<td><p style="text-align:right;">' . $filaAlumnos2['Discount'] . '%</p></td>
					<td><p style="text-align:right;">' . $filaAlumnos2['extension'] . '</p></td>
					<td><p style="text-align:center;">' . $filaAlumnos2['margin2'] . '</td>
					<td><p style="text-align:center;">' . $filaAlumnos2['drp'] . '</td>

				 </tr>';
		}
		$tabla = $tbody . $tabla . '</tbody>';

		/*
		 * FIN FILTRADO X   CO
		 * */


		$tabla .=      //CIERRE FINAL DE TABLA
			'<tr>
			</table>';
		$numline++;
	}


} else {
	$tabla = "No coincidences Found.";
}


echo $processall . $tabla;
?>
