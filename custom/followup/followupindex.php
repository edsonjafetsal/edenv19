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
 *	\file       followup/followupindex.php
 *	\ingroup    followup
 *	\brief      Home page of followup top menu
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

// Load translation files required by the page
$langs->loadLangs(array("followup@followup"));

$action = GETPOST('action', 'aZ09');


// Security check
//if (! $user->rights->followup->myobject->read) accessforbidden();
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0)
{
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

llxHeader('', $langs->trans("To Do's"), '', '', '', '', $extrajs, $extracss);

// print load_fiche_titre($langs->trans("FollowupArea"), '', 'followup.png@followup');
$title = $langs->trans("Weekly To Do's");
$textAfterTitle = 'To make filters work, reset first the report and then modify the filter.';
$combinedText = '<b>' . $title . '</b><br>' . $textAfterTitle.'</br>';

print load_fiche_titre($combinedText, '', 'followup.png@followup');

print '<div class="fichecenter"><div class="fichethirdleft">';


/* BEGIN MODULEBUILDER DRAFT MYOBJECT
// Draft MyObject
if (! empty($conf->followup->enabled) && $user->rights->followup->read)
{
	$langs->load("orders");

	$sql = "SELECT c.rowid, c.ref, c.ref_client, c.total_ht, c.tva as total_tva, c.total_ttc, s.rowid as socid, s.nom as name, s.client, s.canvas";
	$sql.= ", s.code_client";
	$sql.= " FROM ".MAIN_DB_PREFIX."commande as c";
	$sql.= ", ".MAIN_DB_PREFIX."societe as s";
	if (! $user->rights->societe->client->voir && ! $socid) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
	$sql.= " WHERE c.fk_soc = s.rowid";
	$sql.= " AND c.fk_statut = 0";
	$sql.= " AND c.entity IN (".getEntity('commande').")";
	if (! $user->rights->societe->client->voir && ! $socid) $sql.= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
	if ($socid)	$sql.= " AND c.fk_soc = ".$socid;

	$resql = $db->query($sql);
	if ($resql)
	{
		$total = 0;
		$num = $db->num_rows($resql);

		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<th colspan="3">'.$langs->trans("DraftMyObjects").($num?'<span class="badge marginleftonlyshort">'.$num.'</span>':'').'</th></tr>';

		$var = true;
		if ($num > 0)
		{
			$i = 0;
			while ($i < $num)
			{

				$obj = $db->fetch_object($resql);
				print '<tr class="oddeven"><td class="nowrap">';

				$myobjectstatic->id=$obj->rowid;
				$myobjectstatic->ref=$obj->ref;
				$myobjectstatic->ref_client=$obj->ref_client;
				$myobjectstatic->total_ht = $obj->total_ht;
				$myobjectstatic->total_tva = $obj->total_tva;
				$myobjectstatic->total_ttc = $obj->total_ttc;

				print $myobjectstatic->getNomUrl(1);
				print '</td>';
				print '<td class="nowrap">';
				print '</td>';
				print '<td class="right" class="nowrap">'.price($obj->total_ttc).'</td></tr>';
				$i++;
				$total += $obj->total_ttc;
			}
			if ($total>0)
			{

				print '<tr class="liste_total"><td>'.$langs->trans("Total").'</td><td colspan="2" class="right">'.price($total)."</td></tr>";
			}
		}
		else
		{

			print '<tr class="oddeven"><td colspan="3" class="opacitymedium">'.$langs->trans("NoOrder").'</td></tr>';
		}
		print "</table><br>";

		$db->free($resql);
	}
	else
	{
		dol_print_error($db);
	}
}
END MODULEBUILDER DRAFT MYOBJECT */


print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


$NBMAX = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;
$max = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;

/* BEGIN MODULEBUILDER LASTMODIFIED MYOBJECT
// Last modified myobject
if (! empty($conf->followup->enabled) && $user->rights->followup->read)
{
	$sql = "SELECT s.rowid, s.ref, s.label, s.date_creation, s.tms";
	$sql.= " FROM ".MAIN_DB_PREFIX."followup_myobject as s";
	//if (! $user->rights->societe->client->voir && ! $socid) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
	$sql.= " WHERE s.entity IN (".getEntity($myobjectstatic->element).")";
	//if (! $user->rights->societe->client->voir && ! $socid) $sql.= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
	//if ($socid)	$sql.= " AND s.rowid = $socid";
	$sql .= " ORDER BY s.tms DESC";
	$sql .= $db->plimit($max, 0);

	$resql = $db->query($sql);
	if ($resql)
	{
		$num = $db->num_rows($resql);
		$i = 0;

		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<th colspan="2">';
		print $langs->trans("BoxTitleLatestModifiedMyObjects", $max);
		print '</th>';
		print '<th class="right">'.$langs->trans("DateModificationShort").'</th>';
		print '</tr>';
		if ($num)
		{
			while ($i < $num)
			{
				$objp = $db->fetch_object($resql);

				$myobjectstatic->id=$objp->rowid;
				$myobjectstatic->ref=$objp->ref;
				$myobjectstatic->label=$objp->label;
				$myobjectstatic->status = $objp->status;

				print '<tr class="oddeven">';
				print '<td class="nowrap">'.$myobjectstatic->getNomUrl(1).'</td>';
				print '<td class="right nowrap">';
				print "</td>";
				print '<td class="right nowrap">'.dol_print_date($db->jdate($objp->tms), 'day')."</td>";
				print '</tr>';
				$i++;
			}

			$db->free($resql);
		} else {
			print '<tr class="oddeven"><td colspan="3" class="opacitymedium">'.$langs->trans("None").'</td></tr>';
		}
		print "</table><br>";
	}
}
*/


print '</div></div></div>';

//Inicia codigo edson

//Start ajax

	print '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
	print "<script type='text/javascript'>
	var objhtml;

    function Datos1() {
        // Mostrar la animación de carga
        $('#loading').show();

		countdown = $('#countdown').val();
        sales = $('#rep').val();
		cliente = $('#cliente').val();
		status = $('#status').val();
		followupdays = $('#followupdays').val();

		console.log('client',cliente);
		console.log('status',status);
		console.log('Follow up days',followupdays);
		console.log('Count Down',countdown);
        $.ajax({
            url: 'consultaFollowupActually.php',
            type: 'post',
            data: {
                sales: sales,cliente:cliente,status:status,followupdays:followupdays,countdown:countdown
            },
        }).done(function(resultado) {
            // Ocultar la animación de carga
            $('#loading').hide();

            // Mostrar los datos en la tabla
            $('#tabla_resultado').html('');
            $('#tabla_resultado1').html(resultado);

        });
    }

     function Datos() {
        // Mostrar la animación de carga
        $('#loading').show();
		document.getElementById('processcountdays').removeAttribute('disabled');
		document.getElementById('processcountdays').checked = true;

		countdown = $('#countdown').val();
        sales = $('#rep').val();
		cliente = $('#cliente').val();
		status = $('#status').val();
		followupdays = $('#followupdays').val();

		console.log('client',cliente);
		console.log('status',status);
		console.log('Follow up days',followupdays);
		console.log('Count Down',countdown);
        $.ajax({
            url: 'consultaFollowup.php',
            type: 'post',
            data: {
                sales: sales,cliente:cliente,status:status,followupdays:followupdays,countdown:countdown
            },
        }).done(function(resultado) {
            // Ocultar la animación de carga
            $('#loading').hide();

            // Mostrar los datos en la tabla
            $('#tabla_resultado1').html('');
            $('#tabla_resultado').html(resultado);

//            if (sales === '') {
//					// Si está vacío, marcamos el checkbox
//					$('#processcountdays').prop('checked', true);
//                    if ($('#processcountdays').is(':checked')) {
//
//						Datos(); // Llamar a la función si el checkbox está seleccionado
//					}
//				} else {
//					// Si no está vacío, desmarcamos el checkbox
////					$('#processcountdays').prop('checked', false);
//					$('#processcountdays').prop('checked', true);
//                    if ($('#processcountdays').is(':checked')) {
//
////						Datos1(); // Llamar a la función si el checkbox está seleccionado
//					}


        });
    }
    function resetbtnhvm(){
        $('#rep').val('');
		$('#cliente').val('');
		$('#status').val(-2);
		$('#followupdays').val('');
        $('#countdown').val('');


    }

	function processValueFollow(e){
    // Mostrar la animación de carga
    $('#loading').show();

    var id = e.id.substring(e.id.length-(e.id.length-'processordercheck'.length));
    var noteValue = document.getElementById('note_' + id).value;

    var processvalue = 0;
    var processtext = '';

    switch (e.dataset.value){
        case 'Yes':
            processvalueFollow = 0;
            e.dataset.value = 'No';
            break;
        case 'No':
            processvalueFollow = 1;
            e.dataset.value = 'Yes';
            break;
    }
    processtext = e.dataset.value;

    $.ajax({
        type: 'POST',
        url: '" . dol_buildpath('/custom/hmv/app/ajax_updateFollow.php', 1) . "',
        data: {valueid: id, noteValue: noteValue},
        success: function(data) {
            // Ocultar la animación de carga
            $('#loading').hide();

            $('#processedvalue'+id.toString()).html(processtext);
            $('#processedvalue'+id.toString()).html(processtext);
            if (processtext == 'No'){
                $('#processedvalue'+id.toString()).css({'color':'red'});
            } else {
                $('#processedvalue'+id.toString()).css({'color':'blue'});
            }
            Swal.fire({
                title: 'Warning message',
                text: '¡Process completed successfully!',
                icon: 'success',
                confirmButtonText: 'OK',
                allowOutsideClick: false
            }).then(function(result) {
                if (result.isConfirmed) {
                    // Aquí puedes redirigir a donde sea necesario
                    // window.location.href = urlConnection;
                }
            });
        }
    });
}


	   function processValueFollowNote(e){
		// Mostrar la animación de carga
        $('#loading').show();

		var id =e.id.substring(e.id.length-(e.id.length-'processordercheck'.length));
		var noteValue  = document.getElementById('note_' + id).value;
		var followupdate  = document.getElementById('followdate_' + id).innerText;


		console.log('Valor de note:', id);
		console.log('Contenido de la nota:', noteValue);
		console.log('Contenido de la fecha:', followupdate);
		 $.ajax({
					  type: 'POST',
					  url :  '" . dol_buildpath('/custom/hmv/app/ajax_updateFollowNote.php', 1) . "',
					  data: {valueid: id,noteValue:noteValue, followupdate:followupdate},
					  success: function(data)
					  {
						// Ocultar la animación de carga
            			$('#loading').hide();
					 Swal.fire({
                title: 'Warning message',
                text: '¡Note completed successfully!',
                icon: 'success',
                confirmButtonText: 'OK',
                allowOutsideClick: false
            }).then(function(result) {
                if (result.isConfirmed) {
                    // Aquí puedes redirigir a donde sea necesario
                    // window.location.href = urlConnection;
                }
            });
					  }
					});
	   }

	   function processValueFollowBack(e){
		// Mostrar la animación de carga
        $('#loading').show();

		var id =e.id.substring(e.id.length-(e.id.length-'processordercheck'.length));
		var noteValue  = document.getElementById('note_' + id).value;

		console.log('Valor de note:', id);
		console.log('Contenido de la nota:', noteValue);

		 $.ajax({
					  type: 'POST',
					  url :  '" . dol_buildpath('/custom/hmv/app/ajax_updateFollowBack.php', 1) . "',
					  data: {valueid: id,noteValue:noteValue},
					  success: function(data)
					  {
						// Ocultar la animación de carga
            			$('#loading').hide();
					  	 Swal.fire({
                title: 'Warning message',
                text: '¡Process completed successfully!',
                icon: 'success',
                confirmButtonText: 'OK',
                allowOutsideClick: false
            }).then(function(result) {
                if (result.isConfirmed) {
                    // Aquí puedes redirigir a donde sea necesario
                    // window.location.href = urlConnection;
                }
            });
					  }
					});
	   }

	   function processallorder() {
		var updateValues = [];  // Array para almacenar todos los valores

		$('.checkforselect').each(function (index, checkbox) {
			// Obtener el ID de cada registro
			$('#loading').show();
			var toupdate = checkbox.id.replace('processordercheck', '');
			updateValues.push(toupdate);  // Agregar el valor al array
		});

		console.log('ID del registro:', updateValues);

		// Llamar a la función processall con todos los valores usando AJAX
		$.ajax({
			type: 'POST',
			url: '" . dol_buildpath('/custom/hmv/app/ajax_updateFollowAll.php', 1) . "',
			data: { valueid: updateValues},
			success: function (data) {
				$('#loading').hide();
				// Manejar la respuesta del servidor aquí
				console.log('Respuesta del servidor:', data);

				// Reemplazar 'response' con 'data' para mostrar la notificación
				 Swal.fire({
                 title: 'Warning message',
                text: '¡All orders completed!',
                icon: 'success',
                confirmButtonText: 'OK',
                allowOutsideClick: false
            }).then(function(result) {
                if (result.isConfirmed) {
                    // Aquí puedes redirigir a donde sea necesario
                    // window.location.href = urlConnection;
                }
            });

			},
			error: function (error) {
				console.error('Error en la solicitud AJAX:', error);
				$.jnotify('Error updating', 'error');
			}
		});
	}

	function filterTableByCountDays() {
    var checkbox = document.getElementById('processcountdays');
    var tableRows = document.querySelectorAll('.tablageneral tbody tr');
    var checkboxesToHide = document.getElementsByClassName('checkboxTo');
	var tablaToHide = document.getElementsByClassName('tablaTo');

    if (checkbox && checkbox.checked) {
        tableRows.forEach(function (row) {
            var countDaysCell = row.querySelector('.countdays');
            if (countDaysCell && countDaysCell.style.color === 'blue') {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Ocultar checkboxes con la clase 'checkboxToHide'
        for (var i = 0; i < checkboxesToHide.length; i++) {
            checkboxesToHide[i].style.display = 'none';
        }
		for (var j = 0; j < tablaToHide.length; j++) {
            tablaToHide[j].style.display = 'none';
        }
    } else {
        tableRows.forEach(function (row) {
            row.style.display = '';

            // Mostrar checkboxes con la clase 'checkboxToHide'
            for (var i = 0; i < checkboxesToHide.length; i++) {
                checkboxesToHide[i].style.display = '';
            }
			for (var j = 0; j < tablaToHide.length; j++) {
                tablaToHide[j].style.display = '';
            }

        });
    }
}

function filterTableByCountDaysOverdueActivities() {
    var checkbox = document.getElementById('processcountdays');
    var tableRows = document.querySelectorAll('.tablageneral tbody tr');
    var checkboxesToHide = document.getElementsByClassName('checkboxToHide');
    var tablaToHide = document.getElementsByClassName('tablaToHide');
    var checkboxesTo = document.getElementsByClassName('checkboxTo');
    var tablaTo = document.getElementsByClassName('tablaTo');

    if (checkbox && checkbox.checked) {
        tableRows.forEach(function (row) {
            var countDaysCell = row.querySelector('.countdays');
            if (countDaysCell && countDaysCell.style.color === 'red') {
                row.style.display = ''; // Mostrar la fila
            } else {
                row.style.display = 'none'; // Ocultar la fila
            }
        });

        // Ocultar tablas con la clase 'tablaToHide'
        for (var j = 0; j < tablaToHide.length; j++) {
            tablaToHide[j].style.display = 'none';
        }

        // Ocultar checkboxes con la clase 'checkboxToHide'
        for (var i = 0; i < checkboxesToHide.length; i++) {
            checkboxesToHide[i].style.display = 'none';
        }

        // Mostrar checkboxes con la clase 'checkboxTo'
        for (var i = 0; i < checkboxesTo.length; i++) {
            checkboxesTo[i].style.display = '';
        }
        // Mostrar tablas con la clase 'tablaTo'
        for (var j = 0; j < tablaTo.length; j++) {
            tablaTo[j].style.display = '';
        }
    } else {
        tableRows.forEach(function (row) {
            var countDaysCell = row.querySelector('.countdays');
            if (countDaysCell && countDaysCell.style.color === 'blue') {
                row.style.display = ''; // Mostrar la fila
            } else {
                row.style.display = 'none'; // Ocultar la fila
            }
        });

        // Mostrar tablas con la clase 'tablaToHide'
        for (var j = 0; j < tablaToHide.length; j++) {
            tablaToHide[j].style.display = '';
        }

        // Mostrar checkboxes con la clase 'checkboxToHide'
        for (var i = 0; i < checkboxesToHide.length; i++) {
            checkboxesToHide[i].style.display = '';
        }

        // Ocultar checkboxes con la clase 'checkboxTo'
        for (var i = 0; i < checkboxesTo.length; i++) {
            checkboxesTo[i].style.display = 'none';
        }
        // Ocultar tablas con la clase 'tablaTo'
        for (var j = 0; j < tablaTo.length; j++) {
            tablaTo[j].style.display = 'none';
        }
    }
}

			</script>";

//End Ajax

// Agrega un div para mostrar la animación de carga
$root_url = "http://" . $_SERVER['HTTP_HOST'];
$nameDir = 'dolibarr-19.0.2/htdocs';
print '<div id="loading" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.8); z-index: 9999;">';
print '<img style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" src="' . $root_url . '/'.$nameDir.'/custom/followup/gif/loading1.gif" alt="Loading..." />';
print '</div>';
$test=0;


print '<table>';
	print '	<tr>';
	print '		<td>';

	print '		</td>';
	print '		<td>';


	print '		</td>';
	print '	<tr>';

	// select


	print '	<tr>';
	print '		<td>';
	$sql = "SELECT rowid, nom FROM llx_societe order by nom ASC ";
	$resql = $db->query($sql);
	print '<label class ="form-label">Customer</label>';

	print '<select class="form-select" style="height:40px; width:600px"  aria-label="Default select example" name = "cliente" id = "cliente">';
	print '<option value = "" selected>         </option>';
	foreach ($resql as $cliente) {
		print "<option value = " . $cliente['rowid'] . " >" . $cliente['nom'] . "</option>";
	}
	print '</select>';

	print '		</td>';

	print '		<td>&nbsp&nbsp&nbsp&nbsp</td>';
	print '		<td>';
	// $sql = "SELECT id, percent FROM llx_actioncomm";
	// $resql = $db->query($sql);
	// $i = 0;
	// $num = $db->num_rows($resql);
	// $b = 0;
	// foreach ($resql as $values1) {
	// 	foreach ($values1 as $value1) {
	// 		$prod[$b][] .= $value1;
	// 	}
	// 	$b = $b + 1;
	// }

	// print '<label class ="form-label"> Status								</label>';
	// print '<select class="form-select" style="height:40px; width:600px"  aria-label="Default select example" id = "product" name = "product">';
	// print '<option value = "" selected>         </option>';
	// for ($i = 0; $i < $num; $i++) {
	// 	print '<option value = "' . $prod[$i][0] . '" >' . $prod[$i][1] . '</option>';

	// }
	// print '</select>';

	$statusOptions = array(
		'-2' => $langs->trans(" "),
		// '-1' => $langs->trans("ActionNotApplicable"),
		'0' => $langs->trans("ActionsToDoShort"),
		'50' => $langs->trans("ActionRunningShort"),
		// '100' => $langs->trans("ActionDoneShort")
	);

	$selectedValue = -2; // Change this value to the desired default value
	print '<label class="form-label"> Status </label>';
	print '<select class="form-select" style="height:40px; width:600px" aria-label="Default select example" id="status" name="status">';
	foreach ($statusOptions as $value => $label) {
		$selected = ($selectedValue == $value) ? 'selected' : '';
		print '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
	}
	print '</select>';

	print '		<td>&nbsp&nbsp&nbsp&nbsp</td>';


print'	</table> ';

print'	</table> ';

	print'	<table> ';
	print '	<tr>';
	print '		<td>';
	$sql = "SELECT u.rowid as rowid,CONCAT(u.firstname,' ',u.lastname) as salesrep from llx_societe_commerciaux lsc
			left join llx_user u on u.rowid = lsc.rowid where  u.firstname is not null and u.statut = 1";
	$resql = $db->query($sql);
	print '<div>';
	print '<label class ="form-label"> Sales Representative</label>';
	print '<br>';
	print '<select class="form-select" style="height:40px; width:600px"   aria-label="Default select example" name = "rep" id = "rep">';
	print '<option value = "" selected>         </option>';
	foreach ($resql as $rep) {
		$selected = ($rep['rowid'] == $user->id) ? 'selected' : ''; // Verificar si el representante es el usuario en sesión
		print "<option value=" . $rep['rowid'] . " $selected>" . $rep['salesrep'] . "</option>";
	}
	print '</select>';
	print '		</td>';

	print '		<td>&nbsp&nbsp&nbsp&nbsp</td>';
	print '		<td>';
	$sql = "SELECT ae.rowid as rowid,ae.followupdays as followupdays  from llx_actioncomm_extrafields ae where ae.followupdays is not null ORDER BY ae.followupdays DESC";
	// $resql = $db->query($sql);
	$resql = 10;
	print '<label class ="form-label">Follow Up Days</label>';

	print '<select class="form-select" style="height:40px; width:600px" aria-label="Default select example" name = "followupdays" id = "followupdays">';
	print '<option value = "" selected>         </option>';
	$numFollowUp = array(0, 7, 14, 40, 60, 90, 180, 365);

	foreach ($numFollowUp as $value) {
		print "<option value='" . $value . "'>" . $value . "</option>";
	}
	print '</select>';

	print '		</td>';

print'	</table> ';

print'	<table> ';
	print '	<tr>';
	print '		<td>';
	print '<div>';
	print '<label class ="form-label">Due this week</label>';
	print '<br>';
	print '<select class="form-select" style="height:40px; width:600px" aria-label="Default select example" name="countdown" id="countdown">';
	print '<option value="" selected></option>';

	// Obtener la fecha actual
	$currentDate = strtotime('today');

	// Calcular la fecha del próximo domingo
	$nextSunday = strtotime('next Sunday', $currentDate);

	// Iterar desde hoy hasta el próximo domingo y agregar las opciones al select
	for ($i = $currentDate; $i <= $nextSunday; $i = strtotime('+1 day', $i)) {
		// Convertir la fecha a formato legible
		$dateFormatted = date('Y-m-d', $i);
		print '<option value="' . $dateFormatted . '">' . $dateFormatted . '</option>';
	}

	print '</select>';
	print '		</td>';

	print '		<td>&nbsp&nbsp&nbsp&nbsp</td>';
	print '		<td>';
	print '<br><a>
	<input class="form-check-input" type="checkbox" value="" id="processcountdays" style="-ms-transform: scale(2);-moz-transform: scale(2);-webkit-transform: scale(2);-o-transform: scale(2); background-color: #c4c6ca;" disabled>
	<label class="form-check-label" for="flexCheckDefault" style="padding-left: 10px;">Show overdue activities</label>
	</a>';

	print '		</td>';

print'	</table> ';

//botones
print ' <div class="col-lg-5">
<br>
        <a class="btn btn-secondary" aria-current="page" id="btn4" onclick="Datos()">GO</a>
        <a class="btn btn-secondary" aria-current="page" id="btnreset" onclick="resetbtnhvm()" style="
      margin-left: 20px;
">RESET</a>
        </div>';

print            '<section id="tabla_resultado">';
//		<!-- AQUI SE DESPLEGARA NUESTRA TABLA DE CONSULTA -->
print'</section>';

print            '<section id="tabla_resultado1">';
//		<!-- AQUI SE DESPLEGARA NUESTRA TABLA DE CONSULTA -->
print'</section>';

// End of page
llxFooter();
$db->close();
