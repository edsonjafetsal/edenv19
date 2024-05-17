<?php

require '../config.php';
//require('../lib/asset.lib.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/productbatch.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/stock/class/productlot.class.php';
require_once (__DIR__ . '/MigrationAsset.class.php');


dol_include_once('/assetatm/class/asset.class.php');
dol_include_once('/assetatm/lib/asset.lib.php');

global $user,$langs;

$langs->load('assetatm@assetatm');
$langs->load('admin');

if (!($user->admin)) accessforbidden();

$action=GETPOST('action','alpha');
$migration = NEW MigrationAsset($db);




if (isset($action) && $action == 'simulate') {

	if ($migration::DEBUG ){
		$migration->dropTest();
	}

	$outPut = $migration->action_equipment(MigrationAsset::SIMULATION);


}elseif (isset($action) && $action == 'execute') {

	if ($migration::DEBUG ){
		$migration->dropTest();
	}
	$outPut = $migration->action_equipment(MigrationAsset::EXECUTION);

}






$formCore=new TFormCore;
$form=new Form($db);

llxHeader('',$langs->trans("AssetSetup"), '');
$head = assetatmAdminPrepareHead();
$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';




/*
VIEW
*/

print load_fiche_titre($langs->trans("Script_migration"),$linkback);
print dol_get_fiche_head($head, 1, $langs->trans("Asset"), 0, 'pictoof@asset');


print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';


if (in_array('productbatch' ,$conf->modules)) {


	print '<td>' . $langs->trans("Parameters") . '</td>' . "\n";
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="center" width="100">' . $langs->trans("Value") . '</td></tr>' . "\n";

	print '<tr ' . $bc[$var] . '>';
	print '<td>' . $langs->trans("simulate_equipment_switch_to_internal_lot_module") . '</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300" style="white-space:nowrap;">';
	print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="simulate">';
	print '<input type="submit" class="button" value="' . $langs->trans("simulate") . '">';
	print '</form>';
	print '</td></tr>';


	print '<tr ' . $bc[$var] . '>';
	print '<td>' . $langs->trans("execute_equipment_switch_to_internal_lot_module") . '</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300" style="white-space:nowrap;">';
	print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="execute">';
	print '<input type="submit" class="button" value="' . $langs->trans("execute") . '">';
	print '</form>';
	print '</td></tr>';
	print '<tr><td>' . $outPut . '</td></tr>';
	print '</table>';

}else{
	print '<td>' . $langs->trans("Error_required") . '</td>' . "\n";
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="center" width="100"></td></tr>' . "\n";


	print '<tr >';
	print '<td class="text-warning">' . $langs->trans("internal_lot_must_be_activated") . '</td>';
	print '<td align="center" width="20"></td>';
	print '</tr>';

	print '</table>';

}






