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
if (!$res && file_exists("../../../../main.inc.php")) $res = @include "../../../../main.inc.php";

global $user, $db, $langs;
require_once DOL_DOCUMENT_ROOT . '/custom/jsreports/core/operations/operations.php';



$arrayproductos 	= GETPOST('arrayproductos', 'aZ09');
$arraycommande 	= GETPOST('arraycommande', 'aZ09');
$valoresCheck	= GETPOST('valoresCheck', 'aZ09');
$arrayproductos1 = GETPOST('arrayproductos1', 'aZ09');
$num =count($valoresCheck);
$asd=$valoresCheck[0];

$columns 	= GETPOST('columns', 'aZ09');

$countnum= count($arrayproductos);
//$arrayp = [];
for($a=0; $a<$countnum; $a++){
	$arrayp .=$arrayproductos[$a];
	if($a< ($countnum)-1) $arrayp .=" ,";
}

$report = geSchedulingReport("$arrayp");
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

	$tabla = '<table class="border centpercent"  border="1">';
	$tabla .= '<tbody>';
	$tabla .= '	<tr class="liste_titre" >';
	$tabla .= '<!--1 --><td style="text-align:center">Total: '.$num.'  </td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
	$tabla .= '<!--1 --><td style="text-align:center"></td>';
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
	$tabla .='</tr>
		<tr class="liste_titre">
		<!--1 --><td class="titlefield" > # </td>
		<!-- --><!-- <td class="titlefield" > rowid </td> -->';
	$tabla .='<td class="titlefield" style="text-align:center"><b>CUSTOMER</b></td>';
		/*<!-- 3-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>ORDER DATE</b></td>';
		/*<!-- 4-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>ORDER REF</b></td>';
		/*<!-- 5-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>DELIVERY DATE</b></td>';
		/*<!-- 6-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>ORDER MARGIN </b></td>';
		/*<!-- 7-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>FINISHED PRODUCT </b></td>';
		/*<!-- 8-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>QTY REQUESTED</b></td>';
		/*<!-- 9-->*/$tabla .='<td class="titlefield" ><b>FP UOM</b></td> ';
		/*<!-- 10-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>FP STOCK</b></td>';
		/*<!-- 11-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>BOM</b></td>';
		/*<!-- 12-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>RAW MATERIAL</b></td>';
		/*<!-- 13-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>RM BOM QTY</b></td>';
		/*<!-- 14-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>RM PRODUCTION UOM</b> </td>';
		/*<!-- 15-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>QTY NEEDED</b></td>';

		/*<!-- 17-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>RM STOCK</b> </td>';
		/*<!-- 18-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>RM BUYING UOM</b> </td>';
		/*<!-- 19-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>RM LEAD TIME</b></td>';
		/*<!-- 20-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>SUBASSEMBLY STOCK</b></td>';
		/*<!-- 21-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>SUBASSEMBLY RM</b></td>';
		// /*<!-- 22-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>SA UOM </b></td>';

	/*<!-- 23-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>MO</b> </td>';
	/*<!-- 24-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>MO STATUS</b> </td>';
	/*<!-- 25-->*/$tabla .='<td class="nowraponall" style="text-align:center"><b>MO PARENT</b> </td>';
	// /*<!-- 26-->*/$tabla .='<td class="nowraponall" style="text-align:center"><b>MO CHILDREN</b> </td>';
	/*<!-- 27-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>PRODUCTION ORDER</b> </td>';
	/*<!-- 268-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>ESTIMATED PRODUCTION TIME</b> </td>';

		/*<!-- 29-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>WORK STATION</b> </td>';
		/*<!-- 30-->*/$tabla .='<td class="titlefield" style="text-align:center"><b>RANK </b></td>';
		/*<!-- 31-->*/$tabla .='<td class="titlefield" style="text-align:center"><input type="checkbox" name="select-all" id="select-all" /></td>';
	$tabla .='</tr>';
	for ($a = 0; $a < $num; $a++) {
		$NEEDED = $report[$a]->Commandederqty*$report[$a]->Qty;
		$tabla .= '	<tr class="oddeven" >
			<!-- 1--><td class="nowraponall" style="text-align:center">&nbsp;' . ($a + 1) . '</td>
			<!-- --><!-- <td class="nowraponall" style="text-align:center">&nbsp;' . $report[$a]->principalid  . '</td> -->';
		/*<!-- 2-->*/$tabla .='<td class="nowraponall" style="text-align:center"><a href="'.DOL_URL_ROOT.'/societe/card.php?id='.$report[$a]->societerowid.'" target="blank">' .$report[$a]->societe. '</a></td>';
		/*<!-- 3-->*/$tabla .='<td class="nowraponall" style="text-align:center">&nbsp;' . $report[$a]->Datecomande . '</td>';
		/*<!-- 4-->*/$tabla .='<td class="nowraponall" style="text-align:center"> <a href="'.DOL_URL_ROOT.'/commande/card.php?id='.$report[$a]->ORDERROWID.'" target="blank">' . $report[$a]->Commande . '</a></td>';
		/*<!-- 5-->*/$tabla .='<td class="nowraponall" style="text-align:center">&nbsp;' . $report[$a]->Datedeliver . '</td>';
		/*<!-- 6-->*/$tabla .='<td class="nowraponall" style="text-align:right">$&nbsp;' . number_format($report[$a]->margen ,2). '</td>';
		/*<!-- 7-->*/$tabla .='<td class="nowraponall" style="text-align:left"><a href="'.DOL_URL_ROOT.'/product/card.php?id='.$report[$a]->idfinishedproduct.'" target="blank">&nbsp;' . $report[$a]->Product2 . '</td><!-- finished product-->';
		/*<!-- 8-->*/$tabla .='<td class="nowraponall" style="text-align:right">&nbsp;' .number_format( $report[$a]->Commandederqty) . '</td>';
		/*<!-- 9-->*/$tabla .=' <td class="nowraponall"style="text-align:right" >' . $report[$a]->fpuom . '</td>';
		/*<!-- 10-->*/$tabla .='<td class="nowraponall" style="text-align:right">' . $report[$a]->fpstock . '</td>';
		/*<!-- 11-->*/$tabla .='<td class="nowraponall" style="text-align:left"><a href="'.DOL_URL_ROOT.'/custom/nomenclature/nomenclature.php?fk_product='.$report[$a]->idbom.'" target="blank">&nbsp;' . $report[$a]->title . '</a></td><!-- bom -->';
		/*<!-- 12-->*/$tabla .='<td class="nowraponall" style="text-align:left"><a href="'.DOL_URL_ROOT.'/product/card.php?id='.$report[$a]->id_fkproduct.'" target="blank">' . $report[$a]->RAWMATERIAL . '</a></td>';
		/*<!-- 13-->*/$tabla .='<td class="nowraponall"style="text-align:right" >&nbsp;' . $report[$a]->Qty . '</td>';
		/*<!-- 14-->*/$tabla .='<td class="nowraponall" style="text-align:right">&nbsp;' . $report[$a]->uma . '</td>';
		/*<!-- 15-->*/$tabla .='<td class="nowraponall" style="text-align:right">&nbsp;' .number_format($NEEDED).'</td>';

		/*<!-- 17-->*/$tabla .='<td class="nowraponall" style="text-align:right">&nbsp;' .  number_format($report[$a]->rmstock ). '</td>';
		/*<!-- 18-->*/$tabla .='<td class="nowraponall" style="text-align:right">&nbsp;' . $report[$a]->um . '</td>';
		/*<!-- 19-->*/$tabla .='<td class="nowraponall"style="text-align:right" >&nbsp;' . $report[$a]->leadtime . '</td>';
		/*<!-- 20-->*/$tabla .='<td class="nowraponall" style="text-align:center">';	if(getNomenclature($report[$a]->id_fkproduct )){ $tabla .= 'YES'; }else{$tabla .= 'NO';} $tabla .= 	'</td>';
		/*<!-- 21-->*/$tabla .='<td class="nowraponall" style="text-align:right">'.getSubproducts(getNomenclature($report[$a]->id_fkproduct )).'</td>';
		// /*<!-- 22-->*/$tabla .='<td class="nowraponall" style="text-align:center">&nbsp;SA UOM</td>';

		/*<!-- 23-->*/$tabla .='<td class="nowraponall" style="text-align:center"><a href="'.DOL_URL_ROOT.'/custom/of/fiche_of.php?id='.$report[$a]->ofrowid.'&mainmenu=of&leftmenu=" target="blank">' . $report[$a]->ofnumero . '</td>';
		/*<!-- 24-->*/$tabla .='<td class="nowraponall" style="text-align:center">&nbsp;' . $report[$a]->ofstatus . '</td>';
		/*<!-- 25-->*/$tabla .='<td class="nowraponall" style="text-align:center">&nbsp;<a href="'.DOL_URL_ROOT.'/custom/of/fiche_of.php?id='.$report[$a]->fk_assetOf_parentrowid.'&mainmenu=of&leftmenu=" target="blank">' . $report[$a]->fk_assetOf_parent . '</a></td>';
		// /*<!-- 26-->*/$tabla .='<td class="nowraponall" style="text-align:center">&nbsp;MO CHILDREN</td>';
		/*<!-- 27-->*/$tabla .='<td class="nowraponall" style="text-align:center"><a href="'.DOL_URL_ROOT.'/fourn/commande/card.php?id='.$report[$a]->POrowid.'" target="blank">' . $report[$a]->POref . '</a></td>';

		/*<!-- 28-->*/$tabla .='<td class="nowraponall" style="text-align:center">&nbsp;' . $report[$a]->temps_estime_fabrication . '</td>';
		/*<!-- 29-->*/$tabla .='<td class="nowraponall" style="text-align:center">'.getWorkstation(getNomenclature($report[$a]->id_fkproduct )).'</td>';
		/*<!-- 30-->*/$tabla .='<td class="nowraponall"style="text-align:center" >&nbsp;' . $report[$a]->rango . '</td>';
		/*<!-- 31 -->*/$tabla .='<td class="nowraponall" style="text-align:center">&nbsp;<input type="checkbox" id="' .  $report[$a]->ofrowid . '" value="' .  $report[$a]->ORDERROWID . '" name="arrayproductos[]"></td>';
		$tabla .= '</tr>';
	}
	$tabla .= ' </tbody> </table>';

	echo $tabla;
}else{
	echo "NONE";
}
