<?php

    function assetatmAdminPrepareHead()
    {
        global $langs, $conf;

        $langs->load("assetatm@assetatm");

        $h = 0;
        $head = array();

        $head[$h][0] = dol_buildpath("/assetatm/admin/admin.php", 1);
        $head[$h][1] = $langs->trans("Parameters");
        $head[$h][2] = 'settings';
        $h++;

		$head[$h][0] = dol_buildpath("/assetatm/admin/migration_equipement.php", 1);
		$head[$h][1] = $langs->trans("Migration");
		$head[$h][2] = 'Migration';
		$h++;


        // Show more tabs from modules
        // Entries must be declared in modules descriptor with line
        //$this->tabs = array(
        //	'entity:+tabname:Title:@asset:/assetatm/mypage.php?id=__ID__'
        //); // to add new tab
        //$this->tabs = array(
        //	'entity:-tabname:Title:@asset:/assetatm/mypage.php?id=__ID__'
        //); // to remove a tab
        complete_head_from_modules($conf, $langs, null, $head, $h, 'assetatm');

        return $head;
    }

	function assetatmPrepareHead(&$asset,$type='type-asset') {
		global $user, $conf, $langs;

		switch ($type) {
			case 'type-asset':
				return array(
					array(dol_buildpath('/assetatm/typeAsset.php?id='.$asset->getId(),2), $langs->trans('Card'),'fiche')
					,array(dol_buildpath('/assetatm/typeAssetField.php?id='.$asset->getId(),2), $langs->trans('AssetTypeFields'),'field')
				);
				break;
			case 'asset':
				return array(
						array(dol_buildpath('/assetatm/fiche.php?id='.$asset->getId(),2), 'Card','fiche'),
						array(dol_buildpath('/assetatm/fiche.php?action=traceability&id='.$asset->getId(),2), 'Traceability ','traceability'),
						array(dol_buildpath('/assetatm/fiche.php?action=object_linked&id='.$asset->getId(),2), 'Linked Objects','object_linked')
					);
				break;
			case 'assetlot':
				return array(
						array(dol_buildpath('/assetatm/fiche_lot.php?id='.$asset->getId(),2), 'Fiche','fiche'),
						//array(dol_buildpath('/assetatm/fiche_lot.php?action=traceability&id='.$asset->getId(),2), 'Traçabilité','traceability'), // cette partie ne fonctionne plus depuis la refacto de PH, mais je suis pas sur qu'elle ai fonctionné un jour...
						array(dol_buildpath('/assetatm/fiche_lot.php?action=object_linked&id='.$asset->getId(),2), 'Linked Objects','object_linked')
					);
				break;
		}

	}



	/**
	 *  Override
	 * 	Return a combo box with list of units
	 *  For the moment, units labels are defined in measuring_units_string
	 *
	 *  @param	string		$name                Name of HTML field
	 *  @param  string		$measuring_style     Unit to show: weight, size, surface, volume
	 *  @param  string		$default             Force unit
	 * 	@param	int			$adddefault			Add empty unit called "Default"
	 * 	@return	void
	 */
	function custom_load_measuring_units($name='measuring_units', $measuring_style='', $default='0', $adddefault=0)
	{
		global $langs,$conf,$mysoc;
		$langs->load("other");

		$return='';

		$measuring_units=array();
		if ($measuring_style == 'weight') $measuring_units=array(-6=>1,-3=>1,0=>1,3=>1,99=>1);
		else if ($measuring_style == 'size') $measuring_units=array(-3=>1,-2=>1,-1=>1,0=>1,98=>1,99=>1);
        else if ($measuring_style == 'surface') $measuring_units=array(-6=>1,-4=>1,-2=>1,0=>1,98=>1,99=>1);
		else if ($measuring_style == 'volume') $measuring_units=array(-9=>1,-6=>1,-3=>1,0=>1,88=>1,89=>1,97=>1,99=>1,/* 98=>1 */);  // Liter is not used as already available with dm3
		else if ($measuring_style == 'unit') $measuring_units=array(0=>0);

		$return.= '<select class="flat" name="'.$name.'">';
		if ($adddefault) $return.= '<option value="0">'.$langs->trans("Default").'</option>';

		foreach ($measuring_units as $key => $value)
		{
			$return.= '<option value="'.$key.'"';
			if ($key == $default)
			{
				$return.= ' selected="selected"';
			}
			//$return.= '>'.$value.'</option>';
			if ($measuring_style == 'unit') $return.= '>unité(s)</option>';
			else $return.= '>'.measuring_units_string($key,$measuring_style).'</option>';
		}
		$return.= '</select>';

		return $return;
	}

	/**
	 *	Override de la fonction classique de la class FormProject
	 *  Show a combo list with projects qualified for a third party
	 *
	 *	@param	int		$socid      	Id third party (-1=all, 0=only projects not linked to a third party, id=projects not linked or linked to third party id)
	 *	@param  int		$selected   	Id project preselected
	 *	@param  string	$htmlname   	Nom de la zone html
	 *	@param	int		$maxlength		Maximum length of label
	 *	@param	int		$option_only	Option only
	 *	@param	int		$show_empty		Add an empty line
	 *	@return string         		    select or options if OK, void if KO
	 */
	function custom_select_projects($socid=-1, $selected='', $htmlname='projectid', $type_aff = 'view', $maxlength=25, $option_only=0, $show_empty=1)
	{
		global $user,$conf,$langs,$db;

		require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';

		$out='';

		if ($type_aff == 'view')
		{
			if ($selected > 0)
			{
				$project = new Project($db);
				$project->fetch($selected);

				//return dol_trunc($project->ref,18).' - '.dol_trunc($project->title,$maxlength);
				return $project->getNomUrl(1).' - '.dol_trunc($project->title,$maxlength);
			}
			else
			{
				return $out;
			}
		}

		$hideunselectables = false;
		if (! empty($conf->global->PROJECT_HIDE_UNSELECTABLES)) $hideunselectables = true;

		$projectsListId = false;
		if (empty($user->rights->projet->all->lire))
		{
			$projectstatic=new Project($db);
			$projectsListId = $projectstatic->getProjectsAuthorizedForUser($user,0,1);
		}

		// Search all projects
		$sql = 'SELECT p.rowid, p.ref, p.title, p.fk_soc, p.fk_statut, p.public';
		$sql.= ' FROM '.MAIN_DB_PREFIX .'projet as p';
		$sql.= " WHERE p.entity = ".$conf->entity;
		if ($projectsListId !== false) $sql.= " AND p.rowid IN (".$projectsListId.")";
		if ($socid == 0) $sql.= " AND (p.fk_soc=0 OR p.fk_soc IS NULL)";
		if ($socid > 0)  $sql.= " AND (p.fk_soc=".$socid." OR p.fk_soc IS NULL)";
		$sql.= " ORDER BY p.ref ASC";


		$resql=$db->query($sql);
		if ($resql)
		{
			if (empty($option_only)) {
				$out.= '<select class="flat" name="'.$htmlname.'">';
			}
			if (!empty($show_empty)) {
				$out.= '<option value="0">&nbsp;</option>';
			}
			$num = $db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				while ($i < $num)
				{
					$obj = $db->fetch_object($resql);
					// If we ask to filter on a company and user has no permission to see all companies and project is linked to another company, we hide project.
					if ($socid > 0 && (empty($obj->fk_soc) || $obj->fk_soc == $socid) && ! $user->rights->societe->lire)
					{
						// Do nothing
					}
					else
					{
						$labeltoshow=dol_trunc($obj->ref,18);
						//if ($obj->public) $labeltoshow.=' ('.$langs->trans("SharedProject").')';
						//else $labeltoshow.=' ('.$langs->trans("Private").')';
						if (!empty($selected) && $selected == $obj->rowid && $obj->fk_statut > 0)
						{
							$out.= '<option value="'.$obj->rowid.'" selected="selected">'.$labeltoshow.' - '.dol_trunc($obj->title,$maxlength).'</option>';
						}
						else
						{
							$disabled=0;
							$labeltoshow.=' '.dol_trunc($obj->title,$maxlength);
							if (! $obj->fk_statut > 0)
							{
								$disabled=1;
								$labeltoshow.=' - '.$langs->trans("Draft");
							}
							if ($socid > 0 && (! empty($obj->fk_soc) && $obj->fk_soc != $socid))
							{
								$disabled=1;
								$labeltoshow.=' - '.$langs->trans("LinkedToAnotherCompany");
							}

							if ($hideunselectables && $disabled)
							{
								$resultat='';
							}
							else
							{
								$resultat='<option value="'.$obj->rowid.'"';
								if ($disabled) $resultat.=' disabled="disabled"';
								//if ($obj->public) $labeltoshow.=' ('.$langs->trans("Public").')';
								//else $labeltoshow.=' ('.$langs->trans("Private").')';
								$resultat.='>';
								$resultat.=$labeltoshow;
								$resultat.='</option>';
							}
							$out.= $resultat;
						}
					}
					$i++;
				}
			}
			if (empty($option_only)) {
				$out.= '</select>';
			}

			if($conf->cliacropose->enabled) { // TODO c'est naze, à refaire en utilisant la vraie autocompletion dispo depuis dolibarr 3.8 pour utiliser l'auto complete projets de doli si active (j'avais rajouté un script ajax/projects.php pour acropose)

				// Autocomplétion
				if(isset($selected)) {

					$p = new Project($db);
					$p->fetch($selected);
					$selected_value = $p->ref;

				}
				$urloption = $placeholder = ''; // fix unset var
				$out = ajax_autocompleter($selected, $htmlname, DOL_URL_ROOT.'/projet/ajax/projects.php', $urloption, 1);
				$out .= '<input type="text" size="20" name="search_'.$htmlname.'" id="search_'.$htmlname.'" value="'.$selected_value.'"'.$placeholder.' />';

			}

			$db->free($resql);

			return $out;
		}
		else
		{
			dol_print_error($db);
			return '';
		}
	}


	function showTraceability(&$PDOdb, &$asset)
	{
		global $TObjectLoaded;
		$TObjectLoaded = array();

		$TObjectLoaded['asset'][$asset->getId()] = $asset;

		dol_include_once('/of/class/ordre_fabrication_asset.class.php');
		dol_include_once('/fourn/class/fournisseur.commande.class.php');
		dol_include_once('/expedition/class/expedition.class.php');

		?>
		<script type="text/javascript">
			$(document).ready(function(){
				$("#ChartFrom ul:first,#ChartTo ul:first").hide();
			    $("#ChartFrom ul:first").orgChart({container: $("#chart1")});
			    $("#ChartTo ul:first").orgChart({container: $("#chart2")});
			    $(".node").each(function(){
					if ($(this).hasClass('ordre_fabrication')) {
						$(this).css('background-color','#fbcece');
					}
					if ($(this).hasClass('equipement')) {
//						$(this).css('background-color','#F2F2F2');
					}

			    	if($(this).html().indexOf('COMMANDE') >= 0){
			    		$(this).children().css('background-color','#cefbce');
			    	}

					if ($(this).hasClass('expedition')) {
						$(this).css('background-color','#66CCFF');
					}
					if ($(this).hasClass('commande_fournisseur')) {
						$(this).css('background-color','#e0cefb');
					}
			    });
			});
	   	</script>
		<style type="text/css">
			.long-name { font-size: 12px; }
			div.orgChart div.node.level0, div.orgChart div.node.level2 { background-color: rgb(244, 227, 116); }
			div.orgChart div.node {
				width: inherit; min-width: 96px;
				height: inherit; min-height: 60px;
			}
		</style>

		<table width="100%">
			<tr>
				<td valign="top" width="50%">
					<div id="ChartFrom">
						<center><h1>Origin</h1></center>

						<?php showTraceabilityFrom($PDOdb, $asset); ?>

					</div>
					<div id="chart1"></div>
				</td>
				<td valign="top" width="50%">
					<div id="ChartTo" >
						<center><h1>Use</h1></center>

						<?php showTraceabilityTo($PDOdb, $asset); ?>

					</div>
					<div id="chart2"></div>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Provenance
	 *
	 * @param type $PDOdb
	 * @param type $asset
	 */
	function showTraceabilityFrom(&$PDOdb, &$asset, $level=1, $print_ul=true)
	{
		global $db, $TObjectLoaded, $conf;

		if ($level > 8) return; // TODO peut être faire une conf ?

		if ($print_ul) echo '<ul>';
		echo '<li class="equipement">';

		if (!empty($asset->lot_number)) echo '<a title="Lot" href="'.dol_buildpath('/assetatm/fiche_lot.php', 1).'?lot_number='.$asset->lot_number.'">Lot ['.$asset->lot_number.']</a><br />';
		echo $asset->getNomUrl(true, false, 1);

		if(!empty($conf->global->OF_MANAGE_ORDER_LINK_BY_LINE)) {

            $assetOfLine = $asset->getOfLine($PDOdb, 'TO_MAKE');
            $fk_of = $assetOfLine->fk_assetOf;
            if(empty($fk_of)) $fk_of = $asset->getOfId($PDOdb, 'TO_MAKE');
        }else $fk_of = $asset->getOfId($PDOdb, 'TO_MAKE');

		if ($fk_of > 0) // Equipement fabriqué depuis un OF
		{
			if (!empty($TObjectLoaded['OF'][$fk_of])) $of = $TObjectLoaded['OF'][$fk_of];
			else
			{
				$of = new TAssetOF;
				$of->load($PDOdb, $fk_of);
				$TObjectLoaded['OF'][$of->getId()] = $of;
			}

			echo '<ul><li class="ordre_fabrication">';
			echo $of->getNomUrl(1);
			if (!empty($of->fk_commande))
			{
				if (!empty($TObjectLoaded['commande'][$of->fk_commande])) $commande = $TObjectLoaded['commande'][$of->fk_commande];
				else
				{
                    if(!empty($conf->global->OF_MANAGE_ORDER_LINK_BY_LINE) && !empty($assetOfLine->fk_commandedet)) {
                        $orderLine = new OrderLine($db);
                        $orderLine->fetch($assetOfLine->fk_commandedet);
                        $of->fk_commande = $orderLine->fk_commande;
                    }
					$commande = new Commande($db);
					$commande->fetch($of->fk_commande);
					$TObjectLoaded['commande'][$commande->id] = $commande;
				}

				echo '<br />'.img_picto('', '1uparrow.png').'<br />'.$commande->getNomUrl(1);
			}

			$TAssetNeededId = $of->getTAssetId($PDOdb, 'NEEDED');
			echo '<ul>';
			foreach ($TAssetNeededId as &$stdClass)
			{
				if ($TObjectLoaded['asset'][$stdClass->fk_asset]) $assetNeeded = $TObjectLoaded['asset'][$stdClass->fk_asset];
				else
				{
					$assetNeeded = new TAsset;
					$assetNeeded->load($PDOdb, $stdClass->fk_asset, false);
					$TObjectLoaded['asset'][$assetNeeded->getId()] = $assetNeeded;
				}

				showTraceabilityFrom($PDOdb, $assetNeeded, $level+1, false);
			}
			echo '</ul>';
			echo '</li></ul>'."\n";
		}
		else
		{
			// TODO ELSE check si provient d'une réception
			$fk_commandefourn = $asset->getCommandeFournId($PDOdb);
			if ($fk_commandefourn > 0)
			{
				// PRINT <ul>commande fourn link</ul>
				if (!empty($TObjectLoaded['CF'][$fk_commandefourn])) $commandeFourn = $TObjectLoaded['CF'][$fk_commandefourn];
				else
				{
					$commandeFourn = new CommandeFournisseur($db);
					$commandeFourn->fetch($fk_commandefourn);
					$TObjectLoaded['CF'][$fk_commandefourn] = $commandeFourn;
				}

				echo '<ul><li class="commande_fournisseur">'.$commandeFourn->getNomUrl(1).'</li></ul>';
				// FIN
			}
		}

		echo '</li>';
		if ($print_ul) echo '</ul>';
	}

	/**
	 * Utilisation
	 *
	 * @param TPDOdb $PDOdb
	 * @param TAsset $asset
	 */
	function showTraceabilityTo(&$PDOdb, &$asset)
	{
		global $db, $langs, $TObjectLoaded;

		// Check si équipement a été expédié
		// Si oui alors, get expéditions + commande d'origine
		$TExpeditionId = $asset->getTExpeditionId($PDOdb);

		// Peut aussi avoir été utilisé en partie sur un OF
		// Check si présent dans un OF en tant que NEEDED
		$TOfId = $asset->getOfId($PDOdb, 'NEEDED');

		if (!empty($TExpeditionId) || !empty($TOfId))
		{
			echo '<ul>';
			echo '<li>';

			if (!empty($asset->lot_number)) echo '<a title="Lot" href="'.dol_buildpath('/assetatm/fiche_lot.php', 1).'?lot_number='.$asset->lot_number.'">Lot ['.$asset->lot_number.']</a><br />';
			echo $asset->getNomUrl(true, false, 1);

			foreach ($TExpeditionId as &$stdClass)
			{
				if ($TObjectLoaded['expedition'][$stdClass->fk_expedition]) $expedition = $TObjectLoaded['expedition'][$stdClass->fk_expedition];
				else
				{
					$expedition = new Expedition($db);
					$expedition->fetch($stdClass->fk_expedition);
					$TObjectLoaded['expedition'][$expedition->id] = $expedition;
				}

				echo '<ul>';
				echo '<li class="expedition">';
				echo '<h2>'.$langs->trans('Sending').'</h2>';
				if ($expedition->origin_id > 0 && $expedition->origin == 'commande')
				{
					if (!empty($TObjectLoaded['commande'][$expedition->origin_id])) $commande = $TObjectLoaded['commande'][$expedition->origin_id];
					else
					{
						$commande = new Commande($db);
						$commande->fetch($expedition->origin_id);
					}

					echo $commande->getNomUrl(1).'<br />'.img_picto('', '1downarrow.png').'<br />';
				}
				echo $expedition->getNomUrl(1);
				echo '</li>';
				echo '</ul>';
			}

			foreach ($TOfId as &$stdClass)
			{
				if ($TObjectLoaded['OF'][$stdClass->fk_assetOf]) $of = $TObjectLoaded['OF'][$stdClass->fk_assetOf];
				else
				{
					$of = new TAssetOF;
					$of->load($PDOdb, $stdClass->fk_assetOf, false);
					$TObjectLoaded['OF'][$of->getId()] = $of;
				}

				echo '<ul>';
				echo '<li>';
				echo $of->getNomUrl(1);
				if (!empty($of->fk_commande))
				{
					if (!empty($TObjectLoaded['commande'][$of->fk_commande])) $commande = $TObjectLoaded['commande'][$of->fk_commande];
					else
					{
						$commande = new Commande($db);
						$commande->fetch($of->fk_commande);
						$TObjectLoaded['commande'][$commande->id] = $commande;
					}

					echo '<br />'.img_picto('', '1downarrow.png').'<br />'.$commande->getNomUrl(1);
				}
				echo '</li>';
				echo '</ul>';
			}

			echo '</li>';
			echo '</ul>';
		}
	}


	/**
	 * Reload assetatm module menus (used when modifying asset types)
	 */
	function reload_menus()
	{
		global $db;

		dol_include_once('/assetatm/core/modules/modAssetatm.class.php');

		$moduleDesc = new modAssetatm($db);
		$moduleDesc->delete_menus();
		$moduleDesc->insert_menus();
	}
