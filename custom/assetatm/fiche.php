<?php

require('config.php');

dol_include_once('/assetatm/class/asset.class.php');
dol_include_once('/assetatm/lib/asset.lib.php');
dol_include_once('/product/stock/class/entrepot.class.php');
dol_include_once('/core/lib/ajax.lib.php');
dol_include_once('/core/lib/product.lib.php');
dol_include_once('/core/lib/admin.lib.php');

if(!$user->rights->assetatm->all->lire) accessforbidden();

if(! empty($conf->global->MAIN_MODULE_FINANCEMENT))
{
	dol_include_once('/financement/class/affaire.class.php');
}

// Load traductions files requiredby by page
$langs->Load("companies");
$langs->Load("other");
$langs->Load("assetatm@assetatm");

$hookmanager->initHooks(array('assetcard'));

// Protection if external user
if ($user->societe_id > 0)
{
	//accessforbidden();
}

$PDOdb = new TPDOdb;

$asset = new TAsset;
$id = GETPOST('id');


if(! empty($id))
{
	$assetLoaded = $asset->load($PDOdb, $id);
	$asset->load_liste_type_asset($PDOdb);
}

if((! empty($id) && empty($assetLoaded)) || (empty($id) && (empty($_REQUEST['action']) || $_REQUEST['action'] == 'view')))
{
	setEventMessage('Equipement introuvable', 'errors');
	header('Location: ' . dol_buildpath('/assetatm/liste.php', 1));
	exit;
}


// Get parameters
$mode = _action($PDOdb, $asset);

if($mode == 'traceability')
{
	_traceability($PDOdb, $asset);
}
elseif($mode == 'object_linked')
{
	_object_linked($PDOdb, $asset);
}
else
{
	_fiche($PDOdb, $asset, $mode);
}


function _action(TPDOdb &$PDOdb, TAsset &$asset)
{
	global $user, $conf, $langs;

	/*******************************************************************
	* ACTIONS
	*
	* Put here all code to do according to value of "action" parameter
	********************************************************************/

	$action = GETPOST('action');
	$mode = 'view';

	if(! empty($action))
	{
		switch($action)
		{
			case 'new':
			case 'add':
				$asset->set_values($_REQUEST);
				$asset->load_asset_type($PDOdb);

				$mode = 'new';
				break;

			case 'edit'	:
				$asset->set_values($_REQUEST);
				$asset->load_asset_type($PDOdb);

				$mode = 'edit';
				break;

			case 'save':
				if(! empty($_REQUEST['fk_asset_type']))
				{
					$asset->fk_asset_type = GETPOST('fk_asset_type');
				}

				$asset->load_asset_type($PDOdb);

				$TErrors = array();

				//on vérifie que le libellé est renseigné
				if(empty($_REQUEST['serial_number']))
				{
					$TErrors[] = 'The serial number must be complete';
				}
				elseif(! empty($conf->global->ASSET_USE_UNIQUE_SERIAL_NUMBER))
				{
					$a = new TAsset;
					$res = $a->loadBy($PDOdb, GETPOST('serial_number'), 'serial_number');

					if($res)
					{
						$TErrors[] = $langs->trans('Asset_serialNumberAlreadyInUse');
					}
				}

				if(empty($_REQUEST['fk_product']))
				{
					$TErrors[] = 'The product must be completed.';
				}

				//on vérifie que les champs obligatoires sont renseignés
				foreach($asset->assetType->TField as $field)
				{
					// /!\ le champ obligatoire est inversé : 0 => obligatoire, 1 => facultatif...
					if(! empty($field->obligatoire))
					{
						continue;
					}

					if(empty($_REQUEST[$field->code]))
					{
						$TErrors[] = 'Le champ '.$field->libelle.' Must be complete';
					}
					elseif(in_array($field->type, array('float', 'entier')) && ! is_numeric($_REQUEST[$field->code]))
					{
						$TErrors[] = 'Le champ '.$field->libelle.' Must be a number.';
					}
				}

				// On annule le décalage de 1 introduit dans TAsset_field::load() pour ne pas fausser passer le test ci-dessus
				if($field->type == 'liste')
				{
					$_REQUEST[$field->code]--;
				}

				// Dans FormProduct::selectWarehouses(), -1 => pas d'entrepôt
				if($_REQUEST['fk_entrepot'] < 0)
				{
					$_REQUEST['fk_entrepot'] = 0;
				}

				$fk_entrepot = GETPOST('fk_entrepot');

				$doAutomaticStockTransfer = ! empty($asset->rowid) && ! empty($conf->global->ASSET_MAKE_STOCK_MOVEMENTS_AT_CREATION_DELETION) && $asset->fk_entrepot != $fk_entrepot;

				// On stocke les entrepots avant l'écrasement réalisé par le set_values()
				if($doAutomaticStockTransfer)
				{
					$sourceWarehouse = $asset->fk_entrepot;
					$destinationWarehouse = $fk_entrepot;
				}

				if(empty($TErrors))
				{
					$asset->set_values($_REQUEST);

					//Cas spécifique contenance_units et contenancereel_units lorsqu'égale à 0 soit kg ou m, etc
					$asset->contenance_units = ($_REQUEST['contenance_units']) ? GETPOST('contenance_units') : $asset->contenance_units;
					$asset->contenancereel_units = ($_REQUEST['contenancereel_units']) ? GETPOST('contenance_units') : $asset->contenancereel_units;

					if(! isset($_REQUEST['type_mvt']))
					{
						$no_destock_dolibarr = ! empty($asset->rowid) || ! empty($conf->global->ASSET_USE_PRODUCTION_ATTRIBUT) || empty($conf->global->ASSET_MAKE_STOCK_MOVEMENTS_AT_CREATION_DELETION);
						$destock_dolibarr_only = false;
						$qty = 0;
						$description = 'Modification manuelle';

						if(! $no_destock_dolibarr)
						{
							$asset->contenance_units = 0;
							$asset->contenancereel_units = 0;
							$asset->contenance_value = 1;
							$asset->contenancereel_value = 0; // Le mouvement de stock va incrémenter cette valeur
							$qty = 1;
							$description = $langs->trans('StockMovementAssetCreation', $asset->serial_number);
						}
							$isoncreate = $asset->rowid;

						$asset->save($PDOdb, '', $description, $qty, $destock_dolibarr_only, 0, $no_destock_dolibarr,0,false, $asset->valeur);

						if($doAutomaticStockTransfer)
						{
							$description = $langs->trans('StockMovementAssetTransfer', $asset->serial_number);

							if(! empty($sourceWarehouse) && empty($isoncreate))
							{
								$asset->addStockMouvementDolibarr($asset->fk_product, -$asset->contenancereel_value, $description, true, $asset->fk_product, $sourceWarehouse);
							}

							if(! empty($destinationWarehouse) && empty($isoncreate))
							{
								$asset->addStockMouvementDolibarr($asset->fk_product, $asset->contenancereel_value, $description, true, $asset->fk_product, $destinationWarehouse);
							}
						}
					}
					elseif(! empty($asset->fk_entrepot))
					{
						$conf->global->PRODUIT_SOUSPRODUITS = 0;
						$qty = ($_REQUEST['type_mvt'] == 'retrait') ? GETPOST('qty') * -1 : GETPOST('qty');

						$subprice = GETPOST('subprice');

						$asset->save($PDOdb, $user, GETPOST('commentaire_mvt'), $qty, false, 0, false, 0, false, $subprice);
					}
					else
					{
						$TErrors[] = 'Error - No warehouse defined on equipment';
					}
				}

				$urlToGo = dol_buildpath('/assetatm/fiche.php', 1);
				$actionToGo = 'view';
				$moreParams = '';

				if(! empty($asset->rowid))
				{
					$moreParams .= '&id=' . $asset->rowid;
				}

				if (! empty($TErrors))
				{
					setEventMessage($TErrors, 'errors');
					$actionToGo = 'edit';

					if(! empty($_REQUEST['fk_product'])) $moreParams .= '&fk_product=' . GETPOST('fk_product');
					if(! empty($_REQUEST['fk_soc'])) $moreParams .= '&fk_soc=' . GETPOST('fk_soc');
					if(! empty($_REQUEST['fk_asset_type'])) $moreParams .= '&fk_asset_type=' . GETPOST('fk_asset_type');
				}
				else
				{
					setEventMessage('Asset not stored');
				}

				header('Location: ' . $urlToGo . '?action=' . $actionToGo . $moreParams);
				exit;

			case 'clone':
                $confirm = GETPOST('confirm');
                if ($confirm == "yes") {
                    $asset->reinit();
                    $asset->serial_number .= '(copie)';

                    $no_destock_dolibarr = !empty($asset->rowid) || !empty($conf->global->ASSET_USE_PRODUCTION_ATTRIBUT) || empty($conf->global->ASSET_MAKE_STOCK_MOVEMENTS_AT_CREATION_DELETION);
                    $destock_dolibarr_only = false;
                    $qty = 0;
                    $description = '';

                    if (!$no_destock_dolibarr) {
                        $asset->contenance_units = 0;
                        $asset->contenancereel_units = 0;
                        $asset->contenance_value = 1;
                        $asset->contenancereel_value = 0; // Le mouvement de stock va incrémenter cette valeur
                        $qty = 1;
                        $description = $langs->trans('StockMovementAssetCreation', $asset->serial_number);
                    }

                    $asset->save($PDOdb, '', $description, $qty, $destock_dolibarr_only, 0, $no_destock_dolibarr, 0, false, $asset->valeur);

                    setEventMessage('Asset not cloned');
                    header('Location: ' . dol_buildpath('/assetatm/fiche.php', 1) . '?id=' . $asset->getId());
                    exit;
                }
			case 'retour_pret':

				if($conf->clinomadic->enabled)
				{
					$asset->retour_pret($PDOdb, $_REQUEST['fk_entrepot']);
					//$PDOdb->db->debug=true;
				}

				break;

			case 'delete':
			    $confirm = GETPOST('confirm');
			    if ($confirm == "yes"){
                    $updateStock = empty($conf->global->ASSET_USE_PRODUCTION_ATTRIBUT) && ! empty($conf->global->ASSET_MAKE_STOCK_MOVEMENTS_AT_CREATION_DELETION);
                    $asset->delete($PDOdb, $updateStock);

                    setEventMessage($langs->trans('AssetDeleted'));
                    header('Location: ' . dol_buildpath('/assetatm/liste.php', 1));
                    exit;
                }
			default:
				$mode = $action;
		}
	}

	return $mode;
}


function _fiche(TPDOdb $PDOdb, TAsset &$asset, $mode = 'edit')
{
	global $langs, $user, $db, $conf, $ASSET_LINK_ON_FIELD, $hookmanager;
	/***************************************************
	* PAGE
	*
	* Put here all code to build page
	****************************************************/

	llxHeader('',$langs->trans('Asset'));
	print dol_get_fiche_head(assetatmPrepareHead( $asset, 'asset') , 'fiche', $langs->trans('Asset'));

	if(isset($_REQUEST['error'])) {
		?>
		<br><div class="error">Type of movement incorrect</div><br>
		<?php
	}

	// Utilisé pour afficher les bulles d'aide :: A voir si on ferais mieux pas de copier la fonction dans la class TFormCore pour éviter cette instant
	$html=new Form($db);

	if ($mode == "ask_delete"){
	    print $html->formconfirm($_SERVER['PHP_SELF'].'?id='.$asset->id, "Equipement", "Are you sure to delete the asset?", "delete","","",1);
	    $mode = 'view';
    }

    if ($mode == "ask_clone"){
        print $html->formconfirm($_SERVER['PHP_SELF'].'?id='.$asset->id, "Equipement", "Are you sure to clone the asset?", "clone","","",1);
        $mode = 'view';
    }

	$form=new TFormCore($_SERVER['PHP_SELF'],'formeq','POST');
	$form->Set_typeaff($mode);

	$form2=new TFormCore($_SERVER['PHP_SELF'],'form','POST');
	$form2->Set_typeaff('edit');

	echo $form->hidden('id', $asset->getId());
	if ($mode=='new'){
		echo $form->hidden('action', 'edit');
	}
	else {echo $form->hidden('action', 'save');}
	echo $form->hidden('entity', $conf->entity);
	if($mode == 'stock'){
		echo $form->hidden('serial_number', $asset->serial_number);
		echo $form->hidden('fk_product', $asset->fk_product);
	}

	/*
	 * affichage données équipement lié à une affaire du module financement
	 */
	$TAffaire = array();

	if(! empty($conf->global->MAIN_MODULE_FINANCEMENT))
	{
	 	$id_affaire = $asset->getLink('affaire')->fk_document;
		$affaire=new TFin_affaire;
		$affaire->load($PDOdb, $id_affaire, false);

		$TAffaire = $affaire->get_values();
	}

	$TBS=new TTemplateTBS();
	$liste=new TListviewTBS('asset');

	$TBS->TBS->protect=false;
	$TBS->TBS->noerr=true;

	$TAssetStock = array();

	foreach($asset->TStock as &$stock) {

		$date = $stock->get_date('date_mvt');
		if(!empty($conf->global->ASSET_USE_PRODUCTION_ATTRIBUT)){
			$TAssetStock[]=array(
				'date_cre'=>$date
				,'qty'=>$stock->qty
				,'weight_units'=>($asset->gestion_stock != 'UNIT' && $asset->assetType->measuring_units != 'unit') ? measuring_units_string($asset->contenancereel_units,$asset->assetType->measuring_units) : 'unité(s)'
				,'lot' =>$stock->lot
				,'type'=>$stock->type
			);
		}
		else{
			$TAssetStock[]=array(
				'date_cre'=>$date
				,'qty'=>$stock->qty
				,'type'=>$stock->type
			);
		}


	}

	?>
	<script type="text/javascript">
		$('#formeq').submit(function(){
			if($('#type_mvt').val() == ''){
				alert('Type de mouvement incorrect');
				return false;
			}
		})
	</script>
	<?php

	$TFields=array();

	?>
	<script type="text/javascript">
		$(document).ready(function(){

		<?php
		foreach($asset->assetType->TField as $k=>$field) {
			switch($field->type){
				case 'liste':
					$temp = $form->combo('',$field->code,$field->TListe,$asset->{$field->code}); // On décale de 1 pour éviter un index à 0 qui fausse le test empty() de remplissage des champs obligatoires
					break;
				case 'checkbox':
					$temp = $form->combo('',$field->code,array('oui'=>'Oui', 'non'=>'Non'),$asset->{$field->code});
					break;
				case 'date':
					$temp = $form->calendrier('',$field->code,$asset->{$field->code});
					break;
				case 'text':
					$temp = $form->zonetexte('', $field->code, $asset->{$field->code}, 80, 5, '', 'text', '', true);
					break;
				case 'sellist':
					$temp = $form->combo('',$field->code,$field->TListe,$asset->{$field->code});
					break;
				default:
					$temp = $form->texte('', $field->code, $asset->{$field->code}, 50,255,'','','-');
					break;
			}

			$TFields[$k]=array(
					'libelle'=>$field->libelle
					,'valeur'=>$temp
					//champs obligatoire : 0 = obligatoire ; 1 = non obligatoire
					,'obligatoire'=>$field->Mandatory ? 'class="field"': 'class="fieldrequired"'
				);

			//Autocompletion
			if($field->type != 'combo' && $field->type != 'liste' && $field->type != 'sellist'){
				?>
				$("#<?php echo $field->code; ?>").autocomplete({
					source: "script/interface.php?get=autocomplete&json=1&fieldcode=<?php echo $field->code; ?>",
					minLength : 1
				});

				<?php
			}

			//select2
			if($field->type == 'sellist')
			{
				?>
				$("#<?php echo $field->code; ?>").select2();
				<?php
			}
		}

		//Concaténation des champs dans le libelle asset
		foreach($asset->assetType->TField as $k=>$field) {

			if($field->inlibelle == "oui"){
				$chaineid .= "#".$field->code.", ";
				$chaineval .= "$('#".$field->code."').val().toUpperCase()+' '+";
			}

		}
		$chaineval = substr($chaineval, 0,-5);
		$chaineid = substr($chaineid, 0,-2);
		?>
			$('<?php echo $chaineid; ?>').bind("keyup change", function(e) {
				$('#libelle').val(<?php echo $chaineval; ?>);
			});
		});
	</script>
	<?php

	/*echo '<pre>';
	print_r($TFields);
	echo '</pre>';exit;*/

	if($mode == "edit" && empty($asset->serial_number)){
		$asset->serial_number = $asset->getNextValue($ATMdb);
	}

	if(defined('ASSET_FICHE_TPL')){
		$tpl_fiche = ASSET_FICHE_TPL;
	}
	else{
		$tpl_fiche = "fiche.tpl.php";
	}

    $fk_product = (int)GETPOST('fk_product');
    if(!$fk_product) $fk_product=$asset->fk_product;

	if($fk_product>0){
		dol_include_once('/product/class/product.class.php');
		$product = new Product($db);
		$product->fetch($fk_product);
		$product->fetch_optionals($product->id);
	}

	print $TBS->render('tpl/'.$tpl_fiche
		,array(
			'assetField'=>$TFields
		)
		,array(
			'asset'=>array(
				'id'=>$asset->getId()
				/*,'reference'=>$form->texte('', 'reference', $dossier->reference, 100,255,'','','Grab')*/
				,'serial_number'=>$html->textwithpicto($form->texte('', 'serial_number', $asset->serial_number, 100,255,'','','Grab'), $langs->trans('CreateAssetFromProductErrorBadMask'), 1, 'help', '', 0, 3)
				,'produit'=>_fiche_visu_produit($asset,$mode)
				,'entrepot'=>_fiche_visu_produit($asset,$mode,'warehouse')
				,'prix_achat'=>_fiche_visu_prix($asset, $mode)
				,'societe'=>_fiche_visu_societe($asset,$mode)
				,'societe_localisation'=>_fiche_visu_societe($asset,$mode,"societe_localisation")
				,'lot_number'=>$html->textwithpicto($form->texte('', 'lot_number', $asset->lot_number, 100,255,'','','Grab'), $langs->trans('CreateAssetFromProductNumLot'), 1, 'help', '', 0, 3)
				,'dluo'=>(!empty($conf->global->ASSET_SHOW_DLUO))?($html->textwithpicto($form->calendrier('', 'dluo', $asset->dluo), $langs->trans('AssetDescDLUO'), 1, 'help', '', 0, 3).((!empty($asset->dluo) && (!ctype_digit($asset->dluo)?strtotime($asset->dluo):$asset->dluo) < time())?img_warning($langs->trans('Asset_DLUO_outdated')):'')):''
				,'contenance_value'=>$form->texte('', 'contenance_value',$asset->contenance_value , 12,50,'','','0.00')
				,'contenance_units'=>_fiche_visu_units($asset, $mode, 'contenance_units',-6)
				,'contenancereel_value'=>$form->texte('', 'contenancereel_value', $asset->contenancereel_value, 12,50,'','','0.00')
				,'contenancereel_units'=>_fiche_visu_units($asset, $mode, 'contenancereel_units',-6)
				,'point_chute'=>$form->texte('', 'point_chute', ($asset->getId()) ? $asset->point_chute : $asset->assetType->point_chute, 12,10,'','','Grab')
				,'gestion_stock'=>$form->combo('','gestion_stock',$asset->TGestionStock,($asset->getId()) ? $asset->gestion_stock : $asset->assetType->gestion_stock)
				,'status'=>$form->combo('','status',$asset->TStatus,$asset->status)
				,'reutilisable'=>$form->combo('','reutilisable',array('oui'=>'oui','non'=>'non'),($asset->getId()) ? $asset->reutilisable : $asset->assetType->reutilisable)
				,'typehidden'=>$form->hidden('fk_asset_type', ($product->array_options['options_type_asset'] > 0) ? $product->array_options['options_type_asset'] : $asset->fk_asset_type )
				, 'canCreate'=> $user->rights->{ATM_ASSET_NAME}->all->write
			)
			,'stock'=>array(
				'type_mvt'=>$form2->combo('','type_mvt',array(''=>'','retrait'=>'Stock decrease','ajout'=>'Stock increase'),'')
				,'qty'=>$form2->texte('', 'qty', '', 12,10,'','','')
				,'subprice'=>$form2->texte('', 'subprice', '', 12, '', '', '')
				,'commentaire_mvt'=>$form2->zonetexte('','commentaire_mvt','',100)
			)
			,'assetNew' =>array(
				'typeCombo'=> count($asset->TType) ? $form->combo('','fk_asset_type',$asset->TType,$asset->fk_asset_type): "Any Type"
				,'validerType'=>$form->btsubmit('Valider', 'validerType')

			)
			,'affaire'=>$TAffaire
			,'view'=>array(
				'mode'=>$mode
				,'clinomadic'=>($conf->clinomadic->enabled) ? 'view' : 'none'
				,'use_lot_in_of'=>(int)$conf->global->USE_LOT_IN_OF
				,'entrepot'=>($conf->clinomadic->enabled) ? _fiche_visu_produit($asset,'edit','warehouse') : 'none'
				,'module_financement'=>(int)isset($conf->global->MAIN_MODULE_FINANCEMENT)
				,'champs_production'=>$conf->global->ASSET_USE_PRODUCTION_ATTRIBUT
				,'champ_prix_achat'=> $conf->dispatch->enabled && $conf->global->DISPATCH_LINK_ASSET_TO_STOCK_MOVEMENT && $conf->global->ASSET_DISPLAY_MOUVEMENT_PRICE // conf cachées
				,'ASSET_SHOW_DLUO'=>$conf->global->ASSET_SHOW_DLUO
				,'langs'=>$langs
				,'liste'=>$liste->renderArray($PDOdb,$TAssetStock
					,array(
						'title'=>array(
							'date_cre'=>'Movement date'
							,'qty'  =>'Quantity	unité(s) Unit (s)'
							,'weight_units' => 'Unit'
							,'lot' => 'Lot'
							,'type' => 'Comment'
						)
						,'link'=>array_merge($ASSET_LINK_ON_FIELD,array())
						,'liste'=>array(
							'titre'=> 'Stock movements'
						)
					)
				)
			)
		)
	);

	$parameters = array('id'=>$asset->getId());
	$reshook = $hookmanager->executeHooks('formObjectOptions',$parameters,$asset,$mode);    // Note that $action and $object may have been modified by hook

	echo $form->end_form();
	// End of page

	llxFooter('$Date: 2011/07/31 22:21:57 $ - $Revision: 1.19 $');
}


function _fiche_visu_produit(TAsset &$asset, $mode, $type = '')
{
	global $db, $conf, $langs;

	dol_include_once('/product/class/html.formproduct.class.php');

	if(($mode=='edit' || $mode=='new') && $type == "") {
		ob_start();
		$html=new Form($db);
		print $html->textwithpicto($html->select_produits((!empty($_REQUEST['fk_product']))? $_REQUEST['fk_product'] :$asset->fk_product,'fk_product','',$conf->product->limit_size,0,-1,2,'',3,array()), $langs->trans('CreateAssetFromProductDescListProduct'), 1, 'help', '', 0, 3);

		return ob_get_clean();
	}
	elseif($type == "warehouse"){
		ob_start();

		$html=new FormProduct($db);

		if($mode=='edit' || $mode=='new'){
			echo $html->selectWarehouses($asset->fk_entrepot,'fk_entrepot', '', 1);
		}
		else{
			if(! empty($asset->fk_entrepot))
			{
				$entrepot = new Entrepot($db);
				$entrepot->fetch($asset->fk_entrepot);
				echo $entrepot->getNomUrl(1);
			}
			else
			{
				echo '-';
			}
		}
		//($asset->$name != "")? $asset->$name : $defaut

		return ob_get_clean();
	}
	else {
		if($asset->fk_product > 0) {
			dol_include_once('/product/class/product.class.php');

			$product = new Product($db);
			$product->fetch($asset->fk_product);
			return $product->getNomUrl(1) . ' - ' . $product->label;

			return '<a href="'.DOL_URL_ROOT.'/product/fiche.php?id='.$asset->fk_product.'" style="font-weight:bold;">'.img_picto('','object_product.png', '', 0).' '. $product->label.'</a>';
		} else {
			return 'Not defined';
		}
	}
}


function _fiche_visu_societe(TAsset &$asset, $mode, $type="societe")
{
	global $db;

	if($mode=='edit') {
		ob_start();

		$html=new Form($db);
		if($type == "societe"){
			echo $html->select_company($asset->fk_soc,'fk_soc','',1);
		}
		else{
			echo $html->select_company($asset->fk_societe_localisation,'fk_societe_localisation','',1);
		}

		return ob_get_clean();

	}
	else {

			dol_include_once('/societe/class/societe.class.php');

			if($type == 'societe' && $asset->fk_soc>0){
				$soc = new Societe($db);
				$soc->fetch($asset->fk_soc);
				return $soc->getNomUrl(1);
			}
			elseif($type=='societe_localisation' && $asset->fk_societe_localisation>0){
				$soc = new Societe($db);
				$soc->fetch($asset->fk_societe_localisation);
				return $soc->getNomUrl(1);
			}



	}

	return '';
}


function _fiche_visu_affaire(TAsset &$asset, $mode)
{
	global $db;

	if($mode=='edit') {
		ob_start();

		$html=new Form($db);
		echo $html->select_company($asset->fk_soc,'fk_soc','',1);

		return ob_get_clean();

	}
	else {
		if($asset->fk_soc > 0) {
			dol_include_once('/societe/class/societe.class.php');

			$soc = new Societe($db);
			$soc->fetch($asset->fk_soc);

			return '<a href="'.DOL_URL_ROOT.'/societe/soc.php?socid='.$asset->fk_soc.'" style="font-weight:bold;"><img border="0" src="'.DOL_URL_ROOT.'/theme/atm/img/object_company.png"> '.$soc->nom.'</a>';
		} else {
			return 'Not defined';
		}
	}
}


function _fiche_visu_units(&$asset, $mode, $name,$defaut=-3)
{
	global $db;

	dol_include_once('/product/class/html.formproduct.class.php');

	$ATMdb = new TPDOdb;
	$objType = new TAsset_type;
	$objType->load($ATMdb, $asset->fk_asset_type);

	if($mode=='edit')
	{
		$html=new FormProduct($db);

		if($asset->gestion_stock == 'UNIT' || $asset->assetType->measuring_units == 'unit')
		{
			if ((float) DOL_VERSION > 9) return $html->selectMeasuringUnits($name, $asset->assetType->measuring_units, ($asset->getId()) ? $asset->$name : $asset->assetType->$name);
			else return custom_load_measuring_units($name, $asset->assetType->measuring_units, ($asset->getId()) ? $asset->$name : $asset->assetType->$name); // <= maintenant même pour unit
			//return "unité(s)"; <= avant
		}
		else
		{
			if ((float) DOL_VERSION > 9) return $html->selectMeasuringUnits($name, $asset->assetType->measuring_units, ($asset->getId()) ? $asset->$name : $asset->assetType->$name);
			else return custom_load_measuring_units($name, $asset->assetType->measuring_units, ($asset->getId()) ? $asset->$name : $asset->assetType->$name);
			//($asset->$name != "")? $asset->$name : $defaut
		}
	}
	elseif($mode=='new')
	{
		$html=new FormProduct($db);

		if($asset->gestion_stock == 'UNIT' || $asset->assetType->measuring_units == 'unit'){
			if ((float) DOL_VERSION > 9) return $html->selectMeasuringUnits($name, $asset->assetType->measuring_units, ($asset->getId()) ? $defaut : $asset->assetType->$name);
			else return custom_load_measuring_units($name, $asset->assetType->measuring_units, ($asset->getId()) ? $defaut : $asset->assetType->$name);
			//return "unité(s)";
		}
		else
		{
			if ((float) DOL_VERSION > 9) return $html->selectMeasuringUnits($name, $asset->assetType->measuring_units, ($asset->getId()) ? $defaut : $asset->assetType->$name);
			else return custom_load_measuring_units($name, $asset->assetType->measuring_units, ($asset->getId()) ? $defaut : $asset->assetType->$name);
			//($asset->$name != "")? $asset->$name : $defaut
		}
	}
	else
	{
		if($asset->gestion_stock == 'UNIT' || $asset->assetType->measuring_units == 'unit'){
			return "unité(s)";
		}
		else
		{
			return measuring_units_string($asset->$name, $asset->assetType->measuring_units);
		}
	}
}


function _fiche_visu_prix(TAsset &$asset, $mode = 'view')
{
    global $db;

	$elel = TAsset::get_element_element($asset->id, 'TAssetOFLine', 'DolStockMouv');

	if (!empty($elel) && empty($asset->valeur))
	{
		$sql = "SELECT price FROM ".MAIN_DB_PREFIX."stock_mouvement";
		$sql.= " WHERE rowid = " . $elel[0];

		$res = $db->query($sql);
		if($res && $db->num_rows($res))
		{
			$obj = $db->fetch_object($res);
			$asset->valeur = $obj->price;
		}
	}

    if ($mode == 'view')
	{
		return price($asset->valeur) . " €";

	}
    else
	{
		$form=new TFormCore($_SERVER['PHP_SELF'],'formeq','POST');
		$form->Set_typeaff($mode);

		return $form->texte('', 'valeur', $asset->valeur, 100,255,'','',$asset->valeur);
	}
}


function _traceability(TPDOdb &$PDOdb, TAsset &$asset){
	global $db, $conf, $langs;

	$langs->load("sendings");
	$langs->load("orders");

	llxHeader('',$langs->trans('Asset'),'','');
	print dol_get_fiche_head(assetatmPrepareHead( $asset, 'asset') , 'traceability', $langs->trans('Asset'));

	//pre($assetLot,true);
//	$asset->traCeability($PDOdb); // old school
	showTraceability($PDOdb, $asset); // new school ;)

	dol_fiche_end();
	llxFooter();
}

function _object_linked(TPDOdb &$PDOdb, TAsset &$asset)
{
	global $db, $conf, $langs;

	llxHeader('',$langs->trans('Asset'),'','');
	print dol_get_fiche_head(assetatmPrepareHead( $asset, 'asset') , 'object_linked', $langs->trans('Asset'));

	$assetLot = new TAssetLot;
	$assetLot->loadBy($PDOdb, $asset->lot_number, 'lot_number');

	$assetLot->getTraceabilityObjectLinked($PDOdb,$asset->getId());

	// Liste des expéditions liés à l'équipement
	if(! empty($conf->expedition->enabled)) {
		print '<br />';
		_listeTraceabilityExpedition($PDOdb,$assetLot);
	}

	// Liste des commandes fournisseurs liés à l'équipement
	if(! empty($conf->fournisseur->enabled)) {
		print '<br />';
		_listeTraceabilityCommandeFournisseur($PDOdb,$assetLot);
	}

	// Liste des commandes clients liés à l'équipement
	if(! empty($conf->commande->enabled)) {
		print '<br />';
		_listeTraceabilityObject($PDOdb, $assetLot, 'commande');
	}

	// Liste des contrats clients liés à l'équipement
	if(! empty($conf->contrat->enabled)) {
		print '<br />';
		_listeTraceabilityObject($PDOdb, $assetLot);
	}

	// Liste des interventions clients liées à l'équipement
	if(! empty($conf->ficheinter->enabled)) {
		print '<br />';
		_listeTraceabilityObject($PDOdb, $assetLot, 'Fichinter');
	}

	// Liste des tickets liés à l'équipement
	if(! empty($conf->ticketsup->enabled)) {
		print '<br />';
		_listeTraceabilityObject($PDOdb, $assetLot, 'Ticketsup');
	}

	// Liste des OF liés à l'équipement
	if(! empty($conf->of->enabled)) {
		print '<br />';
		_listeTraceabilityOf($PDOdb,$assetLot);
	}
}

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
				'ref_fourn' => 'Ref Supplier',
				'date_commande' => 'Order date',
				'total_ht' => 'Total (excl. tax)',
				'date_livraison' => 'Delivery date',
				'status' => 'Status',
			)
		)
	);
}

function _listeTraceabilityObject(&$PDOdb, $assetLot, $type_object='Contrat') {

	$listeview = new TListviewTBS($assetLot->getId());

	switch($type_object) {

		case 'Contrat' :
			$title = 'Customer contracts';
			break;
		case 'Fichinter' :
			$title = 'Customer interventions ';
			break;
		case 'Ticketsup' :
			$title = 'Tickets';
			break;
		case 'Commande':
			$title = 'Sales Order';
			break;
	}

	print $listeview->renderArray($PDOdb,$assetLot->TTraceabilityObjectLinked[$type_object]
		,array(
			'liste'=>array(
				'titre' => $title
			),
			'title'=>array(
				'ref' => 'Reference',
				'societe' => 'Third Party',
				'status' => 'Status',
				'date' => 'Date creation',
				'date_last_modif' => 'Last modification date',
			)
		)
	);

}

function _listeTraceabilityOf(&$PDOdb,&$assetLot){

	$listeview = new TListviewTBS($assetLot->getId());

	//pre($asset->TTraceabilityObjectLinked['of'],true);

	print $listeview->renderArray($PDOdb,$assetLot->TTraceabilityObjectLinked['of']
		,array(
			'liste'=>array(
				'titre' => "Manufacturing Order",
			),
			'title'=>array(
				'ref' => 'Reference',
				'societe' => 'Third Party',
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

?>
