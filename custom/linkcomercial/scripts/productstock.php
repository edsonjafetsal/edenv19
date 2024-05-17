<?php
require_once DOL_DOCUMENT_ROOT.'/custom/linkcomercial/lib/replenishment.lib.php';


$prod = new Product($db);

print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST" name="formulaire">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="fk_supplier" value="'.$fk_supplier.'">';
print '<input type="hidden" name="fk_entrepot" value="'.$fk_entrepot.'">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
print '<input type="hidden" name="type" value="'.$type.'">';
print '<input type="hidden" name="linecount" value="'.$num.'">';
print '<input type="hidden" name="action" value="order">';
print '<input type="hidden" name="mode" value="'.$mode.'">';
if (!empty($conf->global->STOCK_ALLOW_ADD_LIMIT_STOCK_BY_WAREHOUSE) && $fk_entrepot > 0) {
	$sqldesiredtock = $db->ifsql("pse.desiredstock IS NULL", "p.desiredstock", "pse.desiredstock");
	$sqlalertstock = $db->ifsql("pse.seuil_stock_alerte IS NULL", "p.seuil_stock_alerte", "pse.seuil_stock_alerte");
} else {
	$sqldesiredtock = 'p.desiredstock';
	$sqlalertstock = 'p.seuil_stock_alerte';
}

$sql = 'SELECT p.rowid, p.ref, p.label, p.description, p.price,';
$sql .= ' p.price_ttc, p.price_base_type,p.fk_product_type,';
$sql .= ' p.tms as datem, p.duration, p.tobuy,';
$sql .= ' p.desiredstock, p.seuil_stock_alerte,';
if (!empty($conf->global->STOCK_ALLOW_ADD_LIMIT_STOCK_BY_WAREHOUSE) && $fk_entrepot > 0) {
	$sql .= ' pse.desiredstock as desiredstockpse, pse.seuil_stock_alerte as seuil_stock_alertepse,';
}
$sql .= ' '.$sqldesiredtock.' as desiredstockcombined, '.$sqlalertstock.' as seuil_stock_alertecombined,';
$sql .= ' s.fk_product,';
$sql .= ' SUM('.$db->ifsql("s.reel IS NULL", "0", "s.reel").') as stock_physique';
if (!empty($conf->global->STOCK_ALLOW_ADD_LIMIT_STOCK_BY_WAREHOUSE) && $fk_entrepot > 0) {
	$sql .= ', SUM('.$db->ifsql("s.reel IS NULL OR s.fk_entrepot <> ".$fk_entrepot, "0", "s.reel").') as stock_real_warehouse';
}

// Add fields from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListSelect', $parameters); // Note that $action and $object may have been modified by hook
$sql .= $hookmanager->resPrint;

$sql .= ' FROM '.MAIN_DB_PREFIX.'product as p';
$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'product_stock as s ON p.rowid = s.fk_product';
$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'entrepot AS ent ON s.fk_entrepot = ent.rowid AND ent.entity IN('.getEntity('stock').')';
if ($fk_supplier > 0) {
	$sql .= ' INNER JOIN '.MAIN_DB_PREFIX.'product_fournisseur_price pfp ON (pfp.fk_product = p.rowid AND pfp.fk_soc = '.$fk_supplier.')';
}
if (!empty($conf->global->STOCK_ALLOW_ADD_LIMIT_STOCK_BY_WAREHOUSE) && $fk_entrepot > 0) {
	$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'product_warehouse_properties AS pse ON (p.rowid = pse.fk_product AND pse.fk_entrepot = '.$fk_entrepot.')';
}

// Add fields from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListJoin', $parameters); // Note that $action and $object may have been modified by hook
$sql .= $hookmanager->resPrint;

$sql .= ' WHERE p.entity IN ('.getEntity('product').')';
if ($sall) $sql .= natural_search(array('p.ref', 'p.label', 'p.description', 'p.note'), $sall);
// if the type is not 1, we show all products (type = 0,2,3)
if (dol_strlen($type)) {
	if ($type == 1) {
		$sql .= ' AND p.fk_product_type = 1';
	} else {
		$sql .= ' AND p.fk_product_type <> 1';
	}
}
if ($search_ref) $sql .= natural_search('p.ref', $search_ref);
if ($search_label) $sql .= natural_search('p.label', $search_label);
$sql .= ' AND p.tobuy = 1';
if (empty($conf->global->VARIANT_ALLOW_STOCK_MOVEMENT_ON_VARIANT_PARENT)) {	// Add test to exclude products that has variants
	$sql .= ' AND p.rowid NOT IN (SELECT pac.fk_product_parent FROM '.MAIN_DB_PREFIX.'product_attribute_combination as pac WHERE pac.entity IN ('.getEntity('product').'))';
}
$sql .= ' GROUP BY p.rowid, p.ref, p.label, p.description, p.price';
$sql .= ', p.price_ttc, p.price_base_type,p.fk_product_type, p.tms';
$sql .= ', p.duration, p.tobuy';
$sql .= ', p.desiredstock';
$sql .= ', p.seuil_stock_alerte';
if (!empty($conf->global->STOCK_ALLOW_ADD_LIMIT_STOCK_BY_WAREHOUSE) && $fk_entrepot > 0) {
	$sql .= ', pse.desiredstock';
	$sql .= ', pse.seuil_stock_alerte';
}
$sql .= ', s.fk_product';

if ($usevirtualstock)
{
	if (!empty($conf->commande->enabled)) {
		$sqlCommandesCli = "(SELECT ".$db->ifsql("SUM(cd1.qty) IS NULL", "0", "SUM(cd1.qty)")." as qty"; // We need the ifsql because if result is 0 for product p.rowid, we must return 0 and not NULL
		$sqlCommandesCli .= " FROM ".MAIN_DB_PREFIX."commandedet as cd1, ".MAIN_DB_PREFIX."commande as c1";
		$sqlCommandesCli .= " WHERE c1.rowid = cd1.fk_commande AND c1.entity IN (".getEntity(!empty($conf->global->STOCK_CALCULATE_VIRTUAL_STOCK_TRANSVERSE_MODE) ? 'stock' : 'commande').")";
		$sqlCommandesCli .= " AND cd1.fk_product = p.rowid";
		$sqlCommandesCli .= " AND c1.fk_statut IN (1,2))";
	} else {
		$sqlCommandesCli = '0';
	}

	if (!empty($conf->expedition->enabled)) {
		$sqlExpeditionsCli = "(SELECT ".$db->ifsql("SUM(ed2.qty) IS NULL", "0", "SUM(ed2.qty)")." as qty"; // We need the ifsql because if result is 0 for product p.rowid, we must return 0 and not NULL
		$sqlExpeditionsCli .= " FROM ".MAIN_DB_PREFIX."expedition as e2,";
		$sqlExpeditionsCli .= " ".MAIN_DB_PREFIX."expeditiondet as ed2,";
		$sqlExpeditionsCli .= " ".MAIN_DB_PREFIX."commande as c2,";
		$sqlExpeditionsCli .= " ".MAIN_DB_PREFIX."commandedet as cd2";
		$sqlExpeditionsCli .= " WHERE ed2.fk_expedition = e2.rowid AND cd2.rowid = ed2.fk_origin_line AND e2.entity IN (".getEntity(!empty($conf->global->STOCK_CALCULATE_VIRTUAL_STOCK_TRANSVERSE_MODE) ? 'stock' : 'expedition').")";
		$sqlExpeditionsCli .= " AND cd2.fk_commande = c2.rowid";
		$sqlExpeditionsCli .= " AND c2.fk_statut IN (1,2)";
		$sqlExpeditionsCli .= " AND cd2.fk_product = p.rowid";
		$sqlExpeditionsCli .= " AND e2.fk_statut IN (1,2))";
	} else {
		$sqlExpeditionsCli = '0';
	}

	if (!empty($conf->fournisseur->enabled)) {
		$sqlCommandesFourn = "(SELECT ".$db->ifsql("SUM(cd3.qty) IS NULL", "0", "SUM(cd3.qty)")." as qty"; // We need the ifsql because if result is 0 for product p.rowid, we must return 0 and not NULL
		$sqlCommandesFourn .= " FROM ".MAIN_DB_PREFIX."commande_fournisseurdet as cd3,";
		$sqlCommandesFourn .= " ".MAIN_DB_PREFIX."commande_fournisseur as c3";
		$sqlCommandesFourn .= " WHERE c3.rowid = cd3.fk_commande";
		$sqlCommandesFourn .= " AND c3.entity IN (".getEntity(!empty($conf->global->STOCK_CALCULATE_VIRTUAL_STOCK_TRANSVERSE_MODE) ? 'stock' : 'supplier_order').")";
		$sqlCommandesFourn .= " AND cd3.fk_product = p.rowid";
		$sqlCommandesFourn .= " AND c3.fk_statut IN (3,4))";

		$sqlReceptionFourn = "(SELECT ".$db->ifsql("SUM(fd4.qty) IS NULL", "0", "SUM(fd4.qty)")." as qty"; // We need the ifsql because if result is 0 for product p.rowid, we must return 0 and not NULL
		$sqlReceptionFourn .= " FROM ".MAIN_DB_PREFIX."commande_fournisseur as cf4,";
		$sqlReceptionFourn .= " ".MAIN_DB_PREFIX."commande_fournisseur_dispatch as fd4";
		$sqlReceptionFourn .= " WHERE fd4.fk_commande = cf4.rowid AND cf4.entity IN (".getEntity(!empty($conf->global->STOCK_CALCULATE_VIRTUAL_STOCK_TRANSVERSE_MODE) ? 'stock' : 'supplier_order').")";
		$sqlReceptionFourn .= " AND fd4.fk_product = p.rowid";
		$sqlReceptionFourn .= " AND cf4.fk_statut IN (3,4))";
	} else {
		$sqlCommandesFourn = '0';
		$sqlReceptionFourn = '0';
	}

	if (!empty($conf->mrp->enabled)) {
		$sqlProductionToConsume = "(SELECT GREATEST(0, ".$db->ifsql("SUM(".$db->ifsql("mp5.role = 'toconsume'", 'mp5.qty', '- mp5.qty').") IS NULL", "0", "SUM(".$db->ifsql("mp5.role = 'toconsume'", 'mp5.qty', '- mp5.qty').")").") as qty"; // We need the ifsql because if result is 0 for product p.rowid, we must return 0 and not NULL
		$sqlProductionToConsume .= " FROM ".MAIN_DB_PREFIX."mrp_mo as mm5,";
		$sqlProductionToConsume .= " ".MAIN_DB_PREFIX."mrp_production as mp5";
		$sqlProductionToConsume .= " WHERE mm5.rowid = mp5.fk_mo AND mm5.entity IN (".getEntity(!empty($conf->global->STOCK_CALCULATE_VIRTUAL_STOCK_TRANSVERSE_MODE) ? 'stock' : 'mo').")";
		$sqlProductionToConsume .= " AND mp5.fk_product = p.rowid";
		$sqlProductionToConsume .= " AND mp5.role IN ('toconsume', 'consummed')";
		$sqlProductionToConsume .= " AND mm5.status IN (1,2))";

		$sqlProductionToProduce = "(SELECT GREATEST(0, ".$db->ifsql("SUM(".$db->ifsql("mp5.role = 'toproduce'", 'mp5.qty', '- mp5.qty').") IS NULL", "0", "SUM(".$db->ifsql("mp5.role = 'toconsume'", 'mp5.qty', '- mp5.qty').")").") as qty"; // We need the ifsql because if result is 0 for product p.rowid, we must return 0 and not NULL
		$sqlProductionToProduce .= " FROM ".MAIN_DB_PREFIX."mrp_mo as mm5,";
		$sqlProductionToProduce .= " ".MAIN_DB_PREFIX."mrp_production as mp5";
		$sqlProductionToProduce .= " WHERE mm5.rowid = mp5.fk_mo AND mm5.entity IN (".getEntity(!empty($conf->global->STOCK_CALCULATE_VIRTUAL_STOCK_TRANSVERSE_MODE) ? 'stock' : 'mo').")";
		$sqlProductionToProduce .= " AND mp5.fk_product = p.rowid";
		$sqlProductionToProduce .= " AND mp5.role IN ('toproduce', 'produced')";
		$sqlProductionToProduce .= " AND mm5.status IN (1,2))";
	} else {
		$sqlProductionToConsume = '0';
		$sqlProductionToProduce = '0';
	}

	$sql .= ' HAVING (';
	$sql .= ' ('.$sqldesiredtock.' >= 0 AND ('.$sqldesiredtock.' > SUM('.$db->ifsql("s.reel IS NULL", "0", "s.reel").')';
	$sql .= ' - ('.$sqlCommandesCli.' - '.$sqlExpeditionsCli.') + ('.$sqlCommandesFourn.' - '.$sqlReceptionFourn.') + ('.$sqlProductionToProduce.' - '.$sqlProductionToConsume.')))';
	$sql .= ' OR';
	if ($includeproductswithoutdesiredqty == 'on') {
		$sql .= ' (('.$sqlalertstock.' >= 0 OR '.$sqlalertstock.' IS NULL) AND ('.$db->ifsql("$sqlalertstock IS NULL", "0", $sqlalertstock).' > SUM('.$db->ifsql("s.reel IS NULL", "0", "s.reel").')';
	} else {
		$sql .= ' ('.$sqlalertstock.' >= 0 AND ('.$sqlalertstock.' > SUM('.$db->ifsql("s.reel IS NULL", "0", "s.reel").')';
	}
	$sql .= ' - ('.$sqlCommandesCli.' - '.$sqlExpeditionsCli.') + ('.$sqlCommandesFourn.' - '.$sqlReceptionFourn.') + ('.$sqlProductionToProduce.' - '.$sqlProductionToConsume.')))';
	$sql .= ')';

	if ($salert == 'on')	// Option to see when stock is lower than alert
	{
		$sql .= ' AND (';
		if ($includeproductswithoutdesiredqty == 'on') {
			$sql .= '('.$sqlalertstock.' >= 0 OR '.$sqlalertstock.' IS NULL) AND ('.$db->ifsql("$sqlalertstock IS NULL", "0", $sqlalertstock).' > SUM('.$db->ifsql("s.reel IS NULL", "0", "s.reel").')';
		} else {
			$sql .= $sqlalertstock.' >= 0 AND ('.$sqlalertstock.' > SUM('.$db->ifsql("s.reel IS NULL", "0", "s.reel").')';
		}
		$sql .= ' - ('.$sqlCommandesCli.' - '.$sqlExpeditionsCli.') + ('.$sqlCommandesFourn.' - '.$sqlReceptionFourn.')  + ('.$sqlProductionToProduce.' - '.$sqlProductionToConsume.'))';
		$sql .= ')';
		$alertchecked = 'checked';
	}
} else {
	$sql .= ' HAVING (';
	$sql .= '('.$sqldesiredtock.' >= 0 AND ('.$sqldesiredtock.' > SUM('.$db->ifsql("s.reel IS NULL", "0", "s.reel").')))';
	$sql .= ' OR';
	if ($includeproductswithoutdesiredqty == 'on') {
		$sql .= ' (('.$sqlalertstock.' >= 0 OR '.$sqlalertstock.' IS NULL) AND ('.$db->ifsql("$sqlalertstock IS NULL", "0", $sqlalertstock).' > SUM('.$db->ifsql("s.reel IS NULL", "0", "s.reel").')))';
	} else {
		$sql .= ' ('.$sqlalertstock.' >= 0 AND ('.$sqlalertstock.' > SUM('.$db->ifsql("s.reel IS NULL", "0", "s.reel").')))';
	}
	$sql .= ')';

	if ($salert == 'on')	// Option to see when stock is lower than alert
	{
		$sql .= ' AND (';
		if ($includeproductswithoutdesiredqty == 'on') {
			$sql .= ' ('.$sqlalertstock.' >= 0 OR '.$sqlalertstock.' IS NULL) AND ('.$db->ifsql("$sqlalertstock IS NULL", "0", $sqlalertstock).' > SUM('.$db->ifsql("s.reel IS NULL", "0", "s.reel").'))';
		} else {
			$sql .= ' '.$sqlalertstock.' >= 0 AND ('.$sqlalertstock.' > SUM('.$db->ifsql("s.reel IS NULL", "0", "s.reel").'))';
		}
		$sql .= ')';
		$alertchecked = 'checked';
	}
}

$includeproductswithoutdesiredqtychecked = '';
if ($includeproductswithoutdesiredqty == 'on') {
	$includeproductswithoutdesiredqtychecked = 'checked';
}

// Add where from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters); // Note that $action and $object may have been modified by hook
$sql .= $hookmanager->resPrint;

$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
	$result = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($result);
	if (($page * $limit) > $nbtotalofrecords) {
		$page = 0;
		$offset = 0;
	}
}

$sql .= $db->order($sortfield, $sortorder);
$sql .= $db->plimit($limit + 1, $offset);

//print $sql;
$resql = $db->query($sql);
if (empty($resql))
{
	dol_print_error($db);
	exit;
}

$num = $db->num_rows($resql);
$i = 0;

if ($search_ref || $search_label || $sall || $salert || $draftorder || GETPOST('search', 'alpha')) {
	$filters = '&search_ref='.urlencode($search_ref).'&search_label='.urlencode($search_label);
	$filters .= '&sall='.urlencode($sall);
	$filters .= '&salert='.urlencode($salert);
	$filters .= '&draftorder='.urlencode($draftorder);
	$filters .= '&mode='.urlencode($mode);
	if ($fk_supplier > 0) $filters .= '&fk_supplier='.urlencode($fk_supplier);
	if ($fk_entrepot > 0) $filters .= '&fk_entrepot='.urlencode($fk_entrepot);
} else {
	$filters = '&search_ref='.urlencode($search_ref).'&search_label='.urlencode($search_label);
	$filters .= '&fourn_id='.urlencode($fourn_id);
	$filters .= (isset($type) ? '&type='.urlencode($type) : '');
	$filters .= '&='.urlencode($salert);
	$filters .= '&draftorder='.urlencode($draftorder);
	$filters .= '&mode='.urlencode($mode);
	if ($fk_supplier > 0) $filters .= '&fk_supplier='.urlencode($fk_supplier);
	if ($fk_entrepot > 0) $filters .= '&fk_entrepot='.urlencode($fk_entrepot);
}
if ($limit > 0 && $limit != $conf->liste_limit) {
	$filters .= '&limit='.urlencode($limit);
}

$param = (isset($type) ? '&type='.urlencode($type) : '');
$param .= '&fourn_id='.urlencode($fourn_id).'&search_label='.urlencode($search_label).'&includeproductswithoutdesiredqty='.urlencode($includeproductswithoutdesiredqty).'&salert='.urlencode($salert).'&draftorder='.urlencode($draftorder);
$param .= '&search_ref='.urlencode($search_ref);
$param .= '&mode='.urlencode($mode);
$param .= '&fk_supplier='.urlencode($fk_supplier);
$param .= '&fk_entrepot='.urlencode($fk_entrepot);

$stocklabel = $langs->trans('Stock');
$stocklabelbis = $langs->trans('Stock');
if ($usevirtualstock == 1) $stocklabel = $langs->trans('VirtualStock');
if ($usevirtualstock == 0) $stocklabel = $langs->trans('PhysicalStock');
if (!empty($conf->global->STOCK_ALLOW_ADD_LIMIT_STOCK_BY_WAREHOUSE) && $fk_entrepot > 0)
{
	$stocklabelbis = $stocklabel.' (Selected warehouse)';
	$stocklabel .= ' ('.$langs->trans("AllWarehouses").')';
}
$texte = $langs->trans('Replenishment');

print '<br>';

print '<div class="div-table-responsive">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table

if (!empty($conf->global->REPLENISH_ALLOW_VARIABLESIZELIST)) {
	print_barre_liste(
		$texte,
		$page,
		'replenish.php',
		$filters,
		$sortfield,
		$sortorder,
		'',
		$num,
		$nbtotalofrecords,
		'',
		0,
		'',
		'',
		$limit
	);
} else {
	print_barre_liste(
		$texte,
		$page,
		'replenish.php',
		$filters,
		$sortfield,
		$sortorder,
		'',
		$num,
		$nbtotalofrecords,
		''
	);
}

print '<table class="liste centpercent">';

// Fields title search
print '<tr class="liste_titre_filter">';
print '<td class="liste_titre">&nbsp;</td>';
print '<td class="liste_titre"><input class="flat" type="text" name="search_ref" size="8" value="'.dol_escape_htmltag($search_ref).'"></td>';
print '<td class="liste_titre"><input class="flat" type="text" name="search_label" size="8" value="'.dol_escape_htmltag($search_label).'"></td>';
if (!empty($conf->service->enabled) && $type == 1) print '<td class="liste_titre">&nbsp;</td>';
print '<td class="liste_titre right">'.$form->textwithpicto($langs->trans('IncludeEmptyDesiredStock'), $langs->trans('IncludeProductWithUndefinedAlerts')).'&nbsp;<input type="checkbox" id="includeproductswithoutdesiredqty" name="includeproductswithoutdesiredqty" '.(!empty($includeproductswithoutdesiredqtychecked) ? $includeproductswithoutdesiredqtychecked : '').'></td>';
print '<td class="liste_titre right"></td>';
print '<td class="liste_titre right">'.$langs->trans('AlertOnly').'&nbsp;<input type="checkbox" id="salert" name="salert" '.(!empty($alertchecked) ? $alertchecked : '').'></td>';
if (!empty($conf->global->STOCK_ALLOW_ADD_LIMIT_STOCK_BY_WAREHOUSE) && $fk_entrepot > 0)
{
	print '<td class="liste_titre">&nbsp;</td>';
}
print '<td class="liste_titre right">';
if (!empty($conf->global->STOCK_REPLENISH_ADD_CHECKBOX_INCLUDE_DRAFT_ORDER)) {
	print $langs->trans('IncludeAlsoDraftOrders').'&nbsp;<input type="checkbox" id="draftorder" name="draftorder" '.(!empty($draftchecked) ? $draftchecked : '').'>';
}
print '</td>';
print '<td class="liste_titre">&nbsp;</td>';
// Fields from hook
$parameters = array('param'=>$param, 'sortfield'=>$sortfield, 'sortorder'=>$sortorder);
$reshook = $hookmanager->executeHooks('printFieldListOption', $parameters); // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

print '<td class="liste_titre maxwidthsearch right">';
$searchpicto = $form->showFilterAndCheckAddButtons(0);
print $searchpicto;
print '</td>';
print '</tr>';

// Lines of title
print '<tr class="liste_titre">';
print_liste_field_titre('<input type="checkbox" onClick="toggle(this)" />', $_SERVER["PHP_SELF"], '');
print_liste_field_titre('Ref', $_SERVER["PHP_SELF"], 'p.ref', $param, '', '', $sortfield, $sortorder);
print_liste_field_titre('Label', $_SERVER["PHP_SELF"], 'p.label', $param, '', '', $sortfield, $sortorder);
if (!empty($conf->service->enabled) && $type == 1) print_liste_field_titre('Duration', $_SERVER["PHP_SELF"], 'p.duration', $param, '', '', $sortfield, $sortorder, 'center ');
print_liste_field_titre('DesiredStock', $_SERVER["PHP_SELF"], 'p.desiredstock', $param, '', '', $sortfield, $sortorder, 'right ');
print_liste_field_titre('StockLimitShort', $_SERVER["PHP_SELF"], 'p.seuil_stock_alerte', $param, '', '', $sortfield, $sortorder, 'right ');
print_liste_field_titre($stocklabel, $_SERVER["PHP_SELF"], 'stock_physique', $param, '', '', $sortfield, $sortorder, 'right ');
if (!empty($conf->global->STOCK_ALLOW_ADD_LIMIT_STOCK_BY_WAREHOUSE) && $fk_entrepot > 0)
{
	print_liste_field_titre($stocklabelbis, $_SERVER["PHP_SELF"], 'stock_real_warehouse', $param, '', '', $sortfield, $sortorder, 'right ');
}
print_liste_field_titre('Ordered', $_SERVER["PHP_SELF"], '', $param, '', '', $sortfield, $sortorder, 'right ');
print_liste_field_titre('StockToBuy', $_SERVER["PHP_SELF"], '', $param, '', '', $sortfield, $sortorder, 'right ');
print_liste_field_titre('SupplierRef', $_SERVER["PHP_SELF"], '', $param, '', '', $sortfield, $sortorder, 'right ');

// Hook fields
$parameters = array('param'=>$param, 'sortfield'=>$sortfield, 'sortorder'=>$sortorder);
$reshook = $hookmanager->executeHooks('printFieldListTitle', $parameters); // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

print "</tr>\n";

while ($i < ($limit ? min($num, $limit) : $num))
{
	$objp = $db->fetch_object($resql);

	if (!empty($conf->global->STOCK_SUPPORTS_SERVICES) || $objp->fk_product_type == 0)
	{
		$prod->fetch($objp->rowid);
		$prod->load_stock('warehouseopen, warehouseinternal', $draftchecked);

		// Multilangs
		if (!empty($conf->global->MAIN_MULTILANGS))
		{
			$sql = 'SELECT label,description';
			$sql .= ' FROM '.MAIN_DB_PREFIX.'product_lang';
			$sql .= ' WHERE fk_product = '.$objp->rowid;
			$sql .= ' AND lang = "'.$langs->getDefaultLang().'"';
			$sql .= ' LIMIT 1';

			$resqlm = $db->query($sql);
			if ($resqlm)
			{
				$objtp = $db->fetch_object($resqlm);
				if (!empty($objtp->description)) $objp->description = $objtp->description;
				if (!empty($objtp->label)) $objp->label = $objtp->label;
			}
		}

		$stockwarehouse = 0;
		if ($usevirtualstock)
		{
			// If option to increase/decrease is not on an object validation, virtual stock may differs from physical stock.
			$stock = $prod->stock_theorique;
			//TODO $stockwarehouse = $prod->stock_warehouse[$fk_entrepot]->;
		} else {
			$stock = $prod->stock_reel;
			$stockwarehouse = $prod->stock_warehouse[$fk_entrepot]->real;
		}

		// Force call prod->load_stats_xxx to choose status to count (otherwise it is loaded by load_stock function)
		if (isset($draftchecked)) {
			$result = $prod->load_stats_commande_fournisseur(0, '0,1,2,3,4');
		} else {
			$result = $prod->load_stats_commande_fournisseur(0, '1,2,3,4');
		}

		$result = $prod->load_stats_reception(0, '4');

		//print $prod->stats_commande_fournisseur['qty'].'<br>'."\n";
		//print $prod->stats_reception['qty'];
		$ordered = $prod->stats_commande_fournisseur['qty'] - $prod->stats_reception['qty'];

		$desiredstock = $objp->desiredstock;
		$alertstock = $objp->seuil_stock_alerte;
		$desiredstockwarehouse = ($objp->desiredstockpse ? $objp->desiredstockpse : 0);
		$alertstockwarehouse = ($objp->seuil_stock_alertepse ? $objp->seuil_stock_alertepse : 0);

		$warning = '';
		if ($alertstock && ($stock < $alertstock))
		{
			$warning = img_warning($langs->trans('StockTooLow')).' ';
		}
		$warningwarehouse = '';
		if ($alertstockwarehouse && ($stockwarehouse < $alertstockwarehouse))
		{
			$warningwarehouse = img_warning($langs->trans('StockTooLow')).' ';
		}

		//depending on conf, use either physical stock or
		//virtual stock to compute the stock to buy value

		if (empty($usevirtualstock)) $stocktobuy = max(max($desiredstock, $alertstock) - $stock - $ordered, 0);
		else $stocktobuy = max(max($desiredstock, $alertstock) - $stock, 0); //ordered is already in $stock in virtual mode
		if (empty($usevirtualstock)) $stocktobuywarehouse = max(max($desiredstockwarehouse, $alertstockwarehouse) - $stockwarehouse - $ordered, 0);
		else $stocktobuywarehouse = max(max($desiredstockwarehouse, $alertstockwarehouse) - $stockwarehouse, 0); //ordered is already in $stock in virtual mode

		$picto = '';
		if ($ordered > 0)
		{
			$stockforcompare = ($usevirtualstock ? $stock : $stock + $ordered);
			/*if ($stockforcompare >= $desiredstock)
			{
				$picto = img_picto('', 'help');
			} else {
				$picto = img_picto('', 'help');
			}*/
		} else {
			$picto = img_picto($langs->trans("NoPendingReceptionOnSupplierOrder"), 'help');
		}

		print '<tr class="oddeven">';

		// Select field
		print '<td><input type="checkbox" class="check" name="choose'.$i.'"></td>';

		print '<td class="nowrap">'.$prod->getNomUrl(1, 'stock').'</td>';

		print '<td>'.$objp->label;
		print '<input type="hidden" name="desc'.$i.'" value="'.dol_escape_htmltag($objp->description).'">'; // TODO Remove this and make a fetch to get description when creating order instead of a GETPOST
		print '</td>';

		if (!empty($conf->service->enabled) && $type == 1)
		{
			$regs = array();
			if (preg_match('/([0-9]+)y/i', $objp->duration, $regs)) {
				$duration = $regs[1].' '.$langs->trans('DurationYear');
			} elseif (preg_match('/([0-9]+)m/i', $objp->duration, $regs)) {
				$duration = $regs[1].' '.$langs->trans('DurationMonth');
			} elseif (preg_match('/([0-9]+)d/i', $objp->duration, $regs)) {
				$duration = $regs[1].' '.$langs->trans('DurationDay');
			} else {
				$duration = $objp->duration;
			}
			print '<td class="center">'.$duration.'</td>';
		}

		// Desired stock
		print '<td class="right">'.($fk_entrepot > 0 ? $desiredstockwarehouse : $desiredstock).'</td>';

		// Limit stock for alert
		print '<td class="right">'.($fk_entrepot > 0 ? $alertstockwarehouse : $alertstock).'</td>';

		// Current stock (all warehouses)
		print '<td class="right">'.$warning.$stock.'</td>';

		// Current stock (warehouse selected only)
		if (!empty($conf->global->STOCK_ALLOW_ADD_LIMIT_STOCK_BY_WAREHOUSE) && $fk_entrepot > 0)
		{
			print '<td class="right">'.$warningwarehouse.$stockwarehouse.'</td>';
		}

		// Already ordered
		print '<td class="right"><a href="replenishorders.php?search_product='.$prod->id.'">'.$ordered.'</a> '.$picto.'</td>';

		// To order
		print '<td class="right"><input type="text" size="4" name="tobuy'.$i.'" value="'.($fk_entrepot > 0 ? $stocktobuywarehouse : $stocktobuy).'"></td>';

		// Supplier
		print '<td class="right">';
		print $form->select_product_fourn_price($prod->id, 'fourn'.$i, $fk_supplier);
		print '</td>';

		// Fields from hook
		$parameters = array('objp'=>$objp);
		$reshook = $hookmanager->executeHooks('printFieldListValue', $parameters); // Note that $action and $object may have been modified by hook
		print $hookmanager->resPrint;

		print '</tr>';
	}
	$i++;
}

$parameters = array('sql'=>$sql);
$reshook = $hookmanager->executeHooks('printFieldListFooter', $parameters); // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

print '</table>';
print '</div>';

$db->free($resql);

print dol_get_fiche_end();


$value = $langs->trans("CreateOrders");
print '<div class="center"><input class="button" type="submit" name="valid" value="'.$value.'"></div>';


print '</form>';


// TODO Replace this with jquery
print '
<script type="text/javascript">
function toggle(source)
{
	checkboxes = document.getElementsByClassName("check");
	for (var i=0; i < checkboxes.length;i++) {
		if (!checkboxes[i].disabled) {
			checkboxes[i].checked = source.checked;
		}
	}
}
</script>';
