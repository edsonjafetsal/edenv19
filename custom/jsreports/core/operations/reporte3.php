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

global $user, $db, $langs, $conf ;
require_once DOL_DOCUMENT_ROOT . '/custom/jsreports/core/operations/operations.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/order.lib.php';
require_once DOL_DOCUMENT_ROOT . '/custom/of/class/ordre_fabrication_asset.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/of/class/oftools.class.php';


$arrayproductos 	= GETPOST('arrayproductos', 'aZ09');
$arraycommande 		= GETPOST('arraycommande', 'aZ09');

$num =count($arrayproductos);
require_once DOL_DOCUMENT_ROOT . '/custom/jsreports/core/operations/operations.php';
$fecha= getFecha(dol_now());

$totalof= getTotalTable('assetOf');
//$totalof=56;
$total= $totalof[0]->id+1;
$numeroConCeros = str_pad($total, 5, "0", STR_PAD_LEFT);
$NUMERO="OF".$numeroConCeros;

$variables=null;
$links=null;
for($a=0;$a<$num;$a++){
	$datos=explode( '-', $arrayproductos[$a] );
	$array []= array(
		'idprodfinished' =>  $datos[0],
		'cantidad'    => number_format( $datos[1]),
		'rowmaterial'    => "$datos[2]",
		'qtyrowmaterial'    =>  $datos[3],
		'OrderID'    =>  $datos[4],
		'ofID'    =>  $datos[5]
	);
	$test=0;
	$co= getRecords('commande', $datos[4]);
	$of= getRecords('assetOf', $datos[5]);
	$links.= '<a href="'.DOL_URL_ROOT.'/commande/card.php?id='.$datos[4].'" target="blank">' . $co[0]->ref . '</a> ';
	//	$links.= '<a href="'.DOL_URL_ROOT.'/custom/of/fiche_of.php?id='.$datos[5].'" target="blank">' . $of[0]->numero . '</a>,&nbsp; ';
	$variables.= $datos[0];
	if(!(($a+1)==$num)){
		$variables.=', ';

	}
}
//$reporte= geSchedulingReport($variables);
//print"<pre>";
//print_r($array);
//print"</pre>";
$idco=3;

$notas= $links;
$sql = " INSERT INTO " . MAIN_DB_PREFIX . "assetOf (rowid,date_cre,date_maj,entity,fk_user,fk_assetOf_parent,fk_soc,fk_commande,fk_project,`rank`,temps_estime_fabrication,temps_reel_fabrication,mo_cost,mo_estimated_cost,compo_cost,compo_estimated_cost,total_cost,total_estimated_cost,ordre,numero,status,date_besoin,date_lancement,date_start,date_end,note,modelpdf) VALUES ";
$sql.= " ($total,'".$fecha[2]."','".$fecha[2]."',1.0,0,0,-1,-1,0,0,0.0,0.0,0.0,0.0,0.0,0.0,0.0,0.0,'ASAP','".$NUMERO."','DRAFT','".$fecha[2]."',NULL,NULL,NULL,'".$notas."',''); ";
$result = $db->query($sql);
if ($result) {
//print "OF CREARA ROWID: ".$NUMERO;
//https://foardpanel.local/custom/of/fiche_of.php?id=".$total;
	for($a=0;$a<$num;$a++){

		$totalLineas= getTotalTable('assetOf_line');
		$lineaNueva=$totalLineas[0]->id+1;
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "assetOf_line (rowid,date_cre,date_maj,entity,fk_assetOf,fk_product,fk_product_fournisseur_price,fk_entrepot,fk_nomenclature,nomenclature_valide,fk_commandedet,qty_needed,qty,qty_used,qty_stock,conditionnement,conditionnement_unit,pmp,qty_non_compliant,`type`,lot_number,measuring_units,note_private,fk_assetOf_line_parent) VALUES ";
		$sql.= " (".$lineaNueva.",'".$fecha[2]."','".$fecha[2]."',1,".$total.",".$array[$a]["idprodfinished"].",-2,0,0,1,0,8.0,".$array[$a]["cantidad"].",0.0,0.0,1.0,0.0,0.0,0.0,'TO_MAKE','','unit','',4); ";
		$result = $db->query($sql);
		$nomenclaturesBillOfMasterials= getNomenclarureProduct($array[$a]["idprodfinished"]);
		$numBillOfMaterials= count($nomenclaturesBillOfMasterials);
		if($nomenclaturesBillOfMasterials >0){
			for($BOF=0; $BOF<$numBillOfMaterials; $BOF++){

				$usadoBOF=$nomenclaturesBillOfMasterials[$BOF];
				$totalLineasBOF= getTotalTable('assetOf_line');
				$lineaNuevaBOF=$totalLineasBOF[0]->id+1;
					$sql = "INSERT INTO " . MAIN_DB_PREFIX . "assetOf_line (rowid,date_cre,date_maj,entity,fk_assetOf,fk_product,fk_product_fournisseur_price,fk_entrepot,fk_nomenclature,nomenclature_valide,fk_commandedet,qty_needed,qty,qty_used,qty_stock,conditionnement,conditionnement_unit,pmp,qty_non_compliant,`type`,lot_number,measuring_units,note_private,fk_assetOf_line_parent) VALUES ";
				$sql.= " (".$lineaNuevaBOF.",'".$fecha[2]."','".$fecha[2]."',1,".$total.",".$usadoBOF->fk_product.",-2,0,0,1,0,8.0,".$usadoBOF->qty.",0.0,0.0,1.0,0.0,0.0,0.0,'NEEDED','','unit','',4); ";
				$result = $db->query($sql);
				$test=0;
			}
		}
		$productoFInal= $array[$a]["idprodfinished"];

		$getWorkstationsOf=getworkstationOF($productoFInal);
		$as=0;
		$numworkstation= count($getWorkstationsOf);
		$test=0;

		if($numworkstation>0){
			for($awork=0;$awork<$numworkstation; $awork++){
				$workstationUso=$getWorkstationsOf[$awork];
				$totalLineas= getTotalTable('workstation');
				$NuevoWorkstarionID= ($totalLineas[0]->id)+1;
				//$sql  = " INSERT INTO " . MAIN_DB_PREFIX . "workstation (rowid,date_cre,date_maj,entity,fk_usergroup,name,background,`type`,code,nb_hour_prepare,nb_hour_manufacture,nb_hour_capacity,nb_ressource,thm,thm_machine,thm_overtime,thm_night,nb_hour_before,nb_hour_after,is_parallele) VALUES";
				//$sql .= " (".$NuevoWorkstarionID.",'".$fecha[2]."','".$fecha[2]."',1,0,'".$workstationUso->name."','#','".$workstationUso->type."','".$workstationUso->code."',".$workstationUso->nb_hour_prepare.",".$workstationUso->nb_hour_manufacture.",".$workstationUso->nb_hour_capacity.",".$workstationUso->nb_ressource.",".$workstationUso->thm.",".$workstationUso->thm_machine.",".$workstationUso->thm_overtime.",".$workstationUso->thm_night.",".$workstationUso->nb_hour_before.",".$workstationUso->nb_hour_after.",".$workstationUso->is_parallele."); ";
				//$result = $db->query($sql);
				$test=0;

				$totalLasset_workstation_of= getTotalTable('asset_workstation_of');
				$totalLasset_workstation_ofIDNuevo= ($totalLasset_workstation_of[0]->id)+1;
				$sql  = " INSERT INTO " . MAIN_DB_PREFIX . "asset_workstation_of (rowid,date_cre,date_maj,fk_assetOf,fk_asset_workstation,fk_project_task,nb_hour,nb_hour_real,nb_hour_prepare,rang,thm,nb_days_before_beginning,nb_days_before_reapro,note_private) VALUES ";
				$sql .= " (".$totalLasset_workstation_ofIDNuevo.",'".$fecha[2]."','".$fecha[2]."',".$total.",".$workstationUso->rowid.",0,".$workstationUso->nb_hour_before.",0.0,0.0,2.0,".$workstationUso->thm.",0.05,0.0,''); ";
				$result = $db->query($sql);
				$test=0;
			}
		}







	}

	?>

	<script>
		window.location='../of/fiche_of.php?id='+<?php echo $total?>;
	</script>
<?php


}

//$PDOdb = new TPDOdb;
//if ($conf->workstationatm->enabled && !class_exists('TWorkstation')) dol_include_once('workstationatm/class/workstation.class.php');
//$TCacheWorkstation = TWorkstation::getWorstations($PDOdb);
//$of = OFTools::_createOFCommande($PDOdb, $_REQUEST['TProducts'], $_REQUEST['TQuantites'], $_REQUEST['fk_commande'], $_REQUEST['fk_soc'], isset($_REQUEST['subFormAlone']));
