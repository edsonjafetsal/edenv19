<?php

require('config.php');

require('./class/asset.class.php');
require('./lib/asset.lib.php');

if(!$user->rights->assetatm->all->lire) accessforbidden();

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("assetatm@assetatm");

// Get parameters
_action();

function _action() {
	global $user;	
	$PDOdb=new TPDOdb;

	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'new':
			case 'add':
				$assetlot=new TAssetLot;
				$assetlot->set_values($_REQUEST);
				_fiche($PDOdb,$assetlot,'new');

				break;

			case 'edit'	:
			
				$assetlot=new TAssetLot;
				$assetlot->load($PDOdb, $_REQUEST['id'], false);

				_fiche($PDOdb,$assetlot,'edit');
				break;

			case 'save':
				$assetlot=new TAssetLot;
				if(!empty($_REQUEST['id'])) $assetlot->load($PDOdb, $_REQUEST['id'], false);
				$assetlot->set_values($_REQUEST);
				$assetlot->save($PDOdb);
				
				?>
				<script language="javascript">
					document.location.href="<?php echo dirname($_SERVER['PHP_SELF'])?>/fiche_lot.php?id=<?php echo $assetlot->rowid?>";					
				</script>
				<?php
				
				break;

			case 'delete':
				$assetlot=new TAssetLot;
				$assetlot->load($PDOdb, $_REQUEST['id'], false);
				$assetlot->delete($PDOdb);
				
				?>
				<script language="javascript">
					document.location.href="<?php echo dirname($_SERVER['PHP_SELF'])?>/liste_lot.php?delete_ok=1";					
				</script>
				<?php
				
				break;
			case 'traceability':
				$assetlot=new TAssetLot;
				$assetlot->load($PDOdb, $_REQUEST['id']);
				
				_traceability($PDOdb,$assetlot);
				break;
			case 'object_linked':
				$assetlot=new TAssetLot;
				$assetlot->load($PDOdb, $_REQUEST['id']);
				
				_object_linked($PDOdb,$assetlot);
				break;
		}
		
	}
	elseif((isset($_REQUEST['id']) && !empty($_REQUEST['id'])) || GETPOST('lot_number')) {
		$assetlot=new TAssetLot;
		if (empty($_REQUEST['id'])) $assetlot->loadBy($PDOdb, GETPOST('lot_number'), 'lot_number', false);
		else $assetlot->load($PDOdb, $_REQUEST['id'], false);

		_fiche($PDOdb,$assetlot, 'view');
	}
	else{
		?>
		<script language="javascript">
			document.location.href="<?php echo dirname($_SERVER['PHP_SELF'])?>/liste_lot.php";					
		</script>
		<?php
	}


	
	
}

/**
 * @param TPDOdb $PDOdb
 * @param TAssetLot $assetlot
 * @param string $mode
 */
function _fiche(&$PDOdb,&$assetlot, $mode='edit') {
global $langs,$db,$conf;
/***************************************************
* PAGE
*
* Put here all code to build page
****************************************************/
	
	llxHeader('',$langs->trans('AssetLot'),'','');
	$notab = -1;
	$pageType = 'assetlot';
	$THead = assetatmPrepareHead( $assetlot, $pageType);
	print dol_get_fiche_head($THead , 'fiche', $langs->trans('AssetLot'), $notab);

	print dol_fiche_end($notab);

	$form=new TFormCore($_SERVER['PHP_SELF'],'formeq','POST');
	$form->Set_typeaff($mode);

	echo $form->hidden('id', $assetlot->rowid);
	if ($mode=='new'){
		echo $form->hidden('action', 'save');
	}
	echo $form->hidden('entity', $conf->entity);

	$TBS=new TTemplateTBS();
	$liste=new TListviewTBS('assetlot');

	$TBS->TBS->protect=false;
	$TBS->TBS->noerr=true;
	
	print $TBS->render('tpl/fiche_lot.tpl.php'
		,array()
		,array(
			'langs' => $langs,
			'url' => array(
				'backToList' => dol_buildpath('assetatm/liste_lot.php', 1)
			),
			'assetlot'=>array(
				'id'=>$assetlot->getId()
				,'lot_number'=>$form->texte('', 'lot_number', $assetlot->lot_number, 100,255,'','','à saisir')
			)
			,'view'=>array(
				'mode' => $mode
			)
		)
	);

	echo $form->end_form();
	// End of page
	
	_liste_asset($PDOdb,$assetlot);

	llxFooter();
}

//Affiche les équipements du lot
function _liste_asset(&$PDOdb,&$assetlot){
	
	global $langs,$db,$user,$ASSET_LINK_ON_FIELD, $conf;

	require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';

	if(defined('ASSET_LIST_FIELDS')){
		$fields = ASSET_LIST_FIELDS;
	}
	else{
		$fields = "e.rowid as 'ID',e.dluo as 'DLUO', e.serial_number, e.lot_number, p.rowid as 'fk_product', p.label, ";
		if(! empty($conf->global->ASSET_USE_PRODUCTION_ATTRIBUT)) $fields.= "e.contenancereel_value as 'contenance', e.contenancereel_units as 'unite', ";
		$fields.= "e.date_cre as 'Création'";
	}

	$r = new TSSRenderControler($assetlot);

	$sql="SELECT ".$fields."
		  FROM ((".MAIN_DB_PREFIX."assetatm e LEFT OUTER JOIN ".MAIN_DB_PREFIX."product p ON (e.fk_product=p.rowid))
				LEFT OUTER JOIN ".MAIN_DB_PREFIX."societe s ON (e.fk_soc=s.rowid))
		  WHERE e.lot_number = '".$assetlot->lot_number."'";

    $THide[] = 'DLUO';

    $url = $_SERVER['PHP_SELF'];
    if(! empty($assetlot->rowid)) $url.= '?id='.$assetlot->rowid;

    $form=new TFormCore($url,'list-assetlot','GET');
    print $form->hidden('id', $assetlot->rowid);

	$r->liste($PDOdb, $sql, array(
		'limit'=>array(
			'nbLine'=>'30'
		)
		,'subQuery'=>array()
		,'link'=>array(
			'nom'=>'<a href="'.DOL_URL_ROOT.'/societe/soc.php?socid=@fk_soc@">'.img_picto('','object_company.png','',0).' @val@</a>'
			,'serial_number'=>'<a href="fiche.php?id=@ID@">@val@</a>'
			,'label'=>'<a href="'.DOL_URL_ROOT.'/product/card.php?id=@fk_product@">'.img_picto('','object_product.png','',0).' @val@</a>'
		)
		,'translate'=>array()
		,'hide'=>$THide
		,'type'=>array('Date garantie'=>'date','Date dernière intervention'=>'date', 'Date livraison'=>'date', 'Création'=>'date')
		,'liste'=>array(
			'titre'=>'Liste des '.$langs->trans('AssetInLot')
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['fk_soc']) | (int)isset($_REQUEST['fk_product'])
			,'messageNothing'=>"Il n'y a aucun ".$langs->trans('Asset')." à afficher"
			,'picto_search'=>img_picto('','search.png', '', 0)
		)
		,'title'=>array(
			'serial_number'=>'Numéro de série'
			,'nom'=>'Société'
			,'label'=>'a'
			,'lot_number'=>'Numéro de Lot'
			,'contenance'=>'Contenance Actuelle'
			,'unite'=>'Unité'
		)
		,'search'=>array(
			'serial_number'=>true
			,'nom'=>array('recherche'=>true, 'table'=>'s')
			,'label'=>array('recherche'=>true, 'table'=>'')
		)
		,'eval'=>array(
			'unite'=>'measuring_units_string(@val@,"weight")'
            ,'serial_number'=>"_getNomUrlSerial('@val@', '@DLUO@', '@ID@')"
		)
	));

    echo $form->end_form();

}

function _traceability(&$PDOdb,&$assetLot){
	global $db,$conf,$langs;

	llxHeader('',$langs->trans('AssetLot'),'','');
	print dol_get_fiche_head(assetatmPrepareHead( $assetLot, 'assetlot') , 'traceability', $langs->trans('AssetLot'));
	
	// Fix fatal Call to undefined method TAssetLot::traceability() after this commmit 8b8af14fc199afd3cb1624813c8969ae86130f4a
	// showTraceabilityTo is a replacement of TAssetLot::traceability()
	showTraceabilityTo($PDOdb,$assetLot);
}

/**
 * @param TPDOdb $PDOdb
 * @param TAssetLot $assetlot
 */
function _object_linked(&$PDOdb,&$assetlot){
	global $db,$conf,$langs;

	llxHeader('',$langs->trans('AssetLot'),'','');
	print dol_get_fiche_head(assetatmPrepareHead( $assetlot, 'assetlot') , 'object_linked', $langs->trans('AssetLot'));
	
	$assetlot->getTraceabilityObjectLinked($PDOdb);
	
	//pre($asset->TTraceability,true);
	//Liste des expéditions liés à l'équipement
	_listeTraceabilityExpedition($PDOdb,$assetlot);
	
	//Liste des commandes fournisseurs liés à l'équipement
	_listeTraceabilityCommandeFournisseur($PDOdb,$assetlot);
	
	//Liste des commandes clients liés à l'équipement
	//_listeTraceabilityCommande($PDOdb,$assetlot);
	
	//Liste des OF liés à l'équipement
	_listeTraceabilityOf($PDOdb,$assetlot);
}

/**
 * @param TPDOdb $PDOdb
 * @param TAssetLot $assetLot
 */
function _listeTraceabilityExpedition(&$PDOdb,&$assetLot){
	
	$listeview = new TListviewTBS($assetLot->getId());
	
	print $listeview->renderArray($PDOdb,$assetLot->TTraceabilityObjectLinked['expedition']
		,array(
			'liste'=>array(
					'titre' => "Shipments"
			),
			'title'=>array(
				'ref' => 'Reference',
				'societe' => 'Third Party',
				'ref_fourn' => 'Ref Supplier',
				'date_commande' => 'Order date',
				'total_ht' => 'Total (excl. tax)',
				'date_livraison' => 'Delivery date',
				'status' => 'Status',
			)
		)
	);
}

/**
 * @param TPDOdb $PDOdb
 * @param TAssetLot $assetLot
 */
function _listeTraceabilityCommandeFournisseur(&$PDOdb,&$assetLot){
	
	$listeview = new TListviewTBS($assetLot->getId());
	
	print $listeview->renderArray($PDOdb,$assetLot->TTraceabilityObjectLinked['commande_fournisseur']
		,array(
			'liste'=>array(
				'titre' => "Purchase Orders"
			),
			'title'=>array(
				'ref' => 'Reference',
				'societe' => 'Third Party',
				'ref_fourn' => 'Ref Supplier',
				'date_commande' => 'Order date',
				'total_ht' => 'Total (excl. tax)',
				'date_livraison' => 'Delivery date',
				'status' => 'Status',
			)
		)
	);
}

/**
 * @param TPDOdb $PDOdb
 * @param TAssetLot $assetLot
 */
function _listeTraceabilityCommande(&$PDOdb,&$assetLot){
	
	$listeview = new TListviewTBS($assetLot->getId());
	
	print $listeview->renderArray($PDOdb,$assetLot->TTraceabilityObjectLinked['commande']
		,array(
			'liste'=>array(
				'titre' => "Sales Order"
			),
			'title'=>array(
					'ref' => 'Reference',
				'societe' => 'Third Party',
				'ref_client' => 'Ref Customer',
				'date_commande' => 'Order date',
				'total_ht' => 'Total (excl. tax)',
				'date_livraison' => 'Delivery date',
				'status' => 'Status',
			)
		)
	);
}

/**
 * @param TPDOdb $PDOdb
 * @param TAssetLot $assetLot
 */
function _listeTraceabilityOf(&$PDOdb,&$assetLot){
	
	$listeview = new TListviewTBS($assetLot->getId());
	
	//pre($asset->TTraceabilityObjectLinked['of'],true);
	
	print $listeview->renderArray($PDOdb,$assetLot->TTraceabilityObjectLinked['of']
		,array(
			'liste'=>array(
				'titre' => "Manufacturing Order",
			),
			'title'=>array(
					'ref' => 'Référence',
					'societe' => 'Société',
					'produit_tomake' => 'Products to make',
					'produit_needed' => 'Products needed',
					'priorite' => 'Priority',
					'date_lancement' => 'Launch date',
					'date_besoin' => 'Date needed',
					'status' => 'Status',
				)
			)
	);
}
function _getNomUrlSerial($val, $dluo, $fk_asset) {
    global $langs;
    $warning = '';
    if(empty($val)) $val= '(vide)';
    if(!empty($dluo) && strtotime($dluo) < time()) $warning = img_warning($langs->trans('Asset_DLUO_outdated'));
    return '<a href="fiche.php?id='.$fk_asset.'">'.$val.'</a>'.$warning;
}

