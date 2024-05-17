<?php
	require('config.php');
	require('class/asset.class.php');
	require('lib/asset.lib.php');

	$langs->load('assetatm@assetatm');

	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$ATMdb=new TPDOdb;
	$asset=new TAsset_type;

	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				$asset->set_values($_REQUEST);
				_fiche($ATMdb, $asset,'edit');

				break;
			case 'edit'	:
				$asset->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $asset,'edit');
				break;

			case 'save':
				//$ATMdb->db->debug=true;
				$asset->load($ATMdb, $_REQUEST['id']);
				$asset->set_values($_REQUEST);

				//$mesg = '<div class="ok">Modifications effectuées</div>';
				$mode = 'view';
				if(isset($_REQUEST['TField'])){
					if (!empty($asset->TField)){
						foreach($_REQUEST['TField'] as $k=>$field) {
							$asset->TField[$k]->set_values($field);
						}
					}
				}

				setEventMessage('AssetTypeModificationsDone');

				if ($_REQUEST['TNField']['libelle']!=''){
					$asset->addField($ATMdb, $_REQUEST['TNField']);
					setEventMessage('AssetTypeFieldCreated');
				}
				if(isset($_REQUEST['newField'])) {
					$mode = 'edit';
				}


				$asset->save($ATMdb);
				$asset->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $asset,$mode);
				break;

			case 'view':
				$asset->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $asset,'view');
				break;

			case 'deleteField':
				//$ATMdb->db->debug=true;
				if ($asset->delField($ATMdb, $_REQUEST['idField'])){
					setEventMessage('AssetTypeFieldDeleted');
				}
				else {
					setEventMessage('AssetTypeFieldCouldNotBeDeleted', 'errors');
				}
				$asset->load($ATMdb, $_REQUEST['id']);


				$mode = 'edit';
				_fiche($ATMdb, $asset,$mode);
				break;

		}
	}
	elseif(isset($_REQUEST['id'])) {
		$asset->load($ATMdb, $_REQUEST['id']);

		_fiche($ATMdb, $asset, 'view');

	}
	else {
		/*
		 * Liste
		 */
		 _liste($ATMdb, $asset);
	}


	$ATMdb->close();


function _liste(&$ATMdb, &$asset) {
	global $langs,$conf, $db;

	llxHeader('',$langs->trans('AssetType'));
	getStandartJS();

	$r = new TSSRenderControler($asset);
	$sql="SELECT rowid, code, libelle
		FROM ".MAIN_DB_PREFIX."assetatm_field
		WHERE fk_asset_type=".$asset->getId();

	$TOrder = array('Code'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');

	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
	//print $page;
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'code'=>'<a href="?id=@rowid@&action=view">@val@</a>'
		)
		,'translate'=>array()
		,'hide'=>array()
		,'type'=>array()
		,'liste'=>array(
			'titre'=>$langs->trans('AssetListType')
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>$langs->trans('AssetTypeMsgNothing')
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)

		)
		,'title'=>array(
			'rowid' => 'ID'
			,'libelle'=>$langs->trans('Label')
			,'code'=>$langs->trans('Code')
		)
		,'orderBy'=>$TOrder

	));

	$token = '';
	if(function_exists('newToken')){ $token = '&token='.newToken(); }

	print '<div style="text-align: right;"><a class="butAction" href="?action=add'.$token.'">';
	print $langs->trans('AssetAddType') ;
	print '</a></div>';

	llxFooter();
}

function _fiche(&$ATMdb, &$asset, $mode) {
	global $langs,$db,$user;

	llxHeader('',$langs->trans('AssetType'), '', '', 0, 0);

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $asset->getId());
	echo $form->hidden('action', 'save');



	//Champs
	$TFields=array();
	foreach($asset->TField as $k=>$field) {

		//print_r($field);

		$TFields[$k]=array(
				'id'=>$field->getId()
				,'code'=>$field->code //$form->texte('', 'TField['.$k.'][code]', $field->code, 20,255,'','','-')
				,'libelle'=>$form->texte('', 'TField['.$k.'1][libelle]', $field->libelle, 20,255,'','','-')
				,'indice'=>$k
				,'ordre'=>$form->hidden('TField['.$k.'][ordre]', $k, 'class="ordre'.$k.'"')
				,'type'=>$asset->TType[$field->type]
				,'options'=>$form->zonetexte('','TField['.$k.'][options]',$field->options,50,5)
				,'obligatoire'=>$form->combo('','TField['.$k.'][obligatoire]',array($langs->trans('Yes'), $langs->trans('No')),$field->obligatoire)
				,'inliste'=>$form->combo('','TField['.$k.'][inliste]',array("non"=>$langs->trans('No'),"oui"=>$langs->trans('Yes')),$field->inliste)
				,'inlibelle'=>$form->combo('','TField['.$k.'][inlibelle]',array("non"=>$langs->trans('No'),"oui"=>$langs->trans('Yes')),$field->inlibelle)
				,'numero'=>$k
			);
	}

	$TBS=new TTemplateTBS();

	print $TBS->render('tpl/asset.type.field.tpl.php'
		,array(
			'assetField'=>$TFields
		)
		,array(
			'assetType'=>array(
				'id'=>$asset->getId()
				,'code'=> $asset->code
				,'libelle'=> $asset->libelle
				,'point_chute'=>$asset->point_chute
				,'gestion_stock'=>$asset->TGestionStock[$asset->gestion_stock]
				,'reutilisable'=>($asset->reutilisable == 'oui' ? $langs->trans('Yes') : $langs->trans('No'))
				,'contenance_value'=>$asset->contenance_value
				,'contenance_units'=>_fiche_visu_units($asset, 'view', 'contenance_units',-6)
				,'contenancereel_value'=>$asset->contenancereel_value
				,'contenancereel_units'=>_fiche_visu_units($asset, 'view', 'contenancereel_units',-6)
				,'titreChamps'=>load_fiche_titre($langs->trans('AssetListFields'),'', '', 0, '')
				,'pictoMove'=>img_picto('','grip.png', '', 0)
			)
			,'newField'=>array(
				//'hidden'=>$form->hidden('action', 'save')
				'code'=>$form->texte('', 'TNField[code]', '', 20,255)
				,'ordre'=>$form->hidden('TNField[ordre]', $k+1, 'class="ordre'.($k+1).'"')
				,'indice'=>$k+1
				,'libelle'=>$form->texte('', 'TNField[libelle]', '', 20,255,'','','-')
				,'type'=>$form->combo('', 'TNField[type]',$asset->TType, 'texte')
				,'options'=>$form->zonetexte('','TNField[options]','',50,5)
				,'obligatoire'=>$form->combo('','TNField[obligatoire]',array($langs->trans('Yes'),$langs->trans('No')),'0')
				,'inliste'=>$form->combo('','TNField[inliste]',array("non"=>$langs->trans('No'),"oui"=>$langs->trans('Yes')),'0')
				,'inlibelle'=>$form->combo('','TNField[inlibelle]',array("non"=>$langs->trans('No'),"oui"=>$langs->trans('Yes')),'0')
			)
			,'view'=>array(
				'mode'=>$mode
				,'nbChamps'=>count($asset->TField)
				,'head'=>dol_get_fiche_head(assetatmPrepareHead($asset)  , 'field', $langs->trans('AssetType'))
				,'onglet'=>dol_get_fiche_head(array()  , '', $langs->trans('AssetType'))
				,'langs'=>$langs
			)

		)

	);

	echo $form->end_form();

	// End of page
	llxFooter();
}

function _fiche_visu_units(&$asset, $mode, $name,$defaut=-3) {
	global $db, $langs;

	require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';

	$langs->load("other");

	if($mode=='edit') {
		ob_start();

		$html=new FormProduct($db);

		echo $html->select_measuring_units($name, "weight", $asset->$name);
		//($asset->$name != "")? $asset->$name : $defaut

		return ob_get_clean();

	}
	elseif($mode=='new'){
		ob_start();

		$html=new FormProduct($db);

		echo $html->select_measuring_units($name, "weight", $defaut);
		//($asset->$name != "")? $asset->$name : $defaut

		return ob_get_clean();
	}
	else{
		ob_start();

		echo measuring_units_string($asset->$name, "weight");

		return ob_get_clean();
	}
}
