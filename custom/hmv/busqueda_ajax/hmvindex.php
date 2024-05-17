<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       hmv/hmvindex.php
 *	\ingroup    hmv
 *	\brief      Home page of hmv top menu
 */

header ("Expires: Fri, 14 Mar 1980 20:53:00 GMT"); //la pagina expira en fecha pasada
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); //ultima actualizacion ahora cuando la cargamos
header ("Cache-Control: no-cache, must-revalidate"); //no guardar en CACHE
header ("Pragma: no-cache"); //PARANOIA, NO GUARDAR EN CACHE


// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");


require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

// Load translation files required by the page
$langs->loadLangs(array("hmv@hmv"));

print '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="peticion.js"></script>
<script src="search.js"></script>
';
$action = GETPOST('action', 'aZ09');
$actionup = GETPOST('actionup', 'aZ09');
$actionid = GETPOST('actionid', 'aZ09');
$cliente = GETPOST('cli', 'aZ09');

$product = GETPOST('value', 'aZ09');
$cat = GETPOST('cat', 'aZ09');
$rep = GETPOST('rep', 'aZ09');
$date = GETPOST('date', 'aZ09');
$cbox5 = GETPOST('cbox5', 'aZ09');



// Security check
//if (! $user->rights->hmv->myobject->read) accessforbidden();
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0)
{
	$action = '';
	$socid = $user->socid;
}

$max = 5;
$now = dol_now();


/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("", $langs->trans("HMVArea"));

print load_fiche_titre($langs->trans("HMVArea"), '', 'hmv.png@hmv');

print '<div class="fichecenter"><div class="fichethirdleft">';


/* BEGIN MODULEBUILDER DRAFT MYOBJECT
// Draft MyObject
if (! empty($conf->hmv->enabled) && $user->rights->hmv->read)
{
	$langs->load("orders");

	$sql = "SELECT c.rowid, c.ref, c.ref_client, c.total_ht, c.tva as total_tva, c.total_ttc, s.rowid as socid, s.nom as name, s.client, s.canvas";
	$sql.= ", s.code_client";
	$sql.= " FROM ".MAIN_DB_PREFIX."commande as c";
	$sql.= ", ".MAIN_DB_PREFIX."societe as s";
	if (! $user->rights->societe->client->voir && ! $socid) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
	$sql.= " WHERE c.fk_soc = s.rowid";
	$sql.= " AND c.fk_statut = 0";
	$sql.= " AND c.entity IN (".getEntity('commande').")";
	if (! $user->rights->societe->client->voir && ! $socid) $sql.= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
	if ($socid)	$sql.= " AND c.fk_soc = ".$socid;

	$resql = $db->query($sql);
	if ($resql)
	{
		$total = 0;
		$num = $db->num_rows($resql);

		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<th colspan="3">'.$langs->trans("DraftMyObjects").($num?'<span class="badge marginleftonlyshort">'.$num.'</span>':'').'</th></tr>';

		$var = true;
		if ($num > 0)
		{
			$i = 0;
			while ($i < $num)
			{

				$obj = $db->fetch_object($resql);
				print '<tr class="oddeven"><td class="nowrap">';

				$myobjectstatic->id=$obj->rowid;
				$myobjectstatic->ref=$obj->ref;
				$myobjectstatic->ref_client=$obj->ref_client;
				$myobjectstatic->total_ht = $obj->total_ht;
				$myobjectstatic->total_tva = $obj->total_tva;
				$myobjectstatic->total_ttc = $obj->total_ttc;

				print $myobjectstatic->getNomUrl(1);
				print '</td>';
				print '<td class="nowrap">';
				print '</td>';
				print '<td class="right" class="nowrap">'.price($obj->total_ttc).'</td></tr>';
				$i++;
				$total += $obj->total_ttc;
			}
			if ($total>0)
			{

				print '<tr class="liste_total"><td>'.$langs->trans("Total").'</td><td colspan="2" class="right">'.price($total)."</td></tr>";
			}
		}
		else
		{

			print '<tr class="oddeven"><td colspan="3" class="opacitymedium">'.$langs->trans("NoOrder").'</td></tr>';
		}
		print "</table><br>";

		$db->free($resql);
	}
	else
	{
		dol_print_error($db);
	}
}
END MODULEBUILDER DRAFT MYOBJECT */


print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


$NBMAX = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;
$max = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;



print '</div></div></div>';

/*INICIA CODIGO*/



/*
 * 			AJAX
 * */
print ' <script type="text/javascript">';

/*
 * AJAX PRODUCTOS
 * */
print "
$(document).ready(function(){
  $('#product').on('change',function(){
    let valor = $('#product').val();
    let valor1 = $('#cat').val();
    let valor2 = $('#cliente').val();
    let valor3 = $('#date1').val();
    let valor4 = $('#rep').val();
    let valor5 = $('#cbox5').val();
    console.log(valor);
    $.ajax({
       type: 'POST',
       url : 'hmvindex.php',
       data: {value: valor, cat: valor1, cli: valor2, date: valor3, rep: valor4},
       success: function(data)
       {
			location.reload();
			}
     }).done(function(resultado){
	//	$('#tabla_resultado').html(resultado);
	})
  });
});
 $(document).ready(function(){
  $('#cat').on('change',function(){
    let valor = $('#product').val();
    let valor1 = $('#cat').val();
    let valor2 = $('#cliente').val();
    let valor3 = $('#date1').val();
    let valor4 = $('#rep').val();
    console.log(valor);
    $.ajax({
       type: 'POST',
       url : 'hmvindex.php',
       data: {value: valor, cat: valor1, cli: valor2, date: valor3, rep: valor4},
       success: function(data)
       {

	   }
     });
  });
});
$(document).ready(function(){
  $('#cliente').on('change',function(){
    let valor = $('#product').val();
    let valor1 = $('#cat').val();
    let valor2 = $('#cliente').val();
    let valor3 = $('#date1').val();
    let valor4 = $('#rep').val();
    console.log(valor);
    $.ajax({
       type: 'POST',
       url : 'hmvindex.php',
       data: {value: valor, cat: valor1, cli: valor2, date: valor3, rep: valor4},
       success: function(data)
       {

	   }
     });
  });
});
$(document).ready(function(){
  $('#rep').on('change',function(){
    let valor = $('#product').val();
    let valor1 = $('#cat').val();
    let valor2 = $('#cliente').val();
    let valor3 = $('#date1').val();
    let valor4 = $('#rep').val();
    console.log(valor);
    $.ajax({
       type: 'POST',
       url : 'hmvindex.php',
       data: {value: valor, cat: valor1, cli: valor2, date: valor3, rep: valor4},
       success: function(data)
       {

	   }
     });
  });
});
";





print '    </script>';


/*
 * 			AJAX
 * */
print '<section id="tabla_resultado"> </section>';

print '<label class ="form-label"> DATE</label><input type="date" class="date-range" id="date1" name="date1" value="dateco"><br>';

$sql = "SELECT rowid, nom FROM llx_societe ";
$resql = $db->query($sql);
print '<label class ="form-label">Customer</label>';
print '<select class="form-select" aria-label="Default select example" name = "cliente" id = "cliente">';
foreach ($resql as $cliente){
print "<option value = ".$cliente['rowid']." >".$cliente['nom']."</option>";
}
print '</select>';

//print '<label> Customer</label><input type="searchbox" id="client" value="client"><br>';
//$cliente = GETPOST('cliente', 'aZ09');
$sql = "SELECT rowid, ref FROM llx_product ";
$resql = $db->query($sql);
$i = 0;
$num = $db->num_rows($resql);
//foreach ($resql as $product){
  //  $prod[] .= $product;
//}
$b=0;
foreach($resql as $values1)
{
	foreach($values1 as $value1)
	{
		$prod[$b][].=	$value1;
	}
	$b=$b+1;
}

print '<label class ="form-label"> Product</label>';
print '<select class="form-select" aria-label="Default select example" id = "product" name = "product" value="'.$product.'">';
print '<option value = "" selected>         </option>';
for ($i=0; $i < $num; $i++)
{
	print '<option value = "'.$prod[$i][0].'" >'.$prod[$i][1].'</option>';

}
print '</select>';

print '<pre>product ';print_r($prod[$i]);print '</pre>';

$sql = "SELECT rowid, label FROM llx_categorie ";
$resql = $db->query($sql);
print '<label class ="form-label">Tag/Categorie</label>';
print '<select class="form-select" aria-label="Default select example" name = "cat" id = "cat" value="'.$cat.'">';

foreach ($resql as $cat){
print "<option value = ".$cat['rowid']." >".$cat['label']."</option>";
}
print '</select>';
$sql = "SELECT rowid, CONCAT(firstname,' ',lastname) AS user FROM llx_user ";
$resql = $db->query($sql);
print '<div>';
print '<label class ="form-label"> Sales Representative</label>';
print '<select class="form-select" aria-label="Default select example" name = "rep" id = "rep" value="'.$rep.'">';

foreach ($resql as $rep){
print "<option value = ".$rep['rowid']." >".$rep['user']."</option>";
}
print '</select>';
print '</div>';






print '<br><label>HIDE COLUMNS</label><br><label></label><input type="checkbox" id="cbox1" value=""> Hide CO Ref.</label><input type="checkbox" id="cbox2" value=""> Hide Product</label> <input type="checkbox" id="cbox3" value=""> Hide Shipment</label><label><input type="checkbox" id="cbox4" value=""> Hide Lot</label><br>';
//print '<label><input type="checkbox" id="cbox2" value=""> Hide Product</label><br>';
//print '<label><input type="checkbox" id="cbox3" value=""> Hide Shipment</label><br>';
print '<label><input type="checkbox" id="cbox4" value=""> Hide Lot</label> <input type="checkbox" id="cbox5" value="'.$cbox5.'" name="cbox5"> Hide Customer</label><label><input type="checkbox" id="cbox6" value=""> Hide Description</label><label><input type="checkbox" id="cbox7" value=""> Hide Selling Price</label><label><input type="checkbox" id="cbox8" value=""> Hide Buying Price </label><br>';
//print '<label><input type="checkbox" id="cbox5" value=""> Hide Customer</label><br>';
//print '<label><input type="checkbox" id="cbox6" value=""> Hide Description</label><br>';
//print '<label><input type="checkbox" id="cbox7" value=""> Hide Selling Price</label><br>';
print '<br><label>DROPSHIPPED</label><br><label><input type="checkbox" id="cbox9" value="">Yes </label><label><input type="checkbox" id="cbox10" value="">No </label><br><br>';

//if ($action == 'execute'){ print '<td><a class="butAction"'.($conf->use_javascript_ajax ?  : '').'" href="'.DOL_URL_ROOT.'/custom/hmv/hmvindex.php?action=execute">'.$langs->trans("0").'</a></td>';}
print '<a class="butAction" href="'.DOL_URL_ROOT.'/custom/hmv/hmvindex.php?actionup=execute">'.$langs->trans("Execute").'</a>';

print '<section id="tabla_resultado"> </section>';
		//<!-- AQUI SE DESPLEGARA NUESTRA TABLA DE CONSULTA -->

//if($action == 'execute')
//{
/*$sql = "SELECT CONCAT(u.firstname,' ',u.lastname) AS salesrep, (cod.buy_price_ht*cod.qty) AS extension, FORMAT(cod.total_ht, 2) AS total_line, co.ref AS co, p.ref AS product, ex.ref AS shipment, eba.batch AS lot_shipment, CASE(p.tobatch) WHEN '0' THEN 'NO' WHEN '1' THEN 'YES' END AS is_lot, soc.nom AS third,  p.description AS prod_desc, FORMAT(cod.subprice, 2) AS sell_pri, FORMAT(cod.buy_price_ht, 2) AS buy_pri, co.date_commande AS co_date, cod.qty As qty, cat.label AS categorie, CONCAT(ROUND(100-(((cod.buy_price_ht*cod.qty)* 100)/cod.total_ht) , 0), '%') AS margin2, co.rowid, coex.proc, CASE(coex.proc) WHEN'0' THEN 'NO' WHEN '1' THEN 'YES' END AS prc";
	$sql.= " FROM ".MAIN_DB_PREFIX."commande AS co";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."commandedet AS cod ON co.rowid = cod.fk_commande";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."commande_extrafields AS coex ON co.rowid = coex.fk_object";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."expeditiondet AS exde ON  cod.rowid = exde.fk_origin_line";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."expedition AS ex ON  exde.fk_expedition = ex.rowid";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."expeditiondet_batch AS eba ON exde.rowid = eba.fk_expeditiondet";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product AS p ON  cod.fk_product = p.rowid";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe AS soc ON co.fk_soc = soc.rowid ";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."element_contact as ec ON ec.element_id = co.rowid and ec.fk_c_type_contact in (91) ";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."c_type_contact AS ct ON ec.fk_c_type_contact = ct.rowid AND ct.element LIKE 'commande' ";
	$sql.= " left join ".MAIN_DB_PREFIX."user as u ON u.rowid = ec.fk_socpeople ";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."categorie_product cats ON cats.fk_product = p.rowid ";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."categorie cat ON cat.rowid = cats.fk_categorie ";*/
	$sql = "SELECT p.rowid, p.label from llx_product as p";
	$sql.= " WHERE 1 = 1 ";
	if ($product != "") {
		$sql .= "AND  p.rowid  = " . $product;
	}
	if ($cliente != "") {
		//$sql .= "AND  co.fk_soc  = " . $cliente;
	}
	//$sql .=" AND p.ref IS NOT NULL ";
	$sql .=" limit 3 ";
//	$sql.= " GROUP BY  co.rowid";
	//if (! $user->rights->societe->client->voir && ! $socid) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";

	$sql .= $db->free($max, 0);

	$resql = $db->query($sql);

if ($resql and $product != "")
{
	$a1=0;
	$b=0;
	foreach($resql as $value)
	{
		foreach($value as $a)
		{

			$datos[$b][] .= $a;
		}
		$b=$b+1;
		$a1=0;
	}

	//print $product;





	//print '<ul class="nav nav-pills nav-fill">
	  //        <li class="nav-item">
		//        <a class="butAction btn4" aria-current="page" id="btn4">Sync</a>
		  //    </li>
	//</ul>';

	//print '<a class="butAction" href="'.DOL_URL_ROOT.'/custom/hmv/hmvindex.php?actionupdate='.$object->id.'&amp;action=update">'.$langs->trans("HIDE").'</a>';

	//if ($action == 'update')
	//{
	//			if(!isset($check1)){$check1 = 1;}else {$check1 = null;}
	//			if(!isset($check4)){$check4 = 1;}else {$check4 = null;}
	//			if(!isset($check5)){$check5 = 1;}else {$check5 = null;}
	//			if(!isset($check6)){$check6 = 1;}else {$check6 = null;}
	//			if(!isset($check7)){$check7 = 1;}else {$check7 = null;}
	//			if(!isset($check8)){$check8 = 1;}else {$check8 = null;}
	//			if(!isset($check9)){$check9 = 1;}else {$check9 = null;}
	//			if(!isset($check10)){$check10 = 1;}else {$check10 = null;}
	//			if(!isset($check11)){$check11 = 1;}else {$check11 = null;}
	//}

		//	if(!$check2 ){print '<td>ref</td>';}
		//	if(!$check3 ){print '<td>nombre</td>';}
		//	if(!$check1 ){print '<td>descrip</td>';}


	print'<table class="tftable" border="1">
	<tr><th colspan="17" ><p align="center">Sales by Customer</p></th></tr>
	<tr><p align="center">';
	if(!$check1 ){print '<th>CO</th>';}
	if(!$check2 ){print '<th>EXTENSION</th>';}
	if(!$check3 ){print '<th>TOTAL LINE</th>';}
	if(!$check4 ){print '<th>PRODUCT</th>';}
	if(!$check5 ){print '<th>SHIPMENT</th>';}
	if(!$check6 ){print '<th>LOT/SERIAL</th>';}
	if(!$check7 ){print '<th>LOT</th>';}
	if(!$check8 ){print '<th>COMPANY</th>';}
	if(!$check9 ){print '<th>DESCRIPTION</th>';}
	if(!$check10 ){print '<th>SELL PRICE</th>';}
	if(!$check11 ){print '<th>BUYING PRICE</th>';}
	if(!$check12 ){print '<th>CO DATE</th>';}
	if(!$check13 ){print '<th>QTY</th>';}
	if(!$check14 ){print '<th>PRODUCT</th>';}
	if(!$check15 ){print '<th>MARGIN</th>';}
	if(!$check16 ){print '<th>PROCESSED</th>';}
	if(!$check17 ){print '<th>ACTION</th>';}
	//print'<th>EXTENSION</th><th>TOTAL LINE</th><th>PRODUCT</th><th>SHIPMENT</th><th>LOT/SERIAL</th><th>LOT</th><th>COMPANY</th><th>DESCRIPTION</th><th>SELL PRICE</th><th>NEW</th><th>NEW</th><th>NEW</th><th>NEW</th><th>MARGIN</th><th>NEW</th><th>NEW</th></p></tr>';


	$a1=0;
	$b=0;
	foreach($datos as $value)
	{
		print '<tr>';
		if (!$check1){ echo'<td>'.$value['label'].'</td>';}
		if (!$check2){ print'<td>'.$value['rowid'].'</td>';}

/*
		if (!$check1){ print'<td>'.$value[3].'</td>';}
		if (!$check2){ print'<td>'.$value[1].'</td>';}
		if (!$check3){ print'<td>'.$value[2].'</td>';}
		if (!$check4){ print'<td>'.$value[4].'</td>';}
		if (!$check5){ print'<td>'.$value[5].'</td>';}
		if (!$check6){ print'<td>'.$value[6].'</td>';}
		if (!$check7){ print'<td>'.$value[7].'</td>';}
		if (!$check8){ print'<td>'.$value[8].'</td>';}
		if (!$check9){ print'<td>'.$value[9].'</td>';}
		if (!$check10){ print'<td>'.$value[10].'</td>';}
		if (!$check11){ print'<td>'.$value[11].'</td>';}
		if (!$check12){ print'<td>'.$value[12].'</td>';}
		if (!$check13){ print'<td>'.$value[13].'</td>';}
		if (!$check14){ print'<td>'.$value[14].'</td>';}
		if (!$check15){ print'<td>'.$value[15].'</td>';}
		if (!$check17){ print'<td>'.$value[17].'</td>';}
	//	if (!$check16){

		   // print'<td>';
		 //print '<input type="checkbox" id="cbox'.$value[17].'"></td>';}
	/* $sql = "SELECT rowid, proc  ";
	 $sql.= " FROM ".MAIN_DB_PREFIX."commande_extrafields";
	// $sql.= " WHERE fk_object  =".$value[16];
	 $resql = $db->query($sql);

	 foreach ($resql AS $values){}
		if ($value[17] == 0){ print '<td><a class="butAction"'.($conf->use_javascript_ajax ?  : '').'" href="'.DOL_URL_ROOT.'/custom/hmv/hmvindex.php?actionid='.$value[16].'&amp;actionup=update">'.$langs->trans("0").'</a></td>';}
	    elseif ($value[17] == 1){ print '<td><a class="butAction"'.($conf->use_javascript_ajax ?  : '').'" href="'.DOL_URL_ROOT.'/custom/hmv/hmvindex.php?actionid='.$value[16].'&amp;actionup=return">'.$langs->trans("1").'</a></td>';}
		  //  print '<a class="butAction'.($conf->use_javascript_ajax ? ' reposition' : '').'" href="'.$_SERVER["PHP_SELF"].'?facid='.$object->id.'&amp;action=valid">'.$langs->trans('Validate').'</a>';
		 $ID = $rows['id'];
                   $proc =$rows['proc'];
                   if ($action == 'update')
				{
					if(!isset($proc)){$proc = 1;}
				}
*/

	//	print '<td>'.$value[1].'</td><td>'.$value[2].'</td><td>'.$value[4].'</td><td>'.$value[5].'</td><td>'.$value[6].'</td><td>'.$value[7].'</td><td>'.$value[8].'</td><td>'.$value[9].'</td><td>'.$value[10].'</td><td>'.$value[11].'</td><td>'.$value[12].'</td><td>'.$value[13].'</td><td>'.$value[14].'</td><td>'.$value[15].'</td><td>'.$value[16].'</td><td>check </td>';


	print '</tr>';

	}
//
	print '<tr><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td></tr>
</table>';
} else	 { print "Not Found";}

if ($actionup == 'update') {
    $sql = "UPDATE ".MAIN_DB_PREFIX."commande_extrafields AS ce";
	$sql.= " SET ce.proc = 1";
    $sql.= " WHERE ce.fk_object =".$actionid;

	$resql = $db->query($sql);
	if ($resql)
{
	 $sql = "SELECT rowid, ref  ";
	 $sql.= " FROM llx_commande";
	 $sql.= " WHERE rowid  =".$actionid;
	 $resql = $db->query($sql);

	 foreach ($resql AS $values){

	 }

     //print '<pre>';print_r($values);print '</pre>';

	setEventMessages($values[ref].' : ', 'Order Processed', 'mesgs');

} else {setEventMessages($actionup.' : Error', '', 'mesgs');
}
}
if ($actionup == 'return') {
    $sql = "UPDATE ".MAIN_DB_PREFIX."commande_extrafields AS ce";
	$sql.= " SET ce.proc = 0";
    $sql.= " WHERE ce.fk_object =".$actionid;

	$resql = $db->query($sql);
	if ($resql)
{
     $sql = "SELECT rowid, ref  ";
	 $sql.= " FROM ".MAIN_DB_PREFIX."commande";
	 $sql.= " WHERE rowid  =".$actionid;
	 $resql = $db->query($sql);

	 foreach ($resql AS $values){}


	setEventMessages($values[ref].' : ', 'Order Unprocessed', 'mesgs');

} else {setEventMessages($actionup.' : Error', '', 'mesgs');
}
}

$db->commit();


print '
<br>
<style type="text/css">
.tftable {font-size:29px;color:#1115C0;width:100%;border-width: 1px;border-color: #9dcc7a;border-collapse: collapse;}
.tftable th {font-size:12px;background-color:#1497E6;border-width: 1px;padding: 8px;border-style: solid;border-color: #1115C0;text-align:left;}
.tftable tr {background-color:#ffffff;}
.tftable td {font-size:12px;border-width: 1px;padding: 8px;border-style: solid;border-color: #1115C0;}
.tftable tr:hover {background-color:#B1B0B1;}
</style>

';




//}



// End of page
llxFooter();
$db->close();
