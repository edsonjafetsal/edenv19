<?php
	require('config.php');
	require('./class/asset.class.php');
	
	if(!$user->rights->assetatm->all->lire) accessforbidden();
	
	dol_include_once("/core/class/html.formother.class.php");
	dol_include_once("/core/lib/company.lib.php");
	dol_include_once('/core/lib/product.lib.php');
	dol_include_once('/product/class/product.class.php');
	dol_include_once('/product/stock/class/entrepot.class.php');
	dol_include_once('/product/stock/class/mouvementstock.class.php');

	$hookmanager->initHooks(array('assetlist', 'assetatmlist'));

	_liste($conf->entity);

function get_measuring_units_string($fk_asset,$unite){
            
        $PDOdb = new TPDOdb;    
        
        $asset = new TAsset;
        $asset->load($PDOdb, $fk_asset,false);
        $asset->assetType->load($PDOdb, $asset->fk_asset_type,false);
        
        if($asset->gestion_stock != 'UNIT'){
            return measuring_units_string($unite,$asset->assetType->measuring_units);
        }
        else{
            return 'unité(s)';
        }
        
        $PDOdb->close();
}

function _liste($id_entity) {
	global $langs,$db,$user,$ASSET_LINK_ON_FIELD,$conf, $hookmanager;
	
	$limit = GETPOST('limit');
	$PDOdb=new TPDOdb;
	
	$langs->load('other');
	$langs->load('assetatm@assetatm');

	global $TExtrafieldAssetListeOptionsValues, $TExtrafieldAssetSellListOptionsValues;
	$TExtrafieldAssetListeOptionsValues = $TExtrafieldAssetSellListOptionsValues = array();

	$fk_asset_type = GETPOST('fk_asset_type');
	$fk_product = GETPOST('fk_product');
	$fk_soc = GETPOST('fk_soc');
	$no_serial = GETPOST('no_serial');

	llxHeader('',$langs->trans('ListAsset'),'','');
	//getStandartJS();

	if(! empty($fk_soc))
	{
		$soc = new Societe($db);
		$soc->fetch($fk_soc);
		$soc->info($fk_soc);

		$head = societe_prepare_head($soc);
		dol_fiche_head($head, 'tabEquipement2', $langs->trans("ThirdParty"),0,'company');

		if(function_exists('dol_banner_tab'))
		{
			$linkback = '<a href="'.DOL_URL_ROOT.'/societe/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

			dol_banner_tab($soc, 'socid', $linkback, ($user->societe_id?0:1), 'rowid', 'nom');
		}

		dol_fiche_end();
	}
	elseif(! empty($fk_product))
	{
		$product = new Product($db);
		$result=$product->fetch($fk_product);

		$asset = new TAsset;
		$fk_asset_type = $asset->get_asset_type($PDOdb, $fk_product);

		$head=product_prepare_head($product, $user);
		$titre=$langs->trans("CardProduct".$product->type);
		$picto=($product->type==1?'service':'product');

		dol_fiche_head($head, 'tabEquipement1', $titre, 0, $picto);

		if(function_exists('dol_banner_tab'))
		{
			$linkback = '<a href="'.DOL_URL_ROOT.'/product/list.php?restore_lastsearch_values=1&type='.$product->type.'">'.$langs->trans("BackToList").'</a>';
			$product->next_prev_filter=" fk_product_type = ".$product->type;

			$shownav = 1;
			if ($user->societe_id && ! in_array('product', explode(',',$conf->global->MAIN_MODULES_FOR_EXTERNAL))) $shownav=0;

			dol_banner_tab($product, 'ref', $linkback, $shownav, 'ref');
		}

		dol_fiche_end();
	}
	else
	{
		//print load_fiche_titre('Equipements');
	}


	$TExtrafieldAsset = array();
	
	if(! empty($fk_asset_type))
	{
		$TExtrafieldAsset = TAsset_field::getAllByCode(true, $fk_asset_type);
	}

	$moreParams = '';

	if(! empty($fk_soc))
	{
		$moreParams .= '&fk_soc=' . $fk_soc;
		$listID .= '_soc' . $fk_soc;
	}

	if(! empty($fk_product))
	{
		$moreParams .= '&fk_product=' . $fk_product;
		$listID .= '_product' . $fk_product;
	}

	if(! empty($fk_asset_type))
	{
		$moreParams .= '&fk_asset_type=' . $fk_asset_type;
		$listID .= '_type' . $fk_asset_type;
	}

	$asset=new TAsset;
	$form = new TFormCore($_SERVER['PHP_SELF'] . '?' . $moreParams, 'formDossier', 'POST');

	$listID = 'list_'.$asset->get_table();
	$r = new TListviewTBS($listID);

	$TTitle = array(
		'serial_number'=>$langs->trans('AssetAtmSerialNumber')
		,'nom'=>$langs->trans('Company')
		,'fk_product'=>$langs->trans('Product')
		,'lot_number'=>$langs->trans('AssetAtmBatchNumber')
		,'contenance'=>$langs->trans('AssetAtmCurrentConsistency')
		,'unite'=>$langs->trans('Unit')
		,'fk_soc'=>$langs->trans('Company')
		,'fk_societe_localisation'=>'Location'
        ,'fk_entrepot'=>'Warehouse'
	);

	$TSearch = array(
		'serial_number'=>true
		,'fk_soc'=>array('recherche'=>true, 'table'=>'s', 'field'=>'nom')
		,'fk_societe_localisation'=>array('recherche' => true, 'table'=>'sloc', 'field'=>array('nom', 'name_alias'))
		,'Creation'=>array('recherche'=>'calendar', 'table' => 'e', 'field' => 'date_cre')
        ,'fk_entrepot'=>array('recherche'=>true, 'table'=>'ent', 'field'=>'ref')
	);

	if(empty($fk_product))
	{
		$TSearch['fk_product'] = array('recherche'=>true, 'table'=>'p', 'field'=> array('label', 'ref'));
	}

	if(! empty($conf->global->USE_LOT_IN_OF))
	{
		$TSearch['lot_number'] = array('recherche' => true);
	}

	$TType = array(
		'Date garantie'=>'date'
		,'Date dernière intervention'=>'date'
		,'Date livraison'=>'date'
		,'Création'=>'date'
		,'Date fin pret'=>'date'
		,'Date debut pret'=>'date'
	);

	$TEval = array(
		'unite'=>'get_measuring_units_string(@ID@,"@unite@")'
		,'fk_product'=>'_get_product_link("@val@")'
		,'fk_soc'=>'_show_societe(@val@)'
		,'fk_societe_localisation'=>'_show_societe(@val@)'
        ,'fk_entrepot'=>'_show_entrepot(@val@)'
        ,'serial_number'=>"_getNomUrlSerial('@val@', '@DLUO@', '@ID@')"
	);

	$TSqlJoin = array();

	// TODO remove this shitty conf
	if(defined('ASSET_LIST_FIELDS'))
	{
		$fields = ASSET_LIST_FIELDS;
	}
	else
	{
		$fields = "e.rowid as 'ID', e.serial_number, ";
		if(empty($fk_product)) $fields.= "e.fk_product, ";
		if(! empty($conf->global->ASSET_USE_PRODUCTION_ATTRIBUT)) $fields.= "e.contenancereel_value as 'contenance', e.contenancereel_units as 'unite', ";
		if(! empty($conf->global->USE_LOT_IN_OF)) $fields.= "e.lot_number, ";
		$fields.= "e.fk_soc, e.fk_societe_localisation, e.date_cre as '".$langs->trans('Creation')."'";

		if (!empty($TExtrafieldAsset))
		{
			$fields.= "\n";
			foreach ($TExtrafieldAsset as $code => $TInfo)
			{
				$fields.= ', e.'.$code;
				$TTitle[$code] = $TInfo['label'];
				if ($TInfo['type'] == 'liste')
				{
					$TExtrafieldAssetListeOptionsValues[$code] = $TInfo['options'];
					$TEval[$code] = '_getListeOptionsValue("'.$code.'", "@val@")';
					$TSearch[$code] = array('recherche'=>$TInfo['options'], 'table' => 'e');
				}
				elseif ($TInfo['type'] == 'sellist')
				{
					$optionsParam = explode( ':', array_shift($TInfo['options']));
					$TExtrafieldAssetSellListOptionsValues[$code] = $optionsParam;

					$TSearch[$code]= array('recherche' => true, 'table'=>'search_'.$optionsParam[0], 'field'=>$optionsParam[1]);
					$TEval[$code] = '_getSellistOptionsValue("'.$code.'", "@val@")';
					$TSqlJoin[$code] = " LEFT JOIN `".MAIN_DB_PREFIX.$optionsParam[0]."` as search_".$optionsParam[0]."  ON (e.".$code." = `search_".$optionsParam[0]."`.".$optionsParam[2].") ";
				}
				else if ($TInfo['type'] == 'date')
				{
					$TType[$code] = 'date';
					$TSearch[$code] = array('recherche'=>'calendar', 'table' => 'e');
				}
				else
				{
					$TSearch[$code] = array('recherche'=>true, 'table' => 'e');
				}

				// TODO checkbox
			}
		}

	}
	
	$parameters=array('TTitle' => &$TTitle, 'TEval' => &$TEval, 'TType' => &$TType, 'TSearch' => &$TSearch);
	$reshook=$hookmanager->executeHooks('printFieldListSelect',$parameters);    // Note that $action and $object may have been modified by hook
	$fields.=$hookmanager->resPrint;

	$sql="SELECT ".$fields."
		  FROM ((".MAIN_DB_PREFIX."assetatm e LEFT OUTER JOIN ".MAIN_DB_PREFIX."product p ON (e.fk_product=p.rowid))
				LEFT OUTER JOIN ".MAIN_DB_PREFIX."societe s ON (e.fk_soc=s.rowid))
				LEFT JOIN " . MAIN_DB_PREFIX . "societe sloc ON (sloc.rowid = e.fk_societe_localisation)
				LEFT JOIN " . MAIN_DB_PREFIX . "entrepot ent ON (ent.rowid = e.fk_entrepot)";

	$parameters=array();
	$reshook=$hookmanager->executeHooks('printFieldListJoin',$parameters);    // Note that $action and $object may have been modified by hook
	$sql.=$hookmanager->resPrint;

	// Join extrafields
	if(!empty($TSqlJoin)){
		$sql.= implode( ' ', $TSqlJoin);
	}


	if($conf->clinomadic->enabled && isset($_REQUEST['pret']) && $_REQUEST['pret'] == 1 )
	{
		//TODO => hook !
		$sql .= " WHERE etat = 2"; //prêté
		$sql = "SELECT e.rowid as 'ID', e.serial_number, p.rowid as 'fk_product', p.label, s.rowid as 'fk_soc', s.nom,
				e.date_deb_pret as 'Date debut pret', e.date_fin_pret as 'Date fin pret'
				FROM ((".MAIN_DB_PREFIX."assetatm e LEFT OUTER JOIN ".MAIN_DB_PREFIX."product p ON (e.fk_product=p.rowid))
				LEFT OUTER JOIN ".MAIN_DB_PREFIX."societe s ON (e.fk_societe_localisation=s.rowid))
				WHERE etat = 2";
	}
	else
	{
		$sql .= " WHERE 1";
	}

	if(! empty($fk_soc))
	{
		$sql.=" AND (e.fk_soc = " . $fk_soc . " OR e.fk_societe_localisation = " . $fk_soc . ")"; 
	}

	if(! empty($fk_product))
	{
		$sql .= " AND e.fk_product = " . $fk_product;
	}

	if(! empty($fk_asset_type))
	{
		$sql .= ' AND e.fk_asset_type = ' . $fk_asset_type;
	}
	
	if(empty($fk_soc) && empty($fk_product) && ! empty($id_entity))
	{
		$sql.= ' AND e.entity='.$id_entity;		
	}

	if(! empty($no_serial))
	{
		$sql.=" AND serial_number='' OR serial_number = 'ErrorBadMask'";		
	}

	if(! empty($conf->global->ASSET_HIDE_NO_STOCK_ON_LIST))
	{
		$sql.=" AND contenancereel_value > 0";
	}

	$parameters=array();
	$reshook=$hookmanager->executeHooks('printFieldListWhere',$parameters);    // Note that $action and $object may have been modified by hook
	$sql.=$hookmanager->resPrint;

	if(! empty($conf->global->ASSET_LIST_BY_ROWID_DESC) && empty($_REQUEST['TListTBS'][$listID]['search']))
	{
		$sql.=" ORDER BY e.rowid DESC";
	}

	$parameters=array();
	$reshook=$hookmanager->executeHooks('printFieldListMoreSQL',$parameters);    // Note that $action and $object may have been modified by hook
	$sql.=$hookmanager->resPrint;

	if(! empty($fk_product))
	{
		$THide[] = 'ID';
	}
	$THide[] = 'DLUO';

	$listViewConfig = array(
		'limit'=>array(
			'nbLine'=>!empty($limit)?$limit:$conf->liste_limit
		)
		,'no-select'=>0
		,'subQuery'=>array()
		,'link'=>array(
			'ID'=>'<a href="'.dol_buildpath('/assetatm/fiche.php?id=@val@&action=view', 2).'">@val@</a>'
			,'nom'=>'<a href="'.DOL_URL_ROOT.'/societe/soc.php?socid=@fk_soc@">'.img_picto('','object_company.png','',0).' @val@</a>'
			,'serial_number'=>'<a href="fiche.php?id=@ID@">@val@</a>'
		)
		,'translate'=>array()
		,'hide'=>$THide
		,'type'=>$TType
		,'liste'=>array(
			'titre'=> ($conf->clinomadic->enabled && isset($_REQUEST['pret']) && $_REQUEST['pret'] == 1 ) ?  $langs->trans('ListAssetLent') : $langs->trans('ListAsset')
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> ! empty($fk_soc) || ! empty($fk_product) ? 1 : 0
			,'messageNothing'=>$langs->trans('NoAsset')
			,'picto_search'=>img_picto('','search.png', '', 0)
		)
		,'title'=>$TTitle
		,'search'=>$TSearch
		,'eval'=>$TEval
	);

	// Change view from hooks
	$parameters=array(  'listViewConfig' => $listViewConfig);
	$reshook=$hookmanager->executeHooks('listViewConfig',$parameters,$r);    // Note that $action and $object may have been modified by hook
	if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
	if ($reshook>0)
	{
		$listViewConfig = $hookmanager->resArray;
	}

	echo $r->render($PDOdb, $sql, $listViewConfig);

	if(! empty($fk_soc) || ! empty($fk_product) || ! empty($fk_asset_type))
	{
		$link = dol_buildpath('/assetatm/fiche.php', 1) . '?action=edit' . $moreParams;
		if(!empty($user->rights->{ATM_ASSET_NAME}->all->write)) echo '<div class="tabsAction"><a class="butAction" id="butCreateAsset" href="' . $link . '">' . $langs->trans('CreateAsset') . '</a></div>';
	}

	$form->end();

	$PDOdb->close();

	llxFooter();

}

function _getListeOptionsValue($code, $val)
{
	global $TExtrafieldAssetListeOptionsValues;

	if (!empty($TExtrafieldAssetListeOptionsValues[$code]))
	{
		return $TExtrafieldAssetListeOptionsValues[$code][$val];
	}

	return '';
}


function _getSellistOptionsValue($code = '', $value = '')
{
	global $TExtrafieldAssetSellListOptionsValues, $db, $langs;

	if (!empty($TExtrafieldAssetSellListOptionsValues[$code]))
	{

		$InfoFieldList = $TExtrafieldAssetSellListOptionsValues[$code];

		$selectkey="rowid";
		$keyList='rowid';

		if (count($InfoFieldList)>=3)
		{
			$selectkey = $InfoFieldList[2];
			$keyList=$InfoFieldList[2].' as rowid';
		}

		$fields_label = explode('|',$InfoFieldList[1]);
		if(is_array($fields_label)) {
			$keyList .=', ';
			$keyList .= implode(', ', $fields_label);
		}

		$sql = 'SELECT '.$keyList;
		$sql.= ' FROM '.MAIN_DB_PREFIX .$InfoFieldList[0];
		if (strpos($InfoFieldList[4], 'extra')!==false)
		{
			$sql.= ' as main';
		}
		if ($selectkey=='rowid' && empty($value)) {
			$sql.= " WHERE ".$selectkey."=0";
		} elseif ($selectkey=='rowid') {
			$sql.= " WHERE ".$selectkey."=".$db->escape($value);
		}else {
			$sql.= " WHERE ".$selectkey."='".$db->escape($value)."'";
		}

		$resql = $db->query($sql);
		if ($resql)
		{
			$value='';	// value was used, so now we reset it to use it to build final output

			$obj = $db->fetch_object($resql);

			// Several field into label (eq table:code|libelle:rowid)
			$fields_label = explode('|',$InfoFieldList[1]);

			if(is_array($fields_label) && count($fields_label)>1)
			{
				foreach ($fields_label as $field_toshow)
				{
					$translabel='';
					if (!empty($obj->$field_toshow)) {
						$translabel=$langs->trans($obj->$field_toshow);
					}
					if ($translabel!=$field_toshow) {
						$value.=dol_trunc($translabel,18).' ';
					}else {
						$value.=$obj->$field_toshow.' ';
					}
				}
			}
			else
			{
				$translabel='';
				if (!empty($obj->{$InfoFieldList[1]})) {
					$translabel=$langs->trans($obj->{$InfoFieldList[1]});
				}
				if ($translabel!=$obj->{$InfoFieldList[1]}) {
					$value=dol_trunc($translabel,18);
				}else {
					$value=$obj->{$InfoFieldList[1]};
				}
			}
		}

		return $value;
	}

	return '';
}

function _show_societe($fk_soc) {
	
	global $langs,$db,$conf,$user;
	
	if($fk_soc>0) {
		$soc=new Societe($db);
		if($soc->fetch($fk_soc)>0) {
			return $soc->getNomUrl(1);
		}
	}
	
	return '';
}


function _show_entrepot($fk_entrepot) {

    global $langs,$db,$conf,$user;

    if($fk_entrepot>0) {
        $entrepot=new Entrepot($db);
        if($entrepot->fetch($fk_entrepot)>0) {
            return $entrepot->getNomUrl(1);
        }
    }

    return '';
}

function _get_product_link($fk_product = null)
{
	global $db;

	if (!empty($fk_product))
	{
		$product = new Product($db);
		$product->fetch($fk_product);

		$link = $product->getNomUrl(1);

		if(! empty($product->label))
		{
			$link .= ' - ' . $product->label;
		}

		return $link;
	}

	return 'Produit non défini';
}

function _getNomUrlSerial($val, $dluo, $fk_asset) {
    global $langs, $conf, $db;
    $warning = '';
    if(empty($val)) $val= '(vide)';
    if(!empty($dluo) && strtotime($dluo) < time() && !empty($conf->global->ASSET_SHOW_DLUO)) $warning = img_warning($langs->trans('Asset_DLUO_outdated'));
    $origin = '';
    //On affiche l'origine du dernier mouvement de stock s'il y en a un
	if(!empty($conf->global->ASSET_SHOW_ORIGIN_STOCK_MOVEMENT_ON_LIST)) {
		$sql = "SELECT fk_stock_mouvement FROM ".MAIN_DB_PREFIX."assetatm_stock WHERE fk_asset = ".$fk_asset." ORDER BY date_mvt DESC LIMIT 1";
		$resql = $db->query($sql);
		if($resql) {
			$obj = $db->fetch_object($resql);
			if(!empty($obj->fk_stock_mouvement)) {
				$stockMvt = new MouvementStock($db);
				$stockMvt->fetch($obj->fk_stock_mouvement);
				if(!empty($stockMvt->fk_origin)) $origin = '<div>'.$stockMvt->get_origin($stockMvt->fk_origin, $stockMvt->origintype).'</div>';

			}
		}

	}
    return '<a href="fiche.php?id='.$fk_asset.'">'.$val.'</a>'.$warning.$origin;
}
