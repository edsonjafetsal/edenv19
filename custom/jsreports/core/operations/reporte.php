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

//header ("Expires: Fri, 14 Mar 1980 20:53:00 GMT"); //la pagina expira en fecha pasada
//header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); //ultima actualizacion ahora cuando la cargamos
//header ("Cache-Control: no-cache, must-revalidate"); //no guardar en CACHE
//header ("Pragma: no-cache"); //PARANOIA, NO GUARDAR EN CACHE


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
if (!$res && file_exists("../../../../main.inc.php")) $res = @include "../../../../main.inc.php";

global $user, $db, $langs;


require_once DOL_DOCUMENT_ROOT . '/custom/jsreports/core/operations/operations.php';

$Width1 	= GETPOST('Width1', 'aZ09');
$Width2 = GETPOST('Width2', 'aZ09');
$Thickness1 	= GETPOST('Thickness1', 'aZ09');
$Thickness2 	= GETPOST('Thickness2', 'aZ09');
$Length1= GETPOST('Length1', 'aZ09');
$Length2 	= GETPOST('Length2', 'aZ09');

$idSociete 	= GETPOST('societe', 'aZ09');
$idCommande = GETPOST('commande', 'aZ09');
$idFacture 	= GETPOST('facture', 'aZ09');
$idProduct 	= GETPOST('product', 'aZ09');
$idCategorie= GETPOST('categorie', 'aZ09');
$idUser 	= GETPOST('user', 'aZ09');
$id_c_product_nature = GETPOST('c_product_nature', 'aZ09');
$date 		= GETPOST('date', 'aZ09');
$dateendcommande		= GETPOST('date2', 'aZ09');
$datestart 	= GETPOST('datestart', 'aZ09');
$dateend 	= GETPOST('dateend', 'aZ09');
$workstation 	= GETPOST('workstation', 'aZ09');
$nomenclature 	= GETPOST('nomenclature', 'aZ09');
$columns 	= GETPOST('columns', 'aZ09');

$CustomerFiltro 	= GETPOST('CustomerFiltro', 'aZ09');
$datestartorder 	= GETPOST('datestartorder', 'aZ09');
$dateendorder 	= GETPOST('dateendorder', 'aZ09');
$OrderRefFiltro 	= GETPOST('OrderRefFiltro', 'aZ09');
$datestartdelivery 	= GETPOST('datestartdelivery', 'aZ09');
$dateenddelivery	= GETPOST('dateenddelivery', 'aZ09');
$OrderMarginFiltro 	= GETPOST('OrderMarginFiltro', 'aZ09');
$SalesOrder 	= GETPOST('SalesOrder', 'aZ09');
$columns 	= GETPOST('columns', 'aZ09');


$report = getDataReport($idSociete,$idCommande,$idFacture,$idProduct,$idCategorie,$idUser, $id_c_product_nature, $date, $datestart, $dateend, $workstation, $nomenclature, $dateendcommande,$Width1,$Width2, $Thickness1, $Thickness2,$Length1, $Length2,$SalesOrder );
/*
if($report != null) {


	$num = count($report);
	$tabla = '
<table class="border centpercent">
<tbody>
<tr class="liste_titre">
<td class="titlefield" >NUM </td>
<td class="titlefield" >LABEL </td>
<td class="titlefield" >TITLE </td>
<td class="titlefield" >CUSTOMER </td>
<td class="titlefield" >COMMANDE </td>
<td class="titlefield" >FINISHED PRODUCT </td>
<td class="titlefield" >QTY REQUESTED</td>
<td class="titlefield" >WORKSTATION </td>
<td class="titlefield" >RAWM ATERIAL </td>
<td class="titlefield" >RANK </td>
<td class="titlefield" >HOUR </td>
<td class="titlefield" >HOUR PREPARE </td>
<td class="titlefield" >HOUR MANUFACTURE </td>
<td class="titlefield" >DATE COMMANDE </td>
<td class="titlefield" >DATE DELIVERY </td>
<td class="titlefield" >CHECK </td>
</tr>

';
	for ($a = 0; $a < $num; $a++) {
		$tabla .= '

<tr class="oddeven" >
<td class="nowraponall" >&nbsp;' . ($a + 1) . '</td>
<td class="nowraponall" >&nbsp;' . $report[$a]->label . '</td>
<td class="nowraponall" >&nbsp;' . $report[$a]->title . '</td>
<td class="nowraponall" ><a href="'.DOL_URL_ROOT.'/societe/card.php?id='.$report[$a]->societerowid.'" target="blank">' .$report[$a]->societe. '</a></td>
<td class="nowraponall" > <a href="'.DOL_URL_ROOT.'/commande/card.php?id='.$report[$a]->ORDERROWID.'" target="blank">' . $report[$a]->Commande . '</a></td>
<td class="nowraponall" >&nbsp;' . $report[$a]->Product2 . '</td>
<td class="nowraponall" >&nbsp;' . $report[$a]->Qty . '</td>
<td class="nowraponall" >&nbsp;' . $report[$a]->Workstation1 . '</td>
<td class="nowraponall" >&nbsp;' . $report[$a]->RAWMATERIAL . '</td>
<td class="nowraponall" >&nbsp;' . $report[$a]->rango . '</td>
<td class="nowraponall" >&nbsp;' . $report[$a]->hour . '</td>
<td class="nowraponall" >&nbsp;' . $report[$a]->hourprepare . '</td>
<td class="nowraponall" >&nbsp;' . $report[$a]->hourmanufacture . '</td>
<td class="nowraponall" >&nbsp;' . $report[$a]->Datecomande . '</td>
<td class="nowraponall" >&nbsp;' . $report[$a]->Datedeliver . '</td>
<td class="nowraponall" >&nbsp;<input type="checkbox" id="' . ($a + 1) . '" value="" name="array[]"></td>
</tr>';
	}
	$tabla .= '
</tbody>
</table>



';


	echo $tabla;
}else{
	echo "SIN RESULTADOS";
}
*/

if($report != null) {


	$num = count($report);
	//$ContFiltros=count($columns);


	$tabla = "<style>
			.resaltar{
				cursor: default;
				background-color: #afb5b7;
				color: #ffffff;
			}
		</style>";


	$tabla .= '<b>Total: '.$num.'</b>';
	$tabla .= '<div style="margin: 0px; max-width: (auto - 10%); overflow-x: auto; border: 1px solid #ccc; padding: 20px;"> ';

	$tabla .= '<table class="tagtable liste listwithfilterbefore alan table table-striped"  border="1 " id="alan" name="alan">';

//	$tabla .= '	<tr class="liste_titre" >';

	$tabla .= '<!-- --><!-- <td class="titlefield" > rowid </td> -->';

		// /*<!-- 2-->*/	//$tabla .=' <td class="titlefield" style="text-align:center"> <input type="text" id="CustomerFiltro" name="CustomerFiltro" /></td>';
		// /*<!-- 3-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="date" style="height:20px width:100px" name= "datestartorder" id="datestartorder" class="form-control" value= ""> to <input type="date" style="height:20px; width:100px" name= "dateendorder" id="dateendordersi si asd" class="form-control" value= ""></td>';
		// /*<!-- 4-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="OrderRefFiltro" name="OrderRefFiltro" /></td>';
		// /*<!-- 5-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="date" style="height:20px width:100px" name= "datestartdelivery" id="datestartdelivery" class="form-control" value= ""> to <input type="date" style="height:20px; width:100px" name= "dateenddelivery" id="dateenddelivery" class="form-control" value= ""></td>';
		// /*<!-- 6-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="OrderMarginFiltro" name="OrderMarginFiltro" /></td>';
		// /*<!-- 7-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="FinishedProductFiltro" name="FinishedProductFiltro" /></td>';
		// /*<!-- 8-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="QtyRequestedFiltro" name="QtyRequestedFiltro" /></td>';
		// /*<!-- 9-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="FpuomdFiltro" name="FpuomdFiltro" /></td> ';
		// /*<!-- 10-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="FpStockFiltro" name="FpStockFiltro" /></td>';
		// /*<!-- 11-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="BomFiltro" name="BomFiltro" /></td>';
		// /*<!-- 12-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="RawMaterialFiltro" name="RawMaterialFiltro" /></td>';
		// /*<!-- 13-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="RmBomFiltro" name="RmBomFiltro" /></td>';
		// /*<!-- 14-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="RmProductionFiltro" name="RmProductionFiltro" /></td>';
		// /*<!-- 15-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="QtyNeededFiltro" name="QtyNeededFiltro" /></td>';

		// /*<!-- 17-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="RmStockFiltro" name="RmStockFiltro" /></td>';
		// /*<!-- 18-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="RmBuyingFiltro" name="RmBuyingFiltro" /></td>';
		// /*<!-- 19-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="RmLeadFiltro" name="RmLeadFiltro" /></td>';
		// /*<!-- 20-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="SubassemblyStockFiltro" name="SubassemblyStockFiltro" /></td>';
		// /*<!-- 21-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="SubassemblyRmFiltro" name="SubassemblyRmFiltro" /></td>';
		// /*<!-- 22-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="SaUomFiltro" name="SaUomFiltro" /></td>';


		// /*<!-- 23-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="MOFiltro" name="MOFiltro" /></td>';
		// /*<!-- 24-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="STATUSFiltro" name="STATUSFiltro" /></td>';
		// /*<!-- 25-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="PRODUCTIONORDERFiltro" name="PRODUCTIONORDERFiltro" /></td>';
		// /*<!-- 26-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="ESTIMATEDPRODUCTIONTIMEFiltro" name="ESTIMATEDPRODUCTIONTIMEFiltro" /></td>';

		// /*<!-- 27-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="WorkStationFiltro" name="WorkStationFiltro" /></td>';
		// /*<!-- 28-->*/	$tabla .='<td class="titlefield" style="text-align:center"><input type="text" id="RankFiltro" name="RankFiltro" width="50px"/></td>';
		// /*<!-- 29-->*/$tabla .='<td class="titlefield" style="text-align:center"><button type="submit" class="liste_titre button_search" name="button_search_x" value="x"><span class="fa fa-search"></span></button></td>';
	//	$tabla .='</tr>';
	$va=0;
	for ($a = 0; $a < $num; $a++) {
			if($a ==0){
				$va= $report[$a]->leadtime ;
			}
			if( $report[$a]->leadtime  > $va){
				$va= $report[$a]->leadtime ;
			}

	}



	$tabla .='
		<tr class="liste_titre encabezado" id="encabezado">
		<!--1 --><th class="titlefield"   > # </th>
		<!-- --><!-- <td class="titlefield" > rowid </td> -->';
		/*<!-- 2-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>CUSTOMER</b></th>';
		/*<!-- 3-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>ORDER DATE</b></th>';
		/*<!-- 4-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>ORDER REF</b></th>';
		/*<!-- 5-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>DELIVERY DATE</b></th>';
		/*<!-- 6-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>PRODUCT MARGIN </b></th>';
		/*<!-- 7-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>FINISHED PRODUCT </b></th>';
		/*<!-- 8-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>QTY REQUESTED</b></th>';
		/*<!-- 9-->*/$tabla .='<th class="titlefield" ><b>FP UOM</b></th> ';
		/*<!-- 10-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>FP STOCK</b></th>';
		/*<!-- 11-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>BOM</b></th>';
		/*<!-- 12-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>RAW MATERIAL</b></th>';
		/*<!-- 13-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>RM BOM QTY</b></th>';
		/*<!-- 14-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>RM PRODUCTION UOM</b> </th>';
		/*<!-- 15-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>QTY NEEDED</b></th>';

		/*<!-- 17-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>RM STOCK</b> </th>';
//		/*<!-- 17a-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>RM STOCK TOTAL</b> </th>';
		/*<!-- 18-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>RM BUYING UOM</b> </th>';
		/*<!-- 19-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>RM LEAD TIME (DAYS)</b></th>';
		/*<!-- 20-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>SUBASSEMBLY STOCK</b></th>';
		/*<!-- 21-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>SUBASSEMBLY RM</b></th>';
		// /*<!-- 22-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>SA UOM </b></td>';

	/*<!-- 23-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>MO</b> </th>';
	/*<!-- 24-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>MO STATUS</b> </th>';
	/*<!-- 25-->*/$tabla .='<th class="nowraponall" style="text-align:center"><b>MO PARENT</b> </th>';
	// /*<!-- 26-->*/$tabla .='<td class="nowraponall" style="text-align:center"><b>MO CHILDREN</b> </td>';
	/*<!-- 27-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>PURCHASE ORDER</b> </th>';
	/*<!-- 27a-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>PURCHASE QTY</b> </th>';
	/*<!-- 267-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>PO DATE OF  DELIVERY</b> </th>';
//	/*<!-- 268-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>ESTIMATED PRODUCTION TIME</b> </th>';
//	/*<!-- otro-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>Length x Width x Thick</b> </th>';
//	/*<!-- otro-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>Length </b> </th>';
//	/*<!-- otro-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>Width</b> </th>';
//	/*<!-- otro-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>Thick</b> </th>';
//	/*<!-- otro-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>Wide</b> </th>';

	//	/*<!-- 29-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>WORK STATION</b> </td>';
	//	/*<!-- 30-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>RANK </b></td>';
	///*<!-- 268-->*/$tabla .='<th class="titlefield" style="text-align:center"> </th>';
		/*<!-- 31-->*/$tabla .='<th class="titlefield" style="text-align:center"><input type="checkbox" name="select-all" id="select-all" /></th>';
		$tabla .='</tr>';

	$refcomandes_color=Array();
	$SumasRawMaterial=Array();
	$commandes_stock=Array();


	for ($a = 0; $a < $num; $a++) {
		$totalpedidios=0;
		$NEEDED = $report[$a]->Commandederqty*$report[$a]->Qty;
		if($report[$a]->fpstock ==null) $report[$a]->fpstock=0;
		/*
		 * ASIGNACIONES COLOR
		 * */
		$numero_color_order=null;
		$numRefCommandes=count($refcomandes_color);
		if($numRefCommandes == 0){
			$color=color_random();
			$refcomandes_color[] =array('idOrder' => $report[$a]->ORDERROWID,
											'color' => $color);
			$colorasignado=$refcomandes_color[0]['color'];
		}else{
			for($z=0;$z<$numRefCommandes;$z++){
				if($refcomandes_color[$z]["idOrder"]==$report[$a]->ORDERROWID){
					$numero_color_order=$z;
				}
			}
			if(is_null($numero_color_order)){
				$color=color_random();
				$refcomandes_color[] =array('idOrder' => $report[$a]->ORDERROWID, 'color' => $color);
				$colorasignado=$color;
			}else{
				$colorasignado=$refcomandes_color[$numero_color_order]['color'];

			}

		}

		/*
		 * FIN ASIGNACIONES COLOR
		 * */

		// $NEEDED   							NECESARIO
		//$report[$a]->rmstock    				ACTUAL
		//$report[$a]->id_fkproduct				PRODUCTO
		/*
		 * ASIGNACIONES STOCK ROW MARTERIAL
		 * */
		if($NEEDED  <=0){
			$nuevovalor123=number_format( $report[$a]->Commandederqty);
		}else{
			$nuevovalor123=number_format($NEEDED);
		}


		$numero_stock_rowmaterial=null;
		$stocknuevo=null;
		$numRowMaterial=count($SumasRawMaterial);
		if($numRowMaterial == 0){
				$stocknuevo=$report[$a]->rmstock- $NEEDED;
			$SumasRawMaterial[] =array('rowmaterial' => $report[$a]->id_fkproduct,
				'stock' => $stocknuevo,
				'need' => $NEEDED,
				);
			$stockasignado=$SumasRawMaterial[0]['rowmaterial'];
		}else{
			for($z=0;$z<$numRowMaterial;$z++){
				if($SumasRawMaterial[$z]["rowmaterial"]==$report[$a]->id_fkproduct){
					$numero_stock_rowmaterial=$z;
					$SumasRawMaterial[$z]['need']=$SumasRawMaterial[$z]['need']+$NEEDED;
				}
			}
			if(is_null($numero_stock_rowmaterial)){
				$stocknuevo=$report[$a]->rmstock- $NEEDED;


				$SumasRawMaterial[] =array('rowmaterial' => $report[$a]->id_fkproduct,
					'stock' => $stocknuevo,
					'need' => $NEEDED,
				);

			}else{

				$stocknuevo=$SumasRawMaterial[$numero_stock_rowmaterial]['rowmaterial']-$NEEDED;

			}

		}
		if($stocknuevo < 0){
			$stocknuevo= $stocknuevo*-1;
		}else{
			$stocknuevo= $stocknuevo*1;
		}

		/*
		 * FIN ASIGNACIONES STOCK ROW MARTERIAL
		 * */



		/*
		 * ASIGNACIONES STOCKfinished product
		 * $commandes_stock=Array();
		 * */

		$numero_stock_rfinishedproduct=null;//$numero_stock_rowmaterial=null;
		$stocknuevo=null;
		$numFinishedProduct=count($commandes_stock);
		if($numFinishedProduct == 0){
			$stocknuevo=$report[$a]->rmstock- $NEEDED;
			$commandes_stock[] =array('finishedproduct' => $report[$a]->idfinishedproduct,
				'stock' => $report[$a]->fpstock,
				'need' => $report[$a]->Commandederqty,
				'rawmaterial' =>$report[$a]->id_fkproduct
			);
			$stockasignado=$commandes_stock[0]["stock"];
		}else{
			for($z=0;$z<$numFinishedProduct;$z++){
				if($commandes_stock[$z]["finishedproduct"]== $report[$a]->idfinishedproduct and $commandes_stock[$z]["rawmaterial"]== $report[$a]->id_fkproduct){
					$numero_stock_finished=$z;
					$commandes_stock[$z]['need']=$commandes_stock[$z]['need']+$NEEDED;
				}else{
					if($commandes_stock[$z]["finishedproduct"]== $report[$a]->idfinishedproduct ){
							$commandes_stock[] =array('finishedproduct' => $report[$a]->idfinishedproduct,
								'stock' => $report[$a]->fpstock,
								'need' => $report[$a]->Commandederqty,
								'rawmaterial' =>$report[$a]->id_fkproduct
							);
						$numero_stock_finished=$z;
						$commandes_stock[$z]['need']=$commandes_stock[$z]['need']+$NEEDED;
					}

				}
			}
			if(is_null($numero_stock_finished)){
				$stocknuevofinished=$report[$a]->fpstock- $NEEDED;


				$commandes_stock[] =array('finishedproduct' => $report[$a]->idfinishedproduct,
					'stock' => $report[$a]->fpstock,
					'need' => $report[$a]->Commandederqty,
					'rawmaterial' =>$report[$a]->id_fkproduct
				);

			}else{

				$stocknuevofinished=$commandes_stock[$numero_stock_finished]['stock']-$NEEDED;

			}

		}


		/*
		 * FIN ASIGNACIONES STOCK Rfinished product
		 * */



		$tabla .= '	<tr class="oddeven" >
			<!-- 1--><td class="nowraponall" style="text-align:center">&nbsp;' . ($a + 1) . '</td>
			<!-- --><!-- <td class="nowraponall" style="text-align:center">&nbsp;' . $report[$a]->principalid  . '</td> -->';
			/*<!-- 2-->*/$tabla .='<td class="nowraponall" style="text-align:center"><a href="'.DOL_URL_ROOT.'/societe/card.php?id='.$report[$a]->societerowid.'" target="blank">' .$report[$a]->societe. '</a></td>';
			/*<!-- 3-->*/$tabla .='<td class="nowraponall" style="text-align:center">' . $report[$a]->Datecomande . '</td>';
			/*<!-- 4-->*/$tabla .='<td class="nowraponall" style="text-align:center" BGCOLOR="'.$colorasignado.'"> <a href="'.DOL_URL_ROOT.'/commande/card.php?id='.$report[$a]->ORDERROWID.'" target="blank">' . $report[$a]->Commande . '</a></td>';
		if($report[$a]->POrowid >0){
			$sql1231 ="   select * from ".MAIN_DB_PREFIX."commande_fournisseur lcf  where rowid =".$report[$a]->POrowid." ";
			$res11=  $db->query($sql1231);
		}else{
			$datosPO1=null;
		}

		//$datosPO1 = $db->fetch_object($res11);

		$fechaCorta1 = date("m-d-Y", strtotime($datosPO1->date_livraison));
		//$fechaCorta2 = date("m-d-Y", strtotime($report[$a]->Datedeliver));

// Comparar las fechas
		if ($fechaCorta1 > $report[$a]->Datedeliver) {
			// Si la fechaCorta1 es mayor que la fechaCorta2, poner la celda en rojo
			$tabla .= '<td class="nowraponall " bgcolor="#FF0000" style="text-align:center;">' . $report[$a]->Datedeliver . '</td>';
		} else {
			// Si no, dejar la celda sin estilo adicional
			$tabla .= '<td class="nowraponall" style="text-align:center;">' . $report[$a]->Datedeliver . '</td>';
		}


//			/*<!-- 5-->*/$tabla .='<td class="nowraponall" style="text-align:center">' . $report[$a]->Datedeliver . '</td>';
			/*<!-- 6-->*/$tabla .='<td class="nowraponall" style="text-align:right">' . number_format($report[$a]->margen ,2). '</td>';
			/*<!-- 7-->*/$tabla .='<td class="nowraponall" style="text-align:left"><a href="'.DOL_URL_ROOT.'/product/card.php?id='.$report[$a]->idfinishedproduct.'" target="blank">&nbsp;' . $report[$a]->Product2 . '</td><!-- finished product-->';
			/*<!-- 8-->*/$tabla .='<td class="nowraponall" style="text-align:right">' .number_format( $report[$a]->Commandederqty) . '</td>';
			/*<!-- 9-->*/$tabla .=' <td class="nowraponall"style="text-align:right" >' . $report[$a]->fpuom . '</td>';
			/*<!-- 10-->*/$tabla .='<td class="nowraponall" style="text-align:right">' . $report[$a]->fpstock . '</td>';
			/*<!-- 11-->*/$tabla .='<td class="nowraponall" style="text-align:left"><a href="'.DOL_URL_ROOT.'/custom/nomenclature/nomenclature.php?fk_product='.$report[$a]->idbom.'" target="blank">&nbsp;' . $report[$a]->title . '</a></td><!-- bom -->';
			/*<!-- 12-->*/$tabla .='<td class="nowraponall" style="text-align:left"><a href="'.DOL_URL_ROOT.'/product/card.php?id='.$report[$a]->id_fkproduct.'" target="blank">' . $report[$a]->RAWMATERIAL . '</a></td>';
			/*<!-- 13-->*/$tabla .='<td class="nowraponall"style="text-align:right" >' . $report[$a]->Qty . '</td>';
			/*<!-- 14-->*/$tabla .='<td class="nowraponall" style="text-align:right">' . $report[$a]->uma . '</td>';

			/*<!-- 15-->*/$tabla .='<td class="nowraponall" style="text-align:right">' .number_format($nuevovalor123).'</td>';

			/*<!-- 17-->*/$tabla .='<td class="nowraponall" style="text-align:right">' .  number_format($report[$a]->rmstock). '</td>';


//			/*<!-- 17a-->*/$tabla .='<th class="titlefield" style="text-align:center"><b>'.$stocknuevo.'</b> </th>';
			/*<!-- 18-->*/$tabla .='<td class="nowraponall" style="text-align:right">' . $report[$a]->um . '</td>';
			/*<!-- 19-->*/$tabla .='<td class="nowraponall"style="text-align:right" >' . $report[$a]->leadtime . '</td>';
			/*<!-- 20-->*/$tabla .='<td class="nowraponall" style="text-align:center">';	if(getNomenclature($report[$a]->id_fkproduct )){ $tabla .= 'YES'; }else{$tabla .= 'NO';} $tabla .= 	'</td>';
			/*<!-- 21-->*/$tabla .='<td class="nowraponall" style="text-align:right">'.getSubproducts(getNomenclature($report[$a]->id_fkproduct )).'</td>';
			// /*<!-- 22-->*/$tabla .='<td class="nowraponall" style="text-align:center">&nbsp;SA UOM</td>';

			/*<!-- 23-->*/$tabla .='<td class="nowraponall" style="text-align:center"><a href="'.DOL_URL_ROOT.'/custom/of/fiche_of.php?id='.$report[$a]->ofrowid.'&mainmenu=of&leftmenu=" target="blank">' . $report[$a]->ofnumero . '</td>';
			/*<!-- 24-->*/$tabla .='<td class="nowraponall" style="text-align:center">' . $report[$a]->ofstatus . '</td>';
			/*<!-- 25-->*/$tabla .='<td class="nowraponall" style="text-align:center"><a href="'.DOL_URL_ROOT.'/custom/of/fiche_of.php?id='.$report[$a]->fk_assetOf_parentrowid.'&mainmenu=of&leftmenu=" target="blank">' . $report[$a]->fk_assetOf_parent . '</a></td>';
			// /*<!-- 26-->*/$tabla .='<td class="nowraponall" style="text-align:center">&nbsp;MO CHILDREN</td>';
			//$validacionexistencia=getProductPO($report[$a]->idfinishedproduct, $report[$a]->POrowid);
			$validacionexistencia=getProductPO($report[$a]->principalid, $report[$a]->POrowid);
			if($validacionexistencia){
				/*<!-- 27-->*/$tabla .='<td class="nowraponall" style="text-align:center"><a href="'.DOL_URL_ROOT.'/fourn/commande/card.php?id='.$report[$a]->POrowid.'" target="blank">' . $report[$a]->POref . '</a></td>';

				$sql123 ="   select * from ".MAIN_DB_PREFIX."commande_fournisseurdet lcf  where fk_commande =".$report[$a]->POrowid." and fk_product = ".$report[$a]->principalid ;
				$res1=  $db->query($sql123);
				$datosPO = $db->fetch_object($res1);


				///*<!-- 27a-->*/$tabla .='<td class="nowraponall" style="text-align:center">' . $report[$a]->POqty . '</td>';
				/*<!-- 27a-->*/$tabla .='<td class="nowraponall" style="text-align:center">' . $datosPO->qty. '</td>';
				$sql123 ="   select * from ".MAIN_DB_PREFIX."commande_fournisseur lcf  where rowid =".$report[$a]->POrowid;
				$res1=  $db->query($sql123);
				$datosPO = $db->fetch_object($res1);
				$fechaCorta = date("m-d-Y", strtotime($datosPO->date_livraison));

				/*<!-- 27a-->*/$tabla .='<td class="nowraponall" style="text-align:center">' . $fechaCorta. '</td>';

			}else{
				/*<!-- 27-->*/$tabla .='<td class="nowraponall" style="text-align:center"></td>';
				/*<!-- 27-->*/$tabla .='<td class="nowraponall" style="text-align:center"></td>';
				/*<!-- 27-->*/$tabla .='<td class="nowraponall" style="text-align:center"></td>';
			}


//			/*<!-- 28-->*/$tabla .='<td class="nowraponall" style="text-align:center">' . $report[$a]->temps_estime_fabrication . '</td>';
			if($report[$a]->productlength  == null || $report[$a]->productwidth == null   || $report[$a]->productheight == null  ){
		//		$tabla .= '<!--1 --><td style="text-align:center"></td>';
			}else{
//				/*<!-- 28-->*/$tabla .='<td class="nowraponall" style="text-align:center">' . $report[$a]->productlength . ' X ' . $report[$a]->productwidth . ' X ' . $report[$a]->productheight . '</td>';
//				/*<!-- 28-->*/$tabla .='<td class="nowraponall" style="text-align:center">' . $report[$a]->productlength . '</td>';
//				/*<!-- 28-->*/$tabla .='<td class="nowraponall" style="text-align:center">' . $report[$a]->productwidth . '</td>';
//				/*<!-- 28-->*/$tabla .='<td class="nowraponall" style="text-align:center">' . $report[$a]->productheight . '</td>';
//				/*<!-- 28-->*/$tabla .='<td class="nowraponall" style="text-align:center">' . $report[$a]->productwide . '</td>';
		//		$tabla .= '<!--1 --><td style="text-align:center"></td>';
			}


		//	/*<!-- 29-->*/$tabla .='<td class="nowraponall" style="text-align:center">'.getWorkstation(getNomenclature($report[$a]->id_fkproduct )).'</td>';
		//	/*<!-- 30-->*/$tabla .='<td class="nowraponall"style="text-align:center" >&nbsp;' . $report[$a]->rango . '</td>';


			/*<!-- 31 -->*/$tabla .='<td class="nowraponall" style="text-align:center"><input type="checkbox" id="' .  $report[$a]->idfinishedproduct . '-' .  $report[$a]->Commandederqty. '-' .  $report[$a]->id_fkproduct. '-' .  $report[$a]->Qty. '-'.$report[$a]->ORDERROWID.'-'.$report[$a]->ofrowid.'"  name="arrayproductos[]"></td>';
			///*<!-- 31 -->*/$tabla .='<td class="nowraponall" style="text-align:center"><input type="checkbox" id="' .  $report[$a]->ofrowid . '" value="' .  $report[$a]->ORDERROWID . '" name="arrayproductos[]"></td>';
			$tabla .= '</tr>';
	}
$tabla .= '</table>';
$tabla .= '</div>';
	//$tabla .= '<script src="js/jquery-3.4.1.min"></script>';
//	$tabla .= '<script src="js/jquery-3.4.1"></script>';
	$tabla .= "";


	/*
	 * IMPRESION 2DA TABLA
	 * */
	$numRowMaterial=count($SumasRawMaterial);
	if($numRowMaterial>0){
		$tabla2 = "<style>
						.resaltar{
							cursor: default;
							background-color: #afb5b7;
							color: #ffffff;
						}
					</style>";
		$tabla2 .= '<p><a href="javascript:mostrar();">Overview </a></p>';
		$tabla2 .='<div id="flotante1" style="display:none; width: 200px; " ><br><a href="javascript:cerrar();">Hide</a><table class="tagtable table table-striped minwidth200"  border="1 " id="alan1" name="alan1">';
		$tabla2 .='
		<!-- <tr class="liste_titre encabezado" id="encabezado"> <th class="titlefield"   > # </th>-->
		<!-- --><!-- <td class="titlefield" > rowid </td> -->';
		/*<!-- 2-->*/$tabla2 .='<th class="titlefield minwidth200" style="text-align:center"><b>RAW MATERIAL</b></th>';
		/*<!-- 3-->*/$tabla2 .='<th class="titlefield minwidth100" style="text-align:center"><b>QTY NEEDED </b></th>';
		/*<!-- 3-->*/$tabla2 .='<th class="titlefield minwidth100" style="text-align:center"><b>RM STOCK </b></th>';
		/*<!-- 3-->*/$tabla2 .='<th class="titlefield minwidth150" style="text-align:center"><b>TOTAL QTY NEEDED </b></th>';

		/*<!-- 3-->*/$tabla2 .='</tr>';

		for($y=0;$y<$numRowMaterial;$y++){
			if($SumasRawMaterial[$y]['rowmaterial'] == null){
				continue;
			}
			if($SumasRawMaterial[$y]['need'] < 0){
				$nuevostockmostrar= $SumasRawMaterial[$y]['need']*-1;
			}else{
				$nuevostockmostrar= $SumasRawMaterial[$y]['need']*1;
			}
			require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

			$prodrow = new Product($db);
			$extrafields = new ExtraFields($db);
			$extrafields->fetch_name_optionals_label($prodrow->table_element);
			$prodrow->fetch($SumasRawMaterial[$y]['rowmaterial'], null);
			$tabla2 .= '
	<!--<tr class="oddeven" > <td class="nowraponall" style="text-align:center">&nbsp;' . ($y + 1) . '</td>-->
			<!-- --> <td class="nowraponall" style="text-align:left"><a href="'.DOL_URL_ROOT.'/product/card.php?id='.$prodrow->id.'" target="blank">&nbsp;' . $prodrow->label . '</a></td> ';
			/*<!-- 2-->*/$tabla2 .='<td class="nowraponall" style="text-align:center">' .$nuevostockmostrar . '</td>';
			/*<!-- 2-->*/$tabla2 .='<td class="nowraponall" style="text-align:center">' .$prodrow->stock_reel . '</td>';
			$T=$nuevostockmostrar-$prodrow->stock_reel;
			if($T < 0){
				$T= 0;
			}else{
				$T= $T*1;
			}
			/*<!-- 2-->*/$tabla2 .='<td class="nowraponall" style="text-align:center">' .$T . '</td>';
			/*<!-- 3-->*/$tabla2 .='</tr>';


		}
		/*<!-- 2-->*/$tabla2 .='<td class="nowraponall" style="text-align:center"><b>RM LEAD TIME (days):</b></td>';
		/*<!-- 2-->*/$tabla2 .='<td class="nowraponall" style="text-align:center">' .$va . '</td>';
		/*<!-- 2-->*/$tabla2 .='<td class="nowraponall" style="text-align:center"></td>';
		/*<!-- 2-->*/$tabla2 .='<td class="nowraponall" style="text-align:center"></td>';
		$tabla2 .= '</table></div>';



		$tabla2 .='<div id="flotante1" style="display:none;"><br><a href="javascript:cerrar();">Hide</a><table class="tagtable "  border="1 " id="alan1" name="alan1">';
		/*<!-- 2-->*/$tabla2 .='<th class="titlefield" style="text-align:center"><b>RM LEAD TIME (days):</b></th>';

		$tabla2 .= '</table></div>';
	}

	/*
	 * IMPRESION 2DA TABLA
	 * */





	?>
<?php
	$tabla .= '	<script src="js/jquery-3.4.1"></script>';
	$tabla .= "
	<script>
		$(document).ready(() => {

			$('th').each(function(columna){
				$(this).hover(function () {
					$(this).addClass('resaltar');
				}, function (){
					$(this).removeClass('resaltar');

			});

	/*		$(this).click(function (){
				let registros = $('.alan').find('tbody > tr').get();

				registros.sort(function (a,b){
					let valor1 = $(a).children('td').eq(columna).text().toUpperCase();
					let valor2 = $(b).children('td').eq(columna).text().toUpperCase();

					return valor1 < valor2 ? -1: valor1 > valor2 ? 1 : 0;
				});
				$.each(registros, function (indice, elemento){
					$('tbody').append(elemento);
				});
			});*/
	const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;

	const comparer = (idx, asc) => (a, b) => ((v1, v2) =>
		v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2)
		)(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));

	// do the work...
	document.querySelectorAll('th').forEach(th => th.addEventListener('click', (() => {
		const table = th.closest('table');
		Array.from(table.querySelectorAll('tr:nth-child(n+2)'))
			.sort(comparer(Array.from(th.parentNode.children).indexOf(th), this.asc = !this.asc))
			.forEach(tr => table.appendChild(tr) );
	})));

		});

		});


          function mostrar() {
            div = document.getElementById('flotante1');
            div.style.display = '';
        }

        function cerrar() {
            div = document.getElementById('flotante1');
            div.style.display = 'none';
        }
	</script>

	";
	$tabla .= ' ';

	echo $tabla2."".$tabla;
}else{
	echo "NONE";
}
