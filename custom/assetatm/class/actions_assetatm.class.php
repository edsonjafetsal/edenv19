<?php
class ActionsAssetatm
{
     /** Overloading the doActions function : replacing the parent's function with the one below
      *  @param      parameters  meta datas of the hook (context, etc...)
      *  @param      object             the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
      *  @param      action             current action (if set). Generally create or edit or null
      *  @return       void
      */

    function doActions($parameters, &$object, &$action, $hookmanager)
    {
    	global $langs, $db, $conf, $user;

		
        return 0;
    }

    function formObjectOptions($parameters, &$object, &$action, $hookmanager)
    {
      	global $langs,$db,$conf;
		$langs->load('assetatm@assetatm');
		/*echo '<pre>';
		print_r($parameters['context']);
		echo '</pre>';exit;*/
		if (in_array('pricesuppliercard',explode(':',$parameters['context']))) {
			?>
			<script type="text/javascript">
				$(document).ready(function(){
					$('tr.liste_titre').find('>td:last').before('<td class="liste_titre" align="right"><?php echo dol_escape_js($langs->trans('AssetCompoundProvided')); ?></td>');
				});
			</script>
			<?php
		}
	}

    function formEditProductOptions($parameters, &$object, &$action, $hookmanager)
    {
    	/*ini_set('dysplay_errors','On');
		error_reporting(E_ALL);*/
    	global $db,$langs,$conf;
		include_once(DOL_DOCUMENT_ROOT."/commande/class/commande.class.php");
		include_once(DOL_DOCUMENT_ROOT."/compta/facture/class/facture.class.php");

		/*echo '<pre>';
		print_r($parameters["line"]->rowid);
		echo '</pre>';exit;*/

		$TContext = explode(':',$parameters['context']);
		
		//Commandes et Factures
		if (in_array('ordercard',$TContext)
		|| in_array('propalcard',$TContext)
		|| in_array('invoicecard',$TContext))
		{
        	
        	?>
        	<script type="text/javascript">

			$(document).ready(function () {

				$('td.propaldet_extras_fk_asset select[name=options_fk_asset],td.commandedet_extras_fk_asset select[name=options_fk_asset],td.facturedet_extras_fk_asset select[name=options_fk_asset]').closest('tr').remove();

			});
		
        	</script>
        	
        	<?php
        	
        	$line = &$parameters['line'];
        	if(!empty($conf->global->USE_ASSET_IN_ORDER)) {
        		?>
				<script type="text/javascript">
					
						$('#span_lot').remove();
						
						var fk_product =$('#product_id').val();

						if(fk_product>0) {
							
							$('#product_id').closest('td').find('a').next().after('<div id="span_lot"><?php echo $langs->trans('Asset'); ?> : <select id="options_fk_asset" name="options_fk_asset" class="flat"><option value="0" selected="selected"><?php echo $langs->trans('AssetSelectAsset');?></option></select></div>');
								
							$.ajax({
								method: "get"
								,url: "<?php echo dol_buildpath('/assetatm/script/ajax.liste_asset.php', 1) ?>"
								,dataType: "json"
								,data: {
									fk_product: fk_product,
									fk_soc : <?php echo $object->socid; ?>
									}
								},"json").then(function(select){
									$combo = $('#options_fk_asset');
									
									if(select.length > 0){

										$combo.empty();
										$.each(select, function(i,option){
											if(select.length > 1){
												$combo.prepend('<option value="'+option.id+'">'+option.label+'</option>');
											}
											else{
												$combo.prepend('<option value="'+option.id+'" selected="selected">'+option.label+'</option>');
											}
										})
										$combo.prepend('<option value="0" selected="selected"><?php echo $langs->trans('AssetSelectAsset');?></option>');
									}
									else{
										$combo.empty();
										$combo.prepend('<option value="0" selected="selected"><?php echo $langs->trans('AssetSelectAsset');?></option>');
									}

									$combo.val(<?php echo (int)$line->array_options['options_fk_asset']; ?>);
									
								});

						}
					
				</script>
				<?php
			}

			$this->resprints='';
        }

        /*$this->results=array('myreturn'=>$myvalue);
        $this->resprints='';
 */
        return 0;
    }

	function formAddObjectLine ($parameters, &$object, &$action, $hookmanager) {

		global $db,$langs,$conf;

		/*echo '<pre>';
		print_r($parameters);
		echo '</pre>';*/

		include_once(DOL_DOCUMENT_ROOT."/commande/class/commande.class.php");
		include_once(DOL_DOCUMENT_ROOT."/compta/facture/class/facture.class.php");

		$TContext = explode(':',$parameters['context']);
		
		if (in_array('ordercard',$TContext) 
				|| in_array('propalcard',$TContext) 
				|| in_array('invoicecard',$TContext))
        {
        	
        	?>
        	<script type="text/javascript">

			$(document).ready(function () {

				$('select[name=options_fk_asset]').closest('tr').remove();

			});
		
        	
        	</script>
        	
        	<?php
        	
        	if(!empty($conf->global->USE_ASSET_IN_ORDER)) {
	        	?>
				<script type="text/javascript">
					$('#idprod').change( function(){

						$('#span_lot').remove();
						
						var fk_product = $('#idprod').val();

						if(fk_product>0) {
							$('#idprod').parent().parent().find(" > span:last").after('<span id="span_lot"> - <?php echo $langs->trans('Asset'); ?> : <select id="options_fk_asset" name="options_fk_asset" class="flat"><option value="0" selected="selected"><?php echo $langs->trans('AssetSelectAsset');?></option></select></span>');
								
							$.ajax({
								type: "POST"
								,url: "<?php echo dol_buildpath('/assetatm/script/ajax.liste_asset.php', 1) ?>"
								,dataType: "json"
								,data: {
									fk_product: fk_product,
									fk_soc : <?php echo $object->socid; ?>
									}
								},"json").then(function(select){
									$combo = $('#options_fk_asset');
									
									if(select.length > 0){

										$combo.empty();
										$.each(select, function(i,option){
											if(select.length > 1){
												$combo.prepend('<option value="'+option.id+'">'+option.label+'</option>');
											}
											else{
												$combo.prepend('<option value="'+option.id+'" selected="selected">'+option.label+'</option>');
											}
										})
										$combo.prepend('<option value="0" selected="selected"><?php echo $langs->trans('AssetSelectAsset');?></option>');
									}
									else{
										$combo.empty();
										$combo.prepend('<option value="0" selected="selected"><?php echo $langs->trans('AssetSelectAsset');?></option>');
									}
								});

						}
					});
					
					
				</script>
				<?php
			}
        }

		return 0;
	}

	function printObjectLine ($parameters, &$object, &$action, $hookmanager){

		global $db,$langs;

		/*echo '<pre>';
		print_r($object);
		echo '</pre>';exit;*/

		if(in_array('pricesuppliercard',explode(':',$parameters['context']))){

			$resql = $db->query('SELECT compose_fourni FROM '.MAIN_DB_PREFIX.'product_fournisseur_price WHERE rowid = '.(($object->product_fourn_price_id) ? $object->product_fourn_price_id : $parameters['id_pfp']) );

			$res = $db->fetch_object($resql);

			if($res){
				?>
				<script type="text/javascript">
					$(document).ready(function(){
						$('#row-<?php echo ($object->product_fourn_price_id) ? $object->product_fourn_price_id : $parameters['id_pfp']; ?>').find('>td:last').before('<td align="right"><?php echo ($res->compose_fourni) ? "Oui" : "Non" ; ?></td>');
					});
				</script>
				<?php
			}
		}


		return 0;
	}

	function formCreateThirdpartyOptions($parameters, &$object, &$action, $hookmanager){
		global $conf;

		if (in_array('pricesuppliercard',explode(':',$parameters['context'])) && ! empty($conf->of->enabled)) {
			dol_include_once("/core/class/html.form.class.php");

			$form = new Form($this->db);
			?>
			<tr id="newField">
				<td class="fieldrequired">Compound Provided</td>
				<td><?php print $form->selectarray('selectOuiNon', array(1=>"Yes",0=>"No"), $conf->global->ASSET_DEFAULT_COMPOSE_FOURNI); ?></td>
			</tr>
			<?php
        }

	}

	function formEditThirdpartyOptions ($parameters, &$object, &$action, $hookmanager){
		global $db, $conf;

		/*echo '<pre>';
		print_r($_REQUEST);
		echo '</pre>';exit;*/

		if (in_array('pricesuppliercard',explode(':',$parameters['context'])) && ! empty($conf->of->enabled)) {
			dol_include_once("/core/class/html.form.class.php");

			$resql = $db->query('SELECT compose_fourni FROM '.MAIN_DB_PREFIX.'product_fournisseur_price WHERE rowid = '.$_REQUEST['rowid']);
			$res = $db->fetch_object($resql);

			$form = new Form($db);
			?>
			<tr id="newField">
				<td class="fieldrequired">Composé fourni</td>
				<td><?php print $form->selectarray('selectOuiNon', array(1=>"Oui",0=>"Non"),$res->compose_fourni); ?></td>
			</tr>
			<?php
        }
	}



}
