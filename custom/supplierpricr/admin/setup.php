<?php
/* Copyright (C) 2014-2022	 Charlene Benke <charlene@patas-monkey.com>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *   	\file	   htdocs/custom-tabs/admin/supplierpricr.php
 *		\ingroup	supplierpricr
 *		\brief	  Page to setup the module supplierpricr
 */

// Dolibarr environment
$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");		// For root directory
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");	// For "custom" directory


require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
dol_include_once('/supplierpricr/core/lib/supplierpricr.lib.php');

$langs->load("admin");
$langs->load("supplierpricr@supplierpricr");

if (! $user->admin) accessforbidden();

$type=array('yesno', 'texte', 'chaine');

$action = GETPOST('action', 'alpha');


/*
 * Actions
 */

// pas d'action juste une info



/*
 * View
 */
$page_name = $langs->trans("SupplierPricRSetup")." - " .$langs->trans("GeneralSetup");

$help_url='https://wiki.patas-monkey.com/index.php?title=SupplierPricR';

llxHeader('', $page_name, $help_url);


$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($page_name, $linkback, 'title_setup');

if ($action == 'blockonemptyreffourn') {
	// save the setting
	dolibarr_set_const($db, "SUPPLIERPRICR_BLOCKONEMPTYREFFOURN", GETPOST('value', 'int'), 'chaine', 0, '', $conf->entity);
	$mesg = "<font class='ok'>".$langs->trans("SetupSaved")."</font>";
}
$blockonemptyreffourn=!empty($conf->global->SUPPLIERPRICR_BLOCKONEMPTYREFFOURN)?$conf->global->SUPPLIERPRICR_BLOCKONEMPTYREFFOURN:"";

if ($action == 'quantityonlyone') {
	// save the setting
	dolibarr_set_const($db, "SUPPLIERPRICR_QTYONLYONE", GETPOST('value', 'int'), 'chaine', 0, '', $conf->entity);
	$mesg = "<font class='ok'>".$langs->trans("SetupSaved")."</font>";
}
$quantityonlyone=!empty($conf->global->SUPPLIERPRICR_QTYONLYONE)?$conf->global->SUPPLIERPRICR_QTYONLYONE:"";



$head = supplierpricr_admin_prepare_head();

dol_fiche_head($head, 'setup', $langs->trans("SupplierPricR"), -1, 'supplierpricr@supplierpricr');


print '<table class="noborder" >';

print '<tr class="liste_titre">';
print '<td width="20%">'.$langs->trans("SupplierPricRSetting").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td align=right nowrap >'.$langs->trans("Value").'</td>';
print '</tr>'."\n";

print '<tr >';
print '<td valign=top>'.$langs->trans("BlockOnEmptyRefFourn").'</td>';
print '<td>'.$langs->trans("InfoBlockOnEmptyRefFourn").'</td>';
print '<td align=right >';
if ($blockonemptyreffourn =="1") {
	print '<a href="'.$_SERVER["PHP_SELF"].'?action=blockonemptyreffourn&token='.newToken().'&value=0">';
	print img_picto($langs->trans("Activated"), 'switch_on').'</a>';
} else {
	print '<a href="'.$_SERVER["PHP_SELF"].'?action=blockonemptyreffourn&token='.newToken().'&value=1">';
	print img_picto($langs->trans("Disabled"), 'switch_off').'</a>';
}
print '</td>';
print '</tr>'."\n";

print '<tr >';
print '<td valign=top>'.$langs->trans("QuantityOnlyOne").'</td>';
print '<td>'.$langs->trans("InfoQuantityOnlyOne").'</td>';
print '<td align=right >';
if ($quantityonlyone =="1") {
	print '<a href="'.$_SERVER["PHP_SELF"].'?action=quantityonlyone&token='.newToken().'&value=0">';
	print img_picto($langs->trans("Activated"), 'switch_on').'</a>';
} else {
	print '<a href="'.$_SERVER["PHP_SELF"].'?action=quantityonlyone&token='.newToken().'&value=1">';
	print img_picto($langs->trans("Disabled"), 'switch_off').'</a>';
}
print '</td>';
print '</tr>'."\n";
print '</table>'."\n";


print '<br>'; 

/*
 *  Infos pour le support
 */
/* print '<br>'; */
libxml_use_internal_errors(true);
$sxe = simplexml_load_string(nl2br(file_get_contents('../changelog.xml')));
if ($sxe === false) {
	echo "Erreur lors du chargement du XML\n";
	foreach (libxml_get_errors() as $error) 
		print $error->message;
	exit;
} else
	$tblversions=$sxe->Version;

$currentversion = $tblversions[count($tblversions)-1];

print '<table class="noborder" width="100%">'."\n";
print '<tr class="liste_titre">'."\n";
print '<td width=20%>'.$langs->trans("SupportModuleInformation").'</td>'."\n";
print '<td>'.$langs->trans("Value").'</td>'."\n";
print "</tr>\n";
print '<tr ><td >'.$langs->trans("DolibarrVersion").'</td><td>'.DOL_VERSION.'</td></tr>'."\n";
print '<tr ><td >'.$langs->trans("ModuleVersion").'</td>';
print '<td>'.$currentversion->attributes()->Number." (".$currentversion->attributes()->MonthVersion.')</td></tr>'."\n";
print '<tr ><td >'.$langs->trans("PHPVersion").'</td><td>'.version_php().'</td></tr>'."\n";
print '<tr ><td >'.$langs->trans("DatabaseVersion").'</td>';
print '<td>'.$db::LABEL." ".$db->getVersion().'</td></tr>'."\n";
print '<tr ><td >'.$langs->trans("WebServerVersion").'</td>';
print '<td>'.$_SERVER["SERVER_SOFTWARE"].'</td></tr>'."\n";
print '<tr>'."\n";
print '<td align=center colspan="2"><b>'.$langs->trans("SupportModuleInformationDesc").'<b></td></tr>'."\n";
print "</table>\n";

// Show messages
dol_htmloutput_mesg($mesg, '', 'ok');

// Footer
llxFooter();
$db->close();