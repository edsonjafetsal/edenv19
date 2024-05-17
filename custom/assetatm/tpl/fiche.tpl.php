			<table width="100%" class="noborder">
				[onshow;block=begin;when [view.mode]=='new']
					<tr class="oddeven">
						<td style="width:20%">Type</td>
						<td>[assetNew.typeCombo;strconv=no;protect=no]</td>
						<td>[assetNew.validerType;strconv=no;protect=no]</td>
					</tr>
				[onshow;block=end]
				[onshow;block=begin;when [view.mode]!='new']

				<script type="text/javascript">
					$(document).ready(function(){
						/*
						if($("#gestion_stock").find("option:selected").val() == 'UNIT'){
							$("#measuring_units").parent().parent().hide();
							$("#contenance_value").parent().parent().hide();
							$("#contenancereel_value").parent().parent().hide();
							$("#point_chute").parent().parent().hide();

							$("#contenance_value").val('1');
							$("#contenancereel_value").val('1');
							$("#point_chute").val('0');
				 		}

						$("#gestion_stock").change(function(){
					 		if($(this).find("option:selected").val() == 'UNIT'){
								$("#measuring_units").parent().parent().hide();
								$("#contenance_value").parent().parent().hide();
								$("#contenancereel_value").parent().parent().hide();
								$("#point_chute").parent().parent().hide();

								$("#contenance_value").val('1');
								$("#contenancereel_value").val('1');
								$("#point_chute").val('0');
					 		}
					 		else{
					 			$("#measuring_units").parent().parent().show();
								$("#contenance_value").parent().parent().show();
								$("#contenancereel_value").parent().parent().show();
								$("#point_chute").parent().parent().show();
					 		}
					 	})
					 	*/

						$("#lot_number").autocomplete({
							source: "script/interface.php?get=autocomplete&json=1&fieldcode=lot_number",
							minLength : 1
						});
					});
				</script>
				<tr class="oddeven"><td width="20%" class="fieldrequired">[view.langs.transnoentities(AssetAtmSerialNumber)]</td><td>[asset.serial_number;strconv=no]</td>[asset.typehidden;strconv=no;protect=no]</tr>
				[onshow;block=begin;when [view.use_lot_in_of]=='1']
					<tr class="oddeven"><td>Numéro Lot</td><td>[asset.lot_number;strconv=no;protect=no]</td></tr>
				[onshow;block=end]
				<tr class="oddeven"><td class="fieldrequired">Product</td><td>[asset.produit;strconv=no]</td></tr>
				<tr class="oddeven"><td>Warehouse</td><td>[asset.entrepot;strconv=no]</td></tr>
				<tr class="oddeven"><td>Vendor</td><td>[asset.societe;strconv=no]</td></tr>
				[onshow;block=begin;when [view.champ_prix_achat]=='1']<tr class="oddeven"><td>Purchase value</td><td>[asset.prix_achat;strconv=no]</td></tr>[onshow;block=end]
				<tr class="oddeven"><td>Location</td><td>[asset.societe_localisation;strconv=no]</td></tr>
				[onshow;block=begin;when [view.champs_production]=='1']<tr class="oddeven"><td>Gestion du stock</td><td>[asset.gestion_stock;strconv=no]</td></tr>[onshow;block=end]

				[onshow;block=begin;when [view.champs_production]=='1']<tr class="oddeven"><td>Maximum capacity</td><td>[asset.contenance_value;strconv=no] [asset.contenance_units;strconv=no]</td></tr>[onshow;block=end]
				[onshow;block=begin;when [view.champs_production]=='1']<tr class="oddeven"><td>Current Capacity</td><td>[asset.contenancereel_value;strconv=no] [asset.contenancereel_units;strconv=no]</td></tr>[onshow;block=end]
				[onshow;block=begin;when [view.champs_production]=='1']<tr class="oddeven"><td>Drop point</td><td>[asset.point_chute;strconv=no]</td></tr>[onshow;block=end]


				[onshow;block=begin;when [view.ASSET_SHOW_DLUO]=='1']<tr class="oddeven"><td>BBD</td><td>[asset.dluo;strconv=no;]</td></tr>[onshow;block=end]
				[onshow;block=begin;when [view.champs_production]=='1']<tr class="oddeven"><td>Status</td><td>[asset.status;strconv=no]</td></tr>[onshow;block=end]
				[onshow;block=begin;when [view.champs_production]=='1']<tr class="oddeven"><td>Reusability</td><td>[asset.reutilisable;strconv=no]</td></tr>[onshow;block=end]
				<tr class="oddeven">
					<td style="width:20%" [assetField.obligatoire;strconv=no;protect=no]>[assetField.libelle;block=tr;strconv=no;protect=no] </td>
					<td>[assetField.valeur;strconv=no;protect=no] </td>
				</tr>
				[onshow;block=end]
			</table>

[onshow;block=begin;when [view.mode]=='view']
		</div>

		<script type="text/javascript">
			$(document).ready(function(){
				$('#fk_entrepot').change(function(){
					$('#lend-return').attr('onclick',"document.location.href='?action=retour_pret&id=[asset.id]&fk_entrepot="+$(this).val()+"'");
				})

				$('a#action-delete').click(function()
				{
					// return window.confirm('Êtes-vous sûr(e) de vouloir supprimer cet équipement ?');
				});

				$('a#action-clone').click(function()
				{
					// return window.confirm('Êtes-vous sûr(e) de vouloir cloner cet équipement ?');
				});
			});
		</script>

		<div class="tabsAction">
			<a href="?id=[asset.id]&action=edit" id="action-edit" class="butAction">Modify</a>
			[onshow;block=begin;when [view.clinomadic]=='view']
				[view.entrepot; strconv=no] <a href="?id=[asset.id]&action=retour_pret&fk_entrepot=1" id="lend-return" class="butAction">Loan return</a>
			[onshow;block=end]
			[onshow;block=begin;when [asset.canCreate]=='1']
			<a href="?id=[asset.id]&action=ask_clone" id="action-clone" class="butAction">Clone</a>
			[onshow;block=end]
			<a href="?id=[asset.id]&action=ask_delete" id="action-delete" class="butActionDelete">Delete</a>
		</div>
		<!--<table border="0" width="100%" summary="" style="margin-bottom: 2px;" class="notopnoleftnoright">
			<tr><td valign="middle" class="nobordernopadding"><div class="titre">Mouvements de stock</div></td></tr>
		</table> -->
		[view.liste;strconv=no]

		<div class="tabsAction">
			<a href="?id=[asset.id]&action=stock" id="action-mvt-stock" class="butAction">New Stock Movement</a>
		</div>
[onshow;block=end]

[onshow;block=begin;when [view.mode]=='stock']
		<div class="border" style="margin-top: 25px;">
			<table width="100%" class="border">
				<tr><td>Movement Type </td><td>[stock.type_mvt;strconv=no]</td></tr>
				<tr><td>Quantity</td><td>[stock.qty;strconv=no][asset.contenancereel_units;strconv=no]</td></tr>
				<tr><td>Unit Price (Excl tax)</td><td>[stock.subprice;strconv=no]</td></tr>
				<tr><td>Comments</td><td>[stock.commentaire_mvt;strconv=no]</td></tr>
			</table>
		</div>
[onshow;block=end]

[onshow;block=begin;when [view.mode]!='view']

		<p align="center">
			[onshow;block=begin;when [view.mode]!='add']
				<input type="submit" value="Create" name="save" class="button">
				&nbsp; &nbsp; <input type="button" value="Cancel" name="cancel" class="button" onclick="document.location.href='?id=[asset.id]'">
			[onshow;block=end]
		</p>
[onshow;block=end]
