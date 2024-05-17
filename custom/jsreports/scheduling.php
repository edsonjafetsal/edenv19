<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       jsreports/jsreportsindex.php
 *	\ingroup    jsreports
 *	\brief      Home page of jsreports top menu
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

global $db, $user, $langs, $conf;
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';

// Load translation files required by the page
$langs->loadLangs(array("jsreports@jsreports"));

$action = GETPOST('action', 'aZ09');
$valoresCheck = GETPOST('valoresCheck', 'aZ09');


// Security check
// if (! $user->rights->jsreports->myobject->read) {
// 	accessforbidden();
// }
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

$max = 5;
$now = dol_now();


/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("", $langs->trans("SCHEDULING"));

print load_fiche_titre($langs->trans("SCHEDULING"), '', 'jsreports.png@jsreports');

print '<div class="fichecenter"><div class="fichethirdleft">';

print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


$NBMAX = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;
$max = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;

print '</div></div></div>';

require_once DOL_DOCUMENT_ROOT . '/custom/jsreports/core/operations/operations.php';
//require_once DOL_DOCUMENT_ROOT . '/custom/jsreports/js/jsreports.js.php';

$filtroscustomer= ' WHERE   client = 1 and fournisseur = 0';
?>
<table>
<tbody>
<tr>
	<td>&nbspOrder Date:
	<td>&nbsp;<input type="date" style="height:20px; width:100px" name= "date1" id="date1" class="form-control" value= '.date("Y-m-d").'> &nbsp;&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;Delivery Date:
	<td>&nbsp;<input type="date" style="height:20px; width:100px" name= "datestart" id="datestart" class="form-control" value= '.date("Y-m-d").'> to <input type="date" style="height:20px; width:100px" name= "dateend" id="dateend" class="form-control" value= '.date("Y-m-d").'></td>
	<td>&nbsp;<input type="submit" class="button" value="submit" id="fechas" name="fechas" ></td>

	<td>&nbsp;

	</td>
	<td>&nbsp;</td>
</tr>
<tr>
<td>&nbsp;Third Party</td>
<td>&nbsp;<?php  print getSelect('societe', $filtroscustomer);?>  </td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
  <tr>
<td>&nbsp;Sales Orders</td>
<td>&nbsp;<?php  print getSelect('commande', ' where fk_statut in (1,2) ');?> </td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<!-- <tr>
<td>&nbsp;Invoice</td>
<td>&nbsp;<?php  print getSelect('facture', '');?> </td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>-->
<tr>
	<td>&nbsp;Finished Product</td>
	<td>&nbsp;<?php  print getSelect('product', '');?> </td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;Product Category</td>
	<td>&nbsp;<?php  print getSelect('categorie', '');?></td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;Workstation</td>
	<td>&nbsp;<?php  print getSelect('workstation', '');?></td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>

<tr>
	<td>&nbsp;Raw Material</td>
	<td>&nbsp;<?php  print getSelect('nomenclature', 'group by lp3.labe');?></td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<!--
<tr>
<td>&nbsp;User</td>
<td>&nbsp;<?php  print getSelect('user', '');?></td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>

<tr>
	<td>&nbsp;Type Product</td>
	<td>&nbsp;<?php  print getSelect('c_product_nature', '');?></td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
-->

<tr>
	<td COLSPAN="4">Hide Columns</td>
</tr>
<tr>
	<td>
	<input type="checkbox" id="2" 	value="1"  name="columns[]">CUSTOMER<br>
	<input type="checkbox" id="3" 	value="1"  name="columns[]">ORDER DATE<br>
	<input type="checkbox" id="4" 	value="1"  name="columns[]">ORDER REF<br>
	<input type="checkbox" id="5" 	value="1"  name="columns[]">DELIVERY DATE<br>
	<input type="checkbox" id="6" 	value="1" name="columns[]">ORDER MARGIN <br>
	<input type="checkbox" id="7" 	value="1"  name="columns[]">FINISHED PRODUCT<br>
</td>
<td>
	<input type="checkbox" id="8" 	value="1"  name="columns[]">QTY REQUESTED"<br>
	<input type="checkbox" id="9" 	value="1" name="columns[]">FP UOM<br>
	<input type="checkbox" id="10" 	value="1" name="columns[]">FP STOCK<br>
	<input type="checkbox" id="11" 	value="1"  name="columns[]">BOM<br>
	<input type="checkbox" id="12" 	value="1" name="columns[]">RAW MATERIAL<br>
	<input type="checkbox" id="13" 	value="1" name="columns[]">RM BOM QTY<br>
	<input type="checkbox" id="15" 	value="1" name="columns[]">QTY NEEDED<br>

</td>
<td>
	<input type="checkbox" id="16" 	value="1" name="columns[]">RM PRODUCTION UOM <br>
	<input type="checkbox" id="17" 	value="1" name="columns[]">RM STOCK <br>
	<input type="checkbox" id="18" 	value="1" name="columns[]">RM BUYING UOM <br>
	<input type="checkbox" id="19" 	value="1" name="columns[]">RM LEAD TIME <br>
	<input type="checkbox" id="20" 	value="1" name="columns[]">SUBASSEMBLY STOCK<br>
	<input type="checkbox" id="21" 	value="1" name="columns[]">SUBASSEMBLY RM<br>
	<input type="checkbox" id="22" 	value="1" name="columns[]">SA UOM <br>

</td>
<td>
	<input type="checkbox" id="23" 	value="1" name="columns[]">WORK STATION <br>
	<input type="checkbox" id="24" 	value="1" name="columns[]">RANK<br>
</td>
<td>



</td>
</tr>
<tr>
<td>&nbsp;</td>
<td> </td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr>

<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;	<input type="submit" class="button" value=" SCHEDULING" id="generar" name="generar" style=" display: none"></td>
</tr>


</tbody>
</table>





	<div id="flotante" style="display:none;">CONTENIDO A OCULTAR/MOSTRAR</div>


<section id="tabla_resultado1">
		<!-- AQUI SE DESPLEGARA NUESTRA TABLA DE CONSULTA -->
</section>
	<section id="tabla_resultado">
		<!-- AQUI SE DESPLEGARA NUESTRA TABLA DE CONSULTA -->
</section>




	<script type="text/javascript">
		document.getElementById("select_societe").onchange= function() {reporte()};
		document.getElementById("fechas").onclick= function() {reporte()};
		document.getElementById("generar").onclick= function() {generarbom()};
		document.getElementById("select_commande").onchange= function() {reporte()};
	//	document.getElementById("select_facture").onchange= function() {reporte()};
		document.getElementById("select_product").onchange= function() {reporte()};
		document.getElementById("select_categorie").onchange= function() {reporte()};
		document.getElementById("select_nomenclature").onchange= function() {reporte()};
		document.getElementById("select_workstation").onchange= function() {reporte()};
	//	document.getElementById("select_user").onchange= function() {reporte()};
	//	document.getElementById("select_c_product_nature").onchange= function() {reporte()};
		//document.getElementById(".date1").onchange= function() {reporte()};
		//document.getElementById(".datestart").onchange= function() {reporte()};
		//document.getElementById("#dateend").onchange= function() {reporte()};



		function mostrar() {
			div = document.getElementById('flotante');
			div.style.display = '';
		}

		function cerrar() {
			div = document.getElementById('flotante');
			div.style.display = 'none';
		}

		function reporte() {
			let societe = $('#select_societe').val();
			let commande = $('#select_commande').val();
			let facture = $('#select_facture').val();
			let product = $('#select_product').val();
			let categorie = $('#select_categorie').val();
			let workstation = $('#select_workstation').val();
			let nomenclature = $('#select_nomenclature').val();
			let user = $('#select_user').val();
			let c_product_nature = $('#select_c_product_nature').val();
			let date = $('#date1').val();
			let datestart = $('#datestart').val();
			let dateend = $('#dateend').val();
			let columns = [];
			$("input[type=checkbox]:checked").each(function(){
				columns.push($(this).attr('id'));
			});

			$.ajax({
				url  : 'core/operations/reporte.php',
				type : 'post',
				data: {societe: societe,commande: commande,facture: facture,product: product,categorie: categorie,
					user: user,c_product_nature: c_product_nature,date: date,datestart: datestart,dateend: dateend ,
					nomenclature: nomenclature ,workstation: workstation, columns: columns },
			}).done(function(resultado){
				$('#tabla_resultado').html(resultado);
				document.getElementById("generar").style.display = "block";
			})
		}


		function generarbom() {
			let arrayproductos = [];

			$("input[type=checkbox]:checked").each(function(){
				arrayproductos.push($(this).attr('id'));
			});
			$.ajax({
				url  : 'core/operations/generarbom.php',
				type : 'post',
				data: {valoresCheck: arrayproductos },
			}).done(function(resultado1){
				$('#tabla_resultado1').html(resultado1);
				window.location.reload();
			})
		}






	</script>


<?php




// End of page
llxFooter();
$db->close();
