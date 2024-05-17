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
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

// Load translation files required by the page
$langs->loadLangs(array("jsreports@jsreports"));

$action = GETPOST('action', 'aZ09');


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
$extrajs = '';
$extracss = '';
$extrajs = array(
	'/custom/hmv/inc/datatables/js/jquery.dataTables.min.js',
	'/custom/hmv/inc/datatables/buttons/js/dataTables.buttons.min.js',
	'/custom/hmv/inc/datatables/buttons/js/buttons.colVis.min.js',
	'/custom/hmv/inc/datatables/buttons/js/buttons.html5.min.js',
	'peticion.js'
);


$extracss = array(
	'/custom/hmv/inc/datatables/css/jquery.dataTables.min.css',
	'/custom/hmv/inc/datatables/buttons/css/buttons.dataTables.min.css',
	'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css'
);

llxHeader('', $langs->trans("JsreportsArea"), '', '', '', '', $extrajs, $extracss);



print load_fiche_titre($langs->trans("JsreportsArea"), '', 'jsreports.png@jsreports');

print '<div class="fichecenter"><div class="fichethirdleft">';

print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


$NBMAX = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;
$max = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;

print '</div></div></div>';

require_once DOL_DOCUMENT_ROOT . '/custom/jsreports/core/operations/operations.php';
//require_once DOL_DOCUMENT_ROOT . '/custom/jsreports/js/jsreports.js.php';

//$filtroscustomer= ' WHERE   client = 1 and fournisseur = 0';
$filtroscustomer= ' WHERE    fournisseur = 0';
?>


	<script>
		$(document).ready(function() {
			$('.js-example-basic-single').select2();
		});
	</script>
	<table>
		<tbody>
		<tr>

			<td>

				<DIV class="input-group mb-3" style="height:40px; width:600px">
					<label class="input-group-text" for="inputGroupSelect01">Order Date&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
						<input type="date" style="height:40px; width:220px" name= "date1" id="date1" class="form-control"  value=   ' . date("Y-m-d") . '>
					&nbsp;&nbsp;
						<input type="date" style="height:40px; width:220px" name= "date2" id="date2" class="form-control" value=   ' . date("Y-m-d") . '>

				</div>

			</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>

			<td>
				<DIV class="input-group mb-3" style="height:40px; width:600px">
					<label class="input-group-text" for="inputGroupSelect01">Delivery Date:</label>
					<input type="date" style="height:40px; width:220px" name= "datestart" id="datestart" class="form-control"  value=   ' . date("Y-m-d") . '>
					&nbsp;&nbsp;&nbsp;
					<input type="date" style="height:40px; width:220px" name= "dateend" id="dateend" class="form-control" value=   ' . date("Y-m-d") . '>
				</div>

			</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<?php
		$cos =getCO();
		$options=getOptionsCO($cos);
		$test=0;

		?>

		<tr>

			<td>
				<div class="input-group mb-3">
					<div class="input-group-prepend">
						<label class="input-group-text" for="inputGroupSelect01">Sales Order &nbsp;&nbsp;&nbsp;</label>
					</div>&nbsp;
					<select class="custom-select js-example-basic-single md-3" id="SalesOrder" name= "SalesOrder"   style="width: auto;" >
						<option selected>Select an option...</option>
					<?php print $options;?>
					</select>
				</div>

			</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
<!--		<tr>-->
<!---->
<!--			<td>&nbsp;Length:&nbsp;&nbsp;&nbsp;&nbsp; --><?php // print getSelect('Length1', '');?><!-- to --><?php // print getSelect('Length2', '');?><!-- </td>-->
<!--			<td></td>-->
<!--			<td></td>-->
<!--			<td></td>-->
<!---->
<!---->
<!--		</tr>-->
<!--		<tr>-->
<!--			<td>Width:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;           &nbsp;--><?php // print getSelect('Width1', '');?><!-- to --><?php // print getSelect('Width2', '');?><!-- </td>-->
<!--			<td></td>-->
<!--			<td></td>-->
<!--			<td></td>-->
<!--			<td>&nbsp</td>-->
<!---->
<!---->
<!--		</tr>-->
<!---->
<!--		<tr>-->
<!---->
<!--			<td>&nbsp;Thick:    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; --><?php // print getSelect('Thickness1', '');?><!-- to --><?php // print getSelect('Thickness2', '');?><!--</td>-->
<!--			<td></td>-->
<!--			<td></td>-->
<!--			<td>&nbsp</td>-->
<!--			<td>&nbsp</td>-->
<!---->
<!--		</tr>-->
		<tr>

			<td>&nbsp;<input type="submit" class="button" value="submit" id="fechas" name="fechas" ><input type="submit" class="button" value=" Reset" id="reset" name="reset" ></td>
			<td>	</td>
			<td></td>
			<td>&nbsp</td>
			<td>&nbsp</td>
		</tr>
		</tbody>
	</table>
<!-- 	<div id="flotante" style="display:none;">CONTENIDO A OCULTAR/MOSTRAR</div> -->


	<section id="tabla_resultado1">
		<!-- AQUI SE DESPLEGARA NUESTRA TABLA DE CONSULTA -->
	</section>

	<section id="tabla_resultado">
		<!-- AQUI SE DESPLEGARA NUESTRA TABLA DE CONSULTA -->
	</section>




	<script type="text/javascript">
		//document.getElementById("select_societe").onchange= function() {reporte()};
		document.getElementById("fechas").onclick= function() {reporte()};
		//document.getElementById("generar").onclick= function() {redirigir()};
		document.getElementById("reset").onclick= function() {redirigir()};
		document.getElementById("generar").onclick= function() {generarbom2()};
		//document.getElementById("select_commande").onchange= function() {reporte()};
		//	document.getElementById("select_facture").onchange= function() {reporte()};
		//document.getElementById("select_product").onchange= function() {reporte()};
		//	document.getElementById("select_categorie").onchange= function() {reporte()};
		//document.getElementById("select_nomenclature").onchange= function() {reporte()};
		//document.getElementById("select_workstation").onchange= function() {reporte()};
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
			//let societe = $('#select_societe').val();
			let Width1 = $('#select_Width1').val();
			let Width2 = $('#select_Width2').val();
			let Thickness1 = $('#select_Thickness1').val();
			let Thickness2 = $('#select_Thickness2').val();
			let Length1 = $('#select_Length1').val();
			let Length2 = $('#select_Length2').val();
			let date = $('#date1').val();
			let date2 = $('#date2').val();
			let datestart = $('#datestart').val();
			let dateend = $('#dateend').val();
			let SalesOrder = $('#SalesOrder').val();
			let columns = [];
			$("input[type=checkbox]:checked").each(function(){
				columns.push($(this).attr('id'));
			});

			$.ajax({
				url  : 'core/operations/reporte.php',
				type : 'post',
				data: {Width1: Width1, Width2: Width2, Thickness1: Thickness1, Thickness2: Thickness2, Length2: Length2,
					Length1: Length1 ,date: date,datestart: datestart,dateend: dateend , date2: date2 , SalesOrder: SalesOrder },
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
				url  : 'core/operations/scheduling.php',
				type : 'post',
				data: {valoresCheck: arrayproductos },
			}).done(function(resultado1){
				$('#tabla_resultado1').html(resultado1);
				window.location.reload();
			})
		}



		function redirigir() {
			//window.location='../of/liste_of.php?search_status_of=VALID&idmenu=42&mainmenu=of&leftmenu=';
			window.location='foard.php';

		}

		function generarbom2() {
			let arrayproductos = [];
			let arraycommande = [];

			$("input[type=checkbox]:checked").each(function(){
				arrayproductos.push($(this).attr('id'));
			});
			$("input[type=checkbox]:checked").each(function(){
				arraycommande.push($(this).attr('val'));

			});
			$.ajax({
				url  : 'core/operations/reporte3.php',
				type : 'post',
				data: {arrayproductos: arrayproductos, arraycommande: arraycommande },
			}).done(function(resultado1){

				document.getElementById("tabla_resultado1").style.display ='none';
				$('#tabla_resultado').html(resultado1);
				//window.location.reload();
			})
		}


		//7293177205 usuario total
	</script>


<?php




// End of page
llxFooter();
$db->close();

