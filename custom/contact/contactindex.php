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
 *	\file       contact/contactindex.php
 *	\ingroup    contact
 *	\brief      Home page of contact top menu
 */

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
$langs->loadLangs(array("contact@contact"));

$action = GETPOST('action', 'aZ09');


// Security check
//if (! $user->rights->contact->myobject->read) accessforbidden();
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

 $extrajs = '';
 $extracss = '';
 $extrajs = array(
	 '/custom/hmv/inc/datatables/js/jquery.dataTables.min.js',
	 '/custom/hmv/inc/datatables/buttons/js/dataTables.buttons.min.js',
	 '/custom/hmv/inc/datatables/buttons/js/buttons.colVis.min.js',
	 '/custom/hmv/inc/datatables/buttons/js/buttons.html5.min.js',
	 'peticion.js'
 );


 $extracss = array(
	 '/custom/hmv/inc/datatables/css/jquery.dataTables.min.css',
	 '/custom/hmv/inc/datatables/buttons/css/buttons.dataTables.min.css',
	 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css'
 );
llxHeader("", $langs->trans("Contacts Area"), '', '', '', '', $extrajs, $extracss);
$title =$langs->trans("Contacts Area");
$textAfterTitle = 'Select the contact.';
$combinedText = '<b>' . $title . '</b><br>' . $textAfterTitle.'</br>';

print load_fiche_titre($combinedText, '', 'contact.png@contact');

print '<div class="fichecenter"><div class="fichethirdleft">';


/* BEGIN MODULEBUILDER DRAFT MYOBJECT
// Draft MyObject
if (! empty($conf->contact->enabled) && $user->rights->contact->read)
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

/* BEGIN MODULEBUILDER LASTMODIFIED MYOBJECT
// Last modified myobject
if (! empty($conf->contact->enabled) && $user->rights->contact->read)
{
	$sql = "SELECT s.rowid, s.ref, s.label, s.date_creation, s.tms";
	$sql.= " FROM ".MAIN_DB_PREFIX."contact_myobject as s";
	//if (! $user->rights->societe->client->voir && ! $socid) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
	$sql.= " WHERE s.entity IN (".getEntity($myobjectstatic->element).")";
	//if (! $user->rights->societe->client->voir && ! $socid) $sql.= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
	//if ($socid)	$sql.= " AND s.rowid = $socid";
	$sql .= " ORDER BY s.tms DESC";
	$sql .= $db->plimit($max, 0);

	$resql = $db->query($sql);
	if ($resql)
	{
		$num = $db->num_rows($resql);
		$i = 0;

		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<th colspan="2">';
		print $langs->trans("BoxTitleLatestModifiedMyObjects", $max);
		print '</th>';
		print '<th class="right">'.$langs->trans("DateModificationShort").'</th>';
		print '</tr>';
		if ($num)
		{
			while ($i < $num)
			{
				$objp = $db->fetch_object($resql);

				$myobjectstatic->id=$objp->rowid;
				$myobjectstatic->ref=$objp->ref;
				$myobjectstatic->label=$objp->label;
				$myobjectstatic->status = $objp->status;

				print '<tr class="oddeven">';
				print '<td class="nowrap">'.$myobjectstatic->getNomUrl(1).'</td>';
				print '<td class="right nowrap">';
				print "</td>";
				print '<td class="right nowrap">'.dol_print_date($db->jdate($objp->tms), 'day')."</td>";
				print '</tr>';
				$i++;
			}

			$db->free($resql);
		} else {
			print '<tr class="oddeven"><td colspan="3" class="opacitymedium">'.$langs->trans("None").'</td></tr>';
		}
		print "</table><br>";
	}
}
*/

//edson 30-01-2024
print '</div></div></div>';

$initialSwitchStatus = 'off'; // Define el estado inicial del interruptor
print "<script type='text/javascript'>
    document.addEventListener('DOMContentLoaded', function() {
        var initialSwitchStatus = '$initialSwitchStatus'; // Estado inicial del interruptor

        // Desactiva el select al cargar la página si el interruptor está en 'off'
        if (initialSwitchStatus === 'off') {
            document.getElementById('roles').disabled = true;
        }
    });

    function updateSwitchStatus(element) {
        var switchStatus = element.checked;

        // Activa o desactiva el select según el estado del switch
        document.getElementById('roles').disabled = !switchStatus;

        // Puedes realizar más acciones aquí según sea necesario
        var switchStatu = switchStatus ? 'On' : 'Off';

        // Actualiza el texto dentro del span con id
        document.getElementById('switchText').innerText = switchStatu;

        // Actualiza el icono dentro del span con id
        document.getElementById('switchIcon').innerText = switchStatus ? '✔️' : '❌';
    }
</script>";

//End Ajax
$root_url = "http://" . $_SERVER['HTTP_HOST'];
$nameDir = 'dolibarr';
print '<div id="loading" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.8); z-index: 9999;">';
print '<img style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" src="' . $root_url . '/'.$nameDir.'/custom/followup/gif/loading1.gif" alt="Loading..." />';
print '</div>';
$test=0;



print '<table>';
	print '	<tr>';
	print '		<td>';

	print '		</td>';
	print '		<td>';


	print '		</td>';
	print '	<tr>';

	// select


	print '	<tr>';
	print '		<td>';
  	$sqlPropal = "SELECT lp.fk_soc as societe from llx_propal lp where lp.rowid =  ".$_REQUEST["socid"];
  	$ressqlPropal = $db->query($sqlPropal);
	if($ressqlPropal){
		$objPropal = $db->fetch_object($ressqlPropal);
		$sql = "SELECT rowid as id, nom as nom FROM llx_societe where rowid = ".$objPropal->societe." order by nom ASC";
		$resql = $db->query($sql);
		$objrow =  $db->fetch_object($resql);
	}
	print '<label class ="form-label">Contact</label>';

	print '<select class="form-select" style="height:40px; width:400px"  aria-label="Default select example" name = "cliente" id = "cliente" disabled>';
	print '<option value = "" selected>         </option>';
//	foreach ($objrow as $cliente) {
		$selectedValue = $objrow->nom;
		$selected = ($selectedValue) ? 'selected' : '';
		print '<option value="' . $objrow->id . '" ' . $selected . '>' . $objrow->nom. "</option>";
//	}
	print '</select>';

	print '		</td>';

	print '		<td>&nbsp&nbsp&nbsp&nbsp</td>';
	print '		<td>';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
	require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
	$formcompany = new FormCompany($db);
	$sqlSocPeople = "select ls2.rowid as idsoc  from llx_societe ls ";
	$sqlSocPeople .= "left join llx_socpeople ls2 on ls2.fk_soc=ls.rowid ";
	$sqlSocPeople .= "where ls.rowid =".$objrow->id;
	$resSocPreople = $db->query($sqlSocPeople);
	if ($resSocPreople){
		$objrowSoc =  $db->fetch_object($resSocPreople);
		$objectContact = new Contact($db);
		$res = $objectContact->fetch($objrowSoc->idsoc, $user);
		$objectContact->fetchRoles();
		$objsoc = new Societe($db);
		$objsoc->fetch($objectContact->socid);
	}

		if (!empty($objectContact->socid)) {
			print '<tr><td class="titlefield">'.$langs->trans("ContactByDefaultFor").'</td>';
			print '<td colspan="3">';
			print $formcompany->showRoles("roles", $objectContact, 'edit', $objectContact->roles);

			print '</td></tr>';
		}

	print '		<td>&nbsp&nbsp&nbsp&nbsp</td>';


print'	</table> ';
$initialSwitchStatus = 'off';
print "
<script>
    function sendPostRequest() {
        var formData = new FormData();

        // Obtener el estado del interruptor
        var switchStatus = document.getElementById('processcountdays').checked;

        // Agregar el valor de \$action y el estado del interruptor a formData
        formData.append('action', 'updatesoc');
        formData.append('switchStatus', switchStatus ? 'on' : 'off');
        formData.append('socid', '" . htmlspecialchars($_REQUEST['socid'], ENT_QUOTES, 'UTF-8') . "');
 		// Obtener el elemento select múltiple
        var rolesSelect = document.getElementById('roles');
        var selectedRoles = Array.from(rolesSelect.selectedOptions).map(option => option.value);
        formData.append('roles', JSON.stringify(selectedRoles));

        $('#loading').show();
        fetch('contactindex.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => {
            $('#loading').hide();

			 if (switchStatus === false) {
            $.jnotify('Successfully Updated OFF', 'ok');
			} else if (switchStatus === true) {
				$.jnotify('Successfully Updated ON', 'ok');
			}
             window.location.href = '" . dol_buildpath('/comm/propal/card.php?id=' . htmlspecialchars($_REQUEST['socid'], ENT_QUOTES, 'UTF-8') , 1) . "';
            console.log(response);
        })
        .catch(error => {
            console.error('Error en la petición:', error);
        });
    }
</script>
";

//botones
print ' <br>
<div class="col-lg-5">
<a>
<div class="form-check form-switch">
  <input class="form-check-input" type="checkbox" id="processcountdays" value="on" style="background-color: #c4c6ca;" onchange="updateSwitchStatus(this)">
  <label class="form-check-label" for="processcountdays" style="padding-left: 10px;">
    <span id="switchText">Off</span>
    <span id="switchIcon">❌</span>
  </label>
</div>

</a>
<br><br>
    <a class="btn btn-secondary" aria-current="page" id="btn4" onclick="sendPostRequest()">GO</a>



    <br>
</div>
';
//<a class="btn btn-secondary" aria-current="page" id="btnreset" onclick="resetbtnhvm()" style="margin-left: 20px;">RESET</a>
require_once(DOL_DOCUMENT_ROOT . '/custom/contact/class/operations.php');
UpdateRoles();
// End of page
llxFooter();
$db->close();
