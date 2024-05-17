        [view.head;strconv=no]
            	
			<table width="100%" class="border">
			
			<tr><td width="20%">[view.langs.transnoentitiesnoconv(Label)]</td><td>[assetType.libelle; strconv=no]</td></tr>
			<tr><td width="20%">[view.langs.transnoentitiesnoconv(Code)]</td><td>[assetType.code; strconv=no]</td></tr>
			<tr><td width="20%">[view.langs.transnoentitiesnoconv(AssetTypePointChute)]</td><td>[assetType.point_chute; strconv=no]</td></tr>
			<tr><td width="20%">[view.langs.transnoentitiesnoconv(AssetStockManagement)]</td><td>[assetType.gestion_stock; strconv=no]</td></tr>
			<tr><td width="20%">[view.langs.transnoentitiesnoconv(AssetTypeReusable)]</td><td>[assetType.reutilisable; strconv=no]</td></tr>
			<tr><td width="20%">[view.langs.transnoentitiesnoconv(AssetTypeMaxCapacity)]</td><td>[assetType.contenance_value;strconv=no] [assetType.contenance_units;strconv=no]</td></tr>
			<tr><td width="20%">[view.langs.transnoentitiesnoconv(AssetTypeDefaultCapacity)]</td><td>[assetType.contenancereel_value;strconv=no] [assetType.contenancereel_units;strconv=no]</td></tr>
			</table>
	
		</div> <!-- Fin tabBar -->
		
		
	<br>
	[assetType.titreChamps; strconv=no]	

	[onshow;block=begin;when [view.mode]=='edit']
	<div class="info">[view.langs.transnoentitiesnoconv(AssetTypeFieldOptionsTip); strconv=no]</div>
	[onshow;block=end]
	
<table id="sort" class="noborder" style="width:100%;">
	<!-- entête du tableau -->
	<thead>
		<tr class="liste_titre">
			[onshow;block=begin;when [view.mode]=='edit']
				<th class="liste_titre">[view.langs.transnoentitiesnoconv(AssetTypeFieldMove)]</th>
			[onshow;block=end]
			<th class="liste_titre">[view.langs.transnoentitiesnoconv(Code)]</th>
			<th class="liste_titre">[view.langs.transnoentitiesnoconv(Label)]</th>
			<th class="liste_titre">[view.langs.transnoentitiesnoconv(Type)]</th>
			<th class="liste_titre">[view.langs.transnoentitiesnoconv(AssetTypeFieldOptions)]</th>
			<th class="liste_titre">[view.langs.transnoentitiesnoconv(AssetTypeFieldRequired)]</th>
			<th class="liste_titre">[view.langs.transnoentitiesnoconv(AssetTypeFieldVisibleInList)]</th>
			<th class="liste_titre">[view.langs.transnoentitiesnoconv(AssetTypeFieldConcatenateToLabel)]</th>
			[onshow;block=begin;when [view.mode]=='edit']
				<th class="liste_titre">[view.langs.transnoentitiesnoconv(OFActions)]</th>
			[onshow;block=end]
		</tr>
	</thead>
	<!--<ul id="sortable" style="list-style-type:none;">-->
	<tbody>
	<!-- fields déjà existants -->
	<tr id="[assetField.indice;block=tr;stdconv=no;protect=no]" >
		[assetField.ordre;strconv=no;protect=no]
		[onshow;block=begin;when [view.mode]=='edit']
			<td class="sortable">[assetType.pictoMove; strconv=no]	</td>
		[onshow;block=end]
		<td>[assetField.code;strconv=no;protect=no]</td>
		<td>[assetField.libelle;strconv=no;protect=no]</td>
		<td>[assetField.type;strconv=no;protect=no]</td>
		<td>[assetField.options;strconv=no;protect=no]</td>
		<td>[assetField.obligatoire;strconv=no;protect=no]</td>
		<td>[assetField.inliste;strconv=no;protect=no]</td>
		<td>[assetField.inlibelle;strconv=no;protect=no]</td>
		[onshow;block=begin;when [view.mode]=='edit']
			<td>
				<img src="img/delete.png"  onclick="document.location.href='?id=[assetType.id]&idField=[assetField.id]&action=deleteField'">
			</td>
		[onshow;block=end]
	</tr>

	</tbody>

	<tfoot>
		[onshow;block=begin;when [view.mode]=='edit']
		<!-- Nouveau field-->
		<tr id="[newField.indice;strconv=no;protect=no]">
			[newField.ordre;strconv=no;protect=no]
			<td>[view.langs.transnoentitiesnoconv(New)]</td>
			<td>[newField.code;strconv=no;protect=no]</td>
			<td>[newField.libelle;strconv=no;protect=no]</td>
			<td>[newField.type;strconv=no;protect=no]</td>
			<td>[newField.options;strconv=no;protect=no]</td>
			<td>[newField.obligatoire;strconv=no;protect=no]</td>
			<td>[newField.inliste;strconv=no;protect=no]</td>
			<td>[newField.inlibelle;strconv=no;protect=no]</td>
			<td><input type="submit" value="[view.langs.transnoentitiesnoconv(Add)]" name="newField" class="button"></td>
		</tr>
		[onshow;block=end]
	</tfoot>
	<!--</ul>-->
</table>

[onshow;block=begin;when [view.mode]=='edit']
	<script>
	 	$(".sortable").css('cursor','pointer');
		$(function() {
			$("#sort tbody").sortable({
				//handle: fixHelper,
				stop: function(event, ui) {
					//alert($("#sortable").html())
					var result = $("#sort tbody").sortable('toArray');
					//alert(result);
					for (var i = 0; i< result.length; i++){
						$(".ordre"+result[i]).attr("value", i)
						}
				}
			});
		});	
	</script>
[onshow;block=end]

</div>
[onshow;block=begin;when [view.mode]!='edit']
	<div class="tabsAction">
		<a href="?id=[assetType.id]&action=edit" class="butAction">[view.langs.transnoentitiesnoconv(Modify)]</a>
	</div>
[onshow;block=end]	
[onshow;block=begin;when [view.mode]=='edit']
	<div class="tabsAction" style="text-align:center;">
		<input type="submit" value="[view.langs.transnoentitiesnoconv(Save)]" name="save" class="button"> 
		&nbsp; &nbsp; <input type="button" value="[view.langs.transnoentitiesnoconv(Cancel)]" name="cancel" class="button" onclick="document.location.href='?id=[assetType.id]'">
	</div>
[onshow;block=end]

<div style="clear:both"></div>


