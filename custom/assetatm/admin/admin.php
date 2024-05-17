<?php

	require '../config.php';
	//require('../lib/asset.lib.php');
	require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
	dol_include_once('/assetatm/lib/asset.lib.php');

	global $user,$langs;

	$langs->load('assetatm@assetatm');
	$langs->load('admin');

	if (!($user->admin)) accessforbidden();

	$action=__get('action','');
	$formCore=new TFormCore;
	$form=new Form($db);

    /*
     * Actions
     */
    if (preg_match('/set_(.*)/',$action,$reg))
    {
        $code=$reg[1];
        if (dolibarr_set_const($db, $code, GETPOST($code), 'chaine', 0, '', $conf->entity) > 0)
        {
        	
        	if($code == 'USE_ASSET_IN_ORDER' && GETPOST($code)>0) {
        		
        		dol_include_once('/core/class/extrafields.class.php');
        		$extrafields=new ExtraFields($db);
        		$res = $extrafields->addExtraField('fk_asset', 'Asset', 'sellist', 0, '11', 'propaldet',0,0,'', unserialize('a:1:{s:7:"options";a:1:{s:28:"assetatm:serial_number:rowid";N;}}') );
        		$res = $extrafields->addExtraField('fk_asset', 'Asset', 'sellist', 0, '11', 'commandedet',0,0,'', unserialize('a:1:{s:7:"options";a:1:{s:28:"assetatm:serial_number:rowid";N;}}') );
        		$res = $extrafields->addExtraField('fk_asset', 'Asset', 'sellist', 0, '11', 'facturedet',0,0,'', unserialize('a:1:{s:7:"options";a:1:{s:28:"assetatm:serial_number:rowid";N;}}') );
        		
        		
        	}
        	
            header("Location: ".$_SERVER["PHP_SELF"]);
            exit;
        }
        else
        {
            dol_print_error($db);
        }
    }

	if($action=='save') {

		if(isset($_REQUEST['TAsset']))
		{
			foreach($_REQUEST['TAsset'] as $name=>$param) {

				dolibarr_set_const($db, $name, $param, 'chaine', 0, '', $conf->entity);

			}
		}
		if(isset($_FILES['template']) && !empty($_FILES['template']['tmp_name'])) {

			copy($_FILES['template']['tmp_name'],'../exempleTemplate/templateOF.odt');

		}

		setEventMessage($langs->trans("AssetConfigurationSaved"));

	}

	llxHeader('',$langs->trans("AssetSetup"), '');

	$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
	print_fiche_titre($langs->trans("AssetSetup"),$linkback);

	$head = assetatmAdminPrepareHead();
	dol_fiche_head($head, 0, $langs->trans("Asset"), 0, 'pictoof@asset');

    print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("Parameters").'</td>'."\n";
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("UsetAssetProductionAttributs").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="center" width="300">';
	print ajax_constantonoff('ASSET_USE_PRODUCTION_ATTRIBUT');
	print '</td></tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("ASSET_MAKE_STOCK_MOVEMENTS_AT_CREATION_DELETION").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="center" width="300">';
	print ajax_constantonoff('ASSET_MAKE_STOCK_MOVEMENTS_AT_CREATION_DELETION');
	print '</td></tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("UseBatchNumberInOf").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="center" width="300">';
	print ajax_constantonoff('USE_LOT_IN_OF');
	print '</td></tr>';

    $var=!$var;
    print '<tr '.$bc[$var].'>';
    print '<td>'.$langs->trans("Asset_show_DLUO").'</td>';
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    print '<td align="center" width="20">&nbsp;</td>';
    print '<td align="center" width="300">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="set_ASSET_SHOW_DLUO">';
    print $form->selectarray('ASSET_SHOW_DLUO', array(1=>$langs->trans('Yes'),0=>$langs->trans('No')), $conf->global->ASSET_SHOW_DLUO);
    print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
    print '</form>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("UseAssetInDoc").'</td>';
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="center" width="300">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="set_USE_ASSET_IN_ORDER">';
	print $form->selectarray('USE_ASSET_IN_ORDER', array(1=>$langs->trans('Yes'),0=>$langs->trans('No')), $conf->global->USE_ASSET_IN_ORDER);
	print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
	print '</form>';
	
	
	print '</td></tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("Asset_DefaultCompose_fourni").'</td>';
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="center" width="300">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="set_ASSET_DEFAULT_COMPOSE_FOURNI">';
	print $form->selectarray('ASSET_DEFAULT_COMPOSE_FOURNI', array(1=>$langs->trans('Yes'),0=>$langs->trans('No')), $conf->global->ASSET_DEFAULT_COMPOSE_FOURNI);
	print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
	print '</form>';
	print '</td></tr>';

        $var=!$var;
        print '<tr '.$bc[$var].'>';
        print '<td>'.$langs->trans("Asset_HideNoStockOnList").'</td>';
        print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
        print '<td align="center" width="20">&nbsp;</td>';
        print '<td align="center" width="300">';
        print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
        print '<input type="hidden" name="action" value="set_ASSET_HIDE_NO_STOCK_ON_LIST">';
        print $form->selectarray('ASSET_HIDE_NO_STOCK_ON_LIST', array(1=>$langs->trans('Yes'),0=>$langs->trans('No')), $conf->global->ASSET_HIDE_NO_STOCK_ON_LIST);
        print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
        print '</form>';
        print '</td></tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
    print '<td>'.$langs->trans("AssetDefaultDLUO").'</td>';
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    print '<td align="center" width="20">&nbsp;</td>';
    print '<td align="center" width="300">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="set_ASSET_DEFAULT_DLUO">';
    print $formCore->number("", "ASSET_DEFAULT_DLUO",$conf->global->ASSET_DEFAULT_DLUO,10);
    print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
    print '</form>';
    print '</td></tr>';

    $var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("set_ABRICOT_WKHTMLTOPDF_CMD").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300" style="white-space:nowrap;">';
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="set_ABRICOT_WKHTMLTOPDF_CMD">';
	print $formCore->texte('', 'ABRICOT_WKHTMLTOPDF_CMD', (empty($conf->global->ABRICOT_WKHTMLTOPDF_CMD) ? '' : $conf->global->ABRICOT_WKHTMLTOPDF_CMD), 80,255,' placeholder="wkhtmltopdf" ');
	print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
	print '</form>';
	print '</td></tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("Asset_useUniqueSerialNumber").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="center" width="300">';
	print ajax_constantonoff('ASSET_USE_UNIQUE_SERIAL_NUMBER');
	print '</td></tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("Asset_showOriginStockMovementOnList").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="center" width="300">';
	print ajax_constantonoff('ASSET_SHOW_ORIGIN_STOCK_MOVEMENT_ON_LIST');
	print '</td></tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("Asset_searchProductOnMultiEntities").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="center" width="300">';
	print ajax_constantonoff('ASSET_SEARCH_PRODUCT_ON_MULTI_ENTITIES');
	print '</td></tr>';


	print '</table>';


	showParameters($formCore);

function showParameters(&$formCore) {
	global $db,$conf,$langs,$bc;
	dol_include_once('/product/class/html.formproduct.class.php');

	$formProduct = new FormProduct($db);

	?><form action="<?php echo $_SERVER['PHP_SELF'] ?>" name="load-<?php echo $typeDoc ?>" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="action" value="save" />


		<p align="right">
			<input class="button" type="submit" name="bt_save" value="<?php echo $langs->trans('Save') ?>" />
		</p>

	</form>
	<p align="center" style="background: #fff;">
	   Développé par <br />
	   <a href="http://www.atm-consulting.fr/" target="_blank"><img src="../img/ATM_logo_petit.jpg" /></a>
	</p>

	<br /><br />
	<?php
}
