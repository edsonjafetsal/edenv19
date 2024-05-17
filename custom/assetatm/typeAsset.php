<?php
	require('config.php');
	require('class/asset.class.php');
	require('lib/asset.lib.php');

	$contextpage = isset($_REQUEST['action']) || isset($_REQUEST['id']) ? 'assettypecard' : 'assettypelist';

	$hookmanager->initHooks(array($contextpage));

	$langs->load('assetatm@assetatm');

	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$PDOdb=new TPDOdb;
	$asset=new TAsset_type;

	$mesg = '';
	$error=false;

	//pre($_REQUEST);

	if(isset($_REQUEST['default_dluo_nb']) && isset($_REQUEST['default_dluo_unit'])) $_REQUEST['default_dluo'] = $_REQUEST['default_dluo_nb'].' '.$_REQUEST['default_dluo_unit'];

	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				$asset->set_values($_REQUEST);
				_fiche($PDOdb, $asset,'edit');

				break;
			case 'edit'	:
				//$PDOdb->db->debug=true;
				$asset->load($PDOdb, $_REQUEST['id']);

				_fiche($PDOdb, $asset,'edit');
				break;

			case 'save':
				//$PDOdb->db->debug=true;
				$asset->load($PDOdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Modifications done.</div>';
				$mode = 'view';

				$asset->set_values($_REQUEST);

				$asset->save($PDOdb);
				reload_menus();

				header('Location: ' . dol_buildpath('/assetatm/typeAsset.php?id=' . $asset->rowid, 1));
				exit;

			case 'view':
				$asset->load($PDOdb, $_REQUEST['id']);
				_fiche($PDOdb, $asset,'view');
				break;

			case 'delete':
				$asset->load($PDOdb, $_REQUEST['id']);
				//$PDOdb->db->debug=true;

				//avant de supprimer, on vérifie qu'aucune asset n'est de ce type. Sinon on ne le supprime pas.
				if (!$asset->isUsedByAsset($PDOdb)){
					if ($asset->delete($PDOdb)){
						reload_menus();
						?>
						<script language="javascript">
							document.location.href="?delete_ok=1";
						</script>
						<?php
					}
					else{
						$mesg = '<div class="error">This type of equipment cannot be deleted</div>';
						_liste($PDOdb, $asset);
					}
				}
				else{
					$mesg = '<div class="error">The type of equipment is used on other equipment and cannot be deleted.</div>';
					_liste($PDOdb, $asset);
				}


				break;
		}
	}
	elseif(isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
		$asset->load($PDOdb, $_REQUEST['id']);

		_fiche($PDOdb, $asset, 'view');

	}
	else {
		/*
		 * Liste
		 */
		 _liste($PDOdb, $asset);
	}


	$PDOdb->close();


function _liste(&$PDOdb, &$asset) {
	global $langs,$conf, $db;

	global $mesg, $error;

	llxHeader('',$langs->trans('AssetType'));

	if(!empty($mesg)) echo $mesg;

	//print dol_get_fiche_head(array()  , '', $langs->trans('AssetType'));

	$r = new TSSRenderControler($asset);
	$sql="SELECT rowid, libelle, code, masque, '' as actions
		FROM ".MAIN_DB_PREFIX."assetatm_type
		WHERE 1 ";

	$TOrder = array('rowid'=>'ASC', 'noOrder' => array('actions'));
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');

	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
	//print $page;
	$r->liste($PDOdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'libelle'=>'<a href="?id=@rowid@&action=view">@val@</a>'
			,'actions'=>"<a style=\"cursor:pointer;\" onclick=\"if (window.confirm('Do you want to delete the \'élément ?')){document.location.href='?id=@rowid@&action=delete'};\">".img_picto('','delete.png', '', 0)."</a>"
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
			,'masque'=>$langs->trans('AssetNumberingMask')
			,'actions'=>''
		)
		,'orderby'=>$TOrder

	));

	echo '<div class="tabsAction">';
	echo '<a class="butAction" href="typeAsset.php?action=new">'.$langs->trans('AssetNewType').'</a>';
	echo '</div>';

	llxFooter();
}

function _fiche(&$PDOdb, &$asset, $mode) {
	global $langs,$db,$user;

	//pre($asset);

	llxHeader('',$langs->trans('AssetType'), '', '', 0, 0);

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$doliform=new Form($db);

	$form->Set_typeaff($mode);
	echo $form->hidden('id', $asset->getId());
	echo $form->hidden('action', 'save');

	$TBS=new TTemplateTBS();

	$langs->load('other');
	$measuring_units_values = array(
		'unit' => $langs->trans('AssetStockManagementUNIT'),
		'weight' => $langs->trans('Weight'),
		'size' => $langs->trans('Length'),
		'surface' => $langs->trans('Surface'),
		'volume' => $langs->trans('Volume')
	);

	$token = '';
	if(function_exists('newToken')){ $token = '&token='.newToken(); }

	print $TBS->render('tpl/asset.type.tpl.php'
		,array()
		,array(
			'assetType'=>array(
				'id'=>$asset->getId()
				,'code'=>$form->texte('', 'code', $asset->code, 20,255,'','',$langs->trans('AssetTodo'))
				,'libelle'=>$form->texte('', 'libelle', $asset->libelle, 20,255,'','',$langs->trans('AssetTodo'))
				,'masque'=>$form->texte('', 'masque', $asset->masque, 20,80,'','',$langs->trans('AssetTodo'))
				,'info_masque'=>$doliform->textwithpicto('',$asset->info(),1,0,'',0,3)
				,'point_chute'=>$form->texte('', 'point_chute', $asset->point_chute, 12,10,'','',$langs->trans('AssetTodo'))
				,'gestion_stock'=>$form->combo('','gestion_stock',$asset->TGestionStock,$asset->gestion_stock)
				,'perishable'=>$doliform->textwithpicto($form->combo('','perishable',array(0 => $langs->trans('no'), 1 => $langs->trans('yes')),$asset->perishable), $langs->trans('AssetDescPerishable'),1,0,'',0,3)
				,'cumulate'=>$doliform->textwithpicto($form->combo('','cumulate',array(0 => $langs->trans('no'), 1 => $langs->trans('yes')),$asset->cumulate), $langs->trans('AssetDescCumulate'),1,0,'',0,3)
				,'reutilisable'=>$form->combo('','reutilisable',array('oui'=>$langs->trans('yes'),'non'=>$langs->trans('no')),$asset->reutilisable)
				,'measuring_units'=>$form->combo('', 'measuring_units', $measuring_units_values, $asset->measuring_units, 1 , 'loadMeasuringUnits(this);')
				,'contenance_value'=>$form->texte('', 'contenance_value', $asset->contenance_value, 12,10,'','','')
				,'contenance_units'=>_fiche_visu_units($asset, $mode, 'contenance_units',-6)
				,'contenancereel_value'=>$form->texte('', 'contenancereel_value', $asset->contenancereel_value, 12,10,'','','')
				,'contenancereel_units'=>_fiche_visu_units($asset, $mode, 'contenancereel_units',-6)
				,'supprimable'=>$form->hidden('supprimable', 1)
			    ,'default_DLUO'=>$form->texte('','default_dluo_nb',$asset->default_dluo_nb,5,255).$form->combo('', 'default_dluo_unit', $asset->TDefaultDluoUnit, $asset->default_dluo_unit)
			)
			,'view'=>array(
				'mode'=>$mode
				,'nbChamps'=>count($asset->TField)
				,'head'=>dol_get_fiche_head(assetatmPrepareHead($asset)  , 'fiche',$langs->trans('AssetType'))
				,'onglet'=>dol_get_fiche_head(array()  , '', $langs->trans('AssetCreateType'))
				,'langs'=>$langs
				,'token'=>$token
			)

		)

	);

	echo $form->end_form();
	// End of page

	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

function _fiche_visu_units(&$asset, $mode, $name,$defaut=-3) {
	global $db,$langs;

	require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';

	$langs->load("other");

	if (!empty($asset->measuring_units)) $type = $asset->measuring_units;
	else $type = 'weight';

	if($mode=='edit') {
		ob_start();

		$html=new FormProduct($db);

		if($type == 'unit'){
			echo $langs->trans('Assetunit_s');
		}
		else{
			echo $html->select_measuring_units($name, $type, $asset->$name);
			//($asset->$name != "")? $asset->$name : $defaut
		}

		return ob_get_clean();

	}
	elseif($mode=='new'){
		ob_start();

		$html=new FormProduct($db);

		if($type == 'unit'){
			echo $langs->trans('Assetunit_s');
		}
		else{
			echo $html->select_measuring_units($name, $type, $defaut);
			//($asset->$name != "")? $asset->$name : $defaut
		}

		return ob_get_clean();
	}
	else{
		ob_start();

		if($type == 'unit'){
			echo $langs->trans('Assetunit_s');
		}
		else{
			echo measuring_units_string($asset->$name, $type);
		}

		return ob_get_clean();
	}

	/*
	 *
			// Weight
            print '<tr><td>'.$langs->trans("Weight").'</td><td colspan="3">';
            print '<input name="weight" size="4" value="'.GETPOST('weight').'">';
            print $formproduct->select_measuring_units("weight_units","weight");
            print '</td></tr>';
            // Length
            print '<tr><td>'.$langs->trans("Length").'</td><td colspan="3">';
            print '<input name="size" size="4" value="'.GETPOST('size').'">';
            print $formproduct->select_measuring_units("size_units","size");
            print '</td></tr>';
            // Surface
            print '<tr><td>'.$langs->trans("Surface").'</td><td colspan="3">';
            print '<input name="surface" size="4" value="'.GETPOST('surface').'">';
            print $formproduct->select_measuring_units("surface_units","surface");
            print '</td></tr>';
            // Volume
            print '<tr><td>'.$langs->trans("Volume").'</td><td colspan="3">';
            print '<input name="volume" size="4" value="'.GETPOST('volume').'">';
            print $formproduct->select_measuring_units("volume_units","volume");
            print '</td></tr>';
	 *
	 */

}

