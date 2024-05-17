
<div class="fichecenter"> <!-- begin div class="fiche" -->
	<div class="fichehalfleft">
		<table width="100%" class="border tableforfield">
			<tr><td width="20%">Numéro Lot</td><td>[assetlot.lot_number;strconv=no;protect=no]</td></tr>
		</table>

		<p align="center">
			[onshow;block=begin;when [view.mode]=='new']
			<input type="submit" value="Enregistrer" name="save" class="button">
			[onshow;block=end]
		</p>
	</div>
</div>


<div class="pagination paginationref" style="padding: 0px;">
	<ul class="right">
		<li class="noborder litext">
			<a href="[url.backToList]" >[langs.transnoentities(BackToList)]</a>
		</li>
	</ul>
</div>
