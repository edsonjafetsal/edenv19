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

$sql = "SELECT ref FROM llx_commande ";
$resql = $db->query($sql);
$i = 0;
$num = $db->num_rows($resql);
	$b=0;
foreach($resql as $values1)
{
	foreach($values1 as $value1)
	{
		$prod[$b][].=	$value1;
	}
	$b=$b+1;
}

print '<pre> arreglo1 ';print_r($resql);print '</pre>';

while($num <= $sql) {
	$sql = "SELECT co.ref, co.date_commande, co.total_ttc, cd. rowid, cd.label FROM llx_commande AS co
    LEFT JOIN llx_commandedet AS cd ON co.rowid = cd.fk_commande
    ORDER BY co.ref";

	$resql = $db->query($sql);
	$num = $db->num_rows($resql);

	$a1 = 0;
	$b = 0;

	foreach ($resql as $value) {
		foreach ($value as $a) {

			$data[$b][] .= $a;
		}
		$b = $b + 1;
	}
	$c = 0;
	$d = 0;

	for ($a1 = 0; $a1 < $b; $a1++) {
		if ($a1 == 0) {
			$co = $data[$a1][0];
		}
		if ($co == $data[$a1][0]) {
			$arreglo1[$c][] .= $data;
			$c = $c + 1;
		} else {
			$arreglo2[$d][] .= $data;
			$d = $d + 1;
		}


	}

//print '<pre> arreglo1 ';print_r($arreglo1);print '</pre>';
//print '<pre> arreglo2 ';print_r($arreglo2);print '</pre>';
	print'<table class="tftable" border="1">
	<tr><th colspan="5" ><p align="center">Sales by Customer</p></th></tr>
	<tr><p align="center">';

	print '<th>CO</th>';
	print '<th>date</th>';
	print '<th>3</th>';
	print '<th>4</th>';

	foreach ($data as $value) {
		print '<tr>';
		echo '<td>' . $value[0] . '</td>';
		print'<td>' . $value[1] . '</td>';
		print'<td>' . $value[2] . '</td>';
		print'<td>' . $value[3] . '</td>';
		print '</tr>';

	}

}
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


// End of page
llxFooter();
$db->close();
