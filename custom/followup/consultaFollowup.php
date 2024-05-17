<?php
/////// CONEXIÓN A LA BASE DE DATOS /////////
///
///


header("Expires: Fri, 14 Mar 1980 20:53:00 GMT"); //la pagina expira en fecha pasada
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); //ultima actualizacion ahora cuando la cargamos
header("Cache-Control: no-cache, must-revalidate"); //no guardar en CACHE
header("Pragma: no-cache"); //PARANOIA, NO GUARDAR EN CACHE


// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");


global $db, $conf;


if (isset($_POST['sales'])) $sales = $_POST['sales'];
if (isset($_POST['cliente'])) $sales = $_POST['cliente'];
if (isset($_POST['status'])) $sales = $_POST['status'];
if (isset($_POST['followupdays'])) $sales = $_POST['followupdays'];



//////////////// VALORES INICIALES ///////////////////////
$tabla = "";
/*
 * CABECERAS
 * */
$query1 = "select a.id as id,sop.rowid as idcontact,a.percent AS status , a.datep,u.rowid as rep,  CONCAT(u.firstname,' ',u.lastname) as salesrep,soc.rowid as idcustomer, soc.nom as customer,  concat(sop.firstname,' ',sop.lastname) as contact  , a.label , CONCAT(pr.ref,' ',pr.ref_client) AS ref , pr.rowid AS prid, soc.rowid AS cid, ae.followupdays as FollowUpDays, a.fk_contact AS conid, pr.total AS amount, sc.email, sc.phone, sc.phone_mobile ";
$query1 .= "from llx_actioncomm as a ";
$query1 .= "left join  llx_user as u on u.rowid = a.fk_user_author ";
$query1 .= "left  join llx_societe as soc on soc.rowid = a.fk_soc ";
$query1 .= "left join llx_socpeople as sop on sop.rowid = a.fk_contact ";
$query1 .= "left join llx_propal as pr on pr.rowid = a.fk_element and a.elementtype = 'propal' ";
$query1 .= "left join llx_actioncomm_extrafields as ae on ae.fk_object = a.id ";
$query1 .= "left join llx_element_contact as ec on pr.rowid = ec.element_id and ec.fk_c_type_contact in (41) ";
$query1 .= "left join llx_socpeople as sc on a.fk_contact = sc.rowid ";
$query1 .= "where a.percent not in (-1,100) ";
$query1 .= " AND a.datep >= CURDATE() - INTERVAL WEEKDAY(CURDATE()) DAY ";
$query1 .= " AND a.datep < CURDATE() + INTERVAL 7 - WEEKDAY(CURDATE()) DAY ";
//$query1 .= " AND a.datep <= CURDATE() ";
if (isset($_POST['sales']) and $_POST['sales'] != '') {
	$query1 .= "  AND u.rowid = " . $_POST['sales'];
}
if (isset($_POST['cliente']) and $_POST['cliente'] != '') {
	$query1 .= "  AND soc.rowid = " . $_POST['cliente'];
}
if (isset($_POST['followupdays']) and $_POST['followupdays'] != '') {

	if ($_POST['followupdays'] == 0) {
		$query1 .= "  AND ae.followupdays is null ";
	}else{
		$query1 .= "  AND ae.followupdays = " . $_POST['followupdays'];
	}
}
if (isset($_POST['status']) and $_POST['status'] != '') {
	if ($_POST['status'] == -2) {
        // Si el status es -2, cambia a un valor vacío
        $query1 .= " ";
    } else {
        // De lo contrario, utiliza el valor proporcionado
        $query1 .= " AND a.percent = " . $_POST['status'];
    }
}
if (isset($_POST['countdown']) and $_POST['countdown'] != '') {
//	print "<script type='text/javascript'>
//	$(document).ready(function() {
//		document.getElementById('processcountdays').checked = false;
//		if (!$('#processcountdays').is(':checked')) {
//		filterTableByCountDays(); // Llamar a la función si el checkbox NO está seleccionado
//	}
//	});
//
//
//	</script>";
	$query1 .= "  AND a.datep like '%" . $_POST['countdown']."%'";
}
// $query1 .= "	GROUP BY a.id;";

$cabecerasCO = $db->query($query1);

$num = $cabecerasCO->num_rows;
print "
<link href='https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.8.0/sweetalert2.min.css' rel='stylesheet' />
  <script src='https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js'></script>
  <script src='https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.8.0/sweetalert2.min.js'></script>

  <script>
function showSweetAlert() {
	swal({
		title: 'Mark all events as complete?',
		text: '',
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Yes, I´m sure for Confirm',
		cancelButtonText: 'Cancel'
	  }).then((result) => {
        if (result.value) {
            // Si el usuario hace clic en 'Sí, estoy seguro', llamar a la función processallorder
            processallorder();
			$.jnotify('Successfully Updated', 'ok');
        }
    });
}

function showSweetAlertCount() {
	swal({
		title: 'Mark all event as completed?',
		text: '',
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Yes, I´m sure',
		cancelButtonText: 'Cancel'
	  }).then((result) => {
        if (result.value) {
            // Si el usuario hace clic en 'Sí, estoy seguro', llamar a la función processallorder

			$.jnotify('Successfully Updated', 'ok');
        }
    });
}

// Asociar la función showSweetAlert al evento de clic del checkbox
$('#processallordercheck').click(function() {
    showSweetAlert();
});


$('#processcountdays').click(function() {
    if ($(this).is(':checked')) {
        Datos();
    } else {
        Datos1();
    }
});


//$('#processcountdays').prop('checked', false);
//if (!$('#processcountdays').is(':checked')) {
//    filterTableByCountDays();
//}

</script>";
$processall = '<br><div class="form-check" style="float: left"><input class="form-check-input" type="checkbox" value=""  id="processallordercheck" style="-ms-transform: scale(2);-moz-transform: scale(2);-webkit-transform: scale(2);-o-transform: scale(2); background-color: #c4c6ca; "> <label class="form-check-label" for="flexCheckDefault" style="padding-left: 10px;">All events complete</label></div><br>';


if ($num > 0) {
	$checkedprocess = $CABECERACO['status'] == 'Yes' ? 'checked' : 'unchecked';
	$estiloprocessed = $CABECERACO['status'] == 'Yes' ? 'style="color:blue;font-weight: bold;"' : 'style="color:red;font-weight: bold;"';

	print '<br><b>Total Register:' . $num . ' </b><br>';
	$numline = 1;
	while ($CABECERACO = $cabecerasCO->fetch_assoc()) {
		//print '<pre>product ';print_r($query1);print '</pre>';

		// if ($CABECERACO['status'] == 100) {
		// $tabla .=

			// '<table class="tablacabecera">

		// </table>
		// <br>
		// <div class="form-check">
		// <br>

		// <input class="form-check-input checkforselect" type="checkbox" ' . $checkedprocess . ' id="processordercheck' . $CABECERACO['id'] . '" data-value="' . $CABECERACO['prc'] . '" style="-ms-transform: scale(2);-moz-transform: scale(2);-webkit-transform: scale(2);-o-transform: scale(2); background-color: #c4c6ca; " onclick="processValueFollowBack(this)"> <label class="form-check-label" for="flexCheckDefault" style="padding-left: 10px;">Back To Do</label></div>';                //CABECERA COMMANDE  Y FECHA

		// }else{

		// }
		/*
		 * INICIO  FILTRADO X   CO
		 * */
		//$COMMANDE = $CABECERACO['co'];
		// Define las opciones del campo de selección (puedes personalizar estas opciones según tus necesidades)
		$statusOptions = array(
			'-1' => $langs->trans("ActionNotApplicable"),
			'0' => $langs->trans("ActionsToDoShort"),
			'50' => $langs->trans("ActionRunningShort"),
			'100' => $langs->trans("ActionDoneShort")
		);
		$COMMANDE = $CABECERACO["id"];
		$test = 0;

		if (!function_exists('getStatusText')) {
			function getStatusText($status, $statusOptions)
			{
				return isset($statusOptions[$status]) ? $statusOptions[$status] : '';
			}
		}

		$query2 = "select a.id as id,sop.rowid as idcontact,a.percent AS status, a.datep as followdate,  CONCAT(u.firstname,' ',u.lastname) as salesrep,soc.rowid as idcustomer, soc.nom as customer,  concat(sop.firstname,' ',sop.lastname) as contact  , a.label as label , CONCAT(pr.ref,' ',pr.ref_client) AS ref , pr.rowid AS prid, soc.rowid AS cid, ae.followupdays as FollowUpDays, a.fk_contact AS conid, pr.total AS amount, sc.email as email, sc.phone as phone, sc.phone_mobile as phonem ";
		$query2 .= "from llx_actioncomm as a ";
		$query2 .= "left join  llx_user as u on u.rowid = a.fk_user_author ";
		$query2 .= "left  join llx_societe as soc on soc.rowid = a.fk_soc ";
		$query2 .= "left join llx_socpeople as sop on sop.rowid = a.fk_contact ";
		$query2 .= "left join llx_propal as pr on pr.rowid = a.fk_element and a.elementtype = 'propal' ";
		$query2 .= "left join llx_actioncomm_extrafields as ae on ae.fk_object = a.id ";
		$query2 .= "left join llx_element_contact as ec on pr.rowid = ec.element_id and ec.fk_c_type_contact in (41) ";
		$query2 .= "left join llx_socpeople as sc on a.fk_contact = sc.rowid ";
		$query2 .= "where a.percent not in (-1,100) ";
		$query2 .= " AND a.datep >= CURDATE() - INTERVAL WEEKDAY(CURDATE()) DAY ";
		$query2 .= " AND a.datep < CURDATE() + INTERVAL 7 - WEEKDAY(CURDATE()) DAY ";
//		$query2 .= " AND a.datep <= CURDATE()  "; //registros antes de dia actual
//		$query2 .= " ";
		$query2 .= " AND a.id ='" . $COMMANDE . "'";
		// $query2 .=" GROUP BY a.id;";

		$buscarAlumnos2 = $db->query($query2);

		while ($filaAlumnos2 = $buscarAlumnos2->fetch_assoc()) {

			$root_url = "http://" . $_SERVER['HTTP_HOST'];
			$statusText = getStatusText($filaAlumnos2['status'], $statusOptions);
			$dateFollowUp = new DateTime($filaAlumnos2['followdate']);
			$fechaActual = new DateTime();
// Calcula la diferencia entre las fechas
			$diferencia = $fechaActual->diff($dateFollowUp);
// Obtiene la diferencia en días
			$diferenciaDias = $diferencia->days;

			$estilo = '';
			if ($dateFollowUp < $fechaActual) {
				$estiloCeldaDays = 'color: red;  font-weight: bold;';
				$diferenciaDias = '-' . $diferenciaDias;
				$checkboxClass= 'checkboxTo';
				$tablaclass = 'tablaTo';
			} elseif ($dateFollowUp > $fechaActual) {
				$estiloCeldaDays = 'color: blue;  font-weight: bold;';
				$diferenciaDias = '+' . $diferenciaDias;
				$checkboxClass = 'checkboxToHide';
				$tablaclass = 'tablaToHide';
			} else {
				// Si la fecha de seguimiento es igual al día actual
				// La mostramos en azul en lugar de rojo
				$estiloCeldaDays = 'color: blue;  font-weight: bold;';
				$diferenciaDias = '0';
				$checkboxClass = 'checkbox';
				$tablaclass = 'tabla';
			}

			$tabla .=

			'<table class="tablacabecera">

		</table>
		<br>
		<div class="form-check">
		<br>

		<div class="row align-items-start ">
                            <div class="col-lg-6 col-md-6 col-sm-12">
							<input class="form-check-input checkforselect '.$checkboxClass .'" type="checkbox" ' . $checkedprocess . ' id="processordercheck' . $CABECERACO['id'] . '" data-value="' . $CABECERACO['prc'] . '" style="'. $estiloCeldaDays . '-ms-transform: scale(2);-moz-transform: scale(2);-webkit-transform: scale(2);-o-transform: scale(2); background-color: #c4c6ca; " onclick="processValueFollow(this)"> <label class="form-check-label '.$tablaclass.'" for="flexCheckDefault" style="padding-left: 10px;">Event Complete</label>

                                <div class="form-text ms-1 '.$tablaclass.'"></div>
								<p>
								<p>
								<p>
								<input class="form-check-input checkforselect '.$checkboxClass .'" type="checkbox" ' . $checkedprocess . ' id="processordercheck' . $CABECERACO['id'] . '" data-value="' . $CABECERACO['prc'] . '" style="'. $estiloCeldaDays . '-ms-transform: scale(2);-moz-transform: scale(2);-webkit-transform: scale(2);-o-transform: scale(2); background-color: #c4c6ca; " onclick="processValueFollowNote(this)"> <label class="form-check-label '.$tablaclass.'" for="flexCheckDefault" style="padding-left: 10px;">Update Follow Up Note</label>
                                <div class="form-text ms-1 '.$tablaclass.'"></div>                            </div>
                           </div>
							</div>

                           ';


		$tabla .=
		'<table class="table table-striped table-hover tablageneral '.$tablaclass.'" >
		  <thead>
			<tr >
				<td><b><p>Event ref.</p></b></td>
				<td><b><p>Label</p></b></td>
				<td><b><p>Status</p></b></td>
				<td><b><p>Follow up Date</p></b></td>
				<td><b><p>Count Down (Days)</p></b></td>
				<td><b><p>Customer</p></b></td>
				<td><b><p style="text-align:center;">Contact/Address </p></b></td>
				<td><b><p style="text-align:center;">Ref.</p></b></td>
				<td><b><p>Amount</p></b></td>
				<td><b><p>Follow up days</p></b></td>
				<td><b><p>Email</p></b></td>
				<td><b><p>Phone</p></b></td>
				<td><b><p>Phone Mobile</p></b></td>
				<td><b><p>Follow up note</p></b></td>

			</tr></thead>';
	$tbody = "<tbody><div style='width:100%'>";



			if (!is_null($filaAlumnos2['FollowUpDays']) && $filaAlumnos2['FollowUpDays'] != 0) {
				$estiloDays = 'color: black;  font-weight: bold;';
			} elseif ($filaAlumnos2['FollowUpDays'] == 0) {
				$estiloDays = 'color: #FFFFFF;  font-weight: bold;';
			}
			$test=0;
			$estiloCelda = 'color: #f0f0f0; background-color: #5EBDF3; font-weight: bold;';
			$estiloCeldaNull = 'color: #f0f0f0; background-color: #c4bebd; font-weight: bold;';
			$estiloCeldaLinks = 's background-color: #0A0134; font-weight: bold;';

			$test=0;

			if(is_null($filaAlumnos2['FollowUpDays'])){
				$tabla .=
				'<tr style="text-align:center; ' . $estiloCeldaNull . ' ">
				    <td><p style="text-align:center; "><a href=' . $root_url . '/dolibarr/comm/action/card.php?id=' . $CABECERACO['id'] . ' style="color: black;">' . $filaAlumnos2['id'] . '</a></td>
					<td><p style="text-align:center; color: black;">' . $filaAlumnos2['label'] . '</td>
					<td><p style="text-align:center; color: black;">'  . $statusText . '</td>
					<td id ="followdate_' . $CABECERACO['id'] .'"><p style="text-align:center; color: black;">' . $filaAlumnos2['followdate'] . '</p></td>
					<td><p class="countdays ' . $checkboxClass . '" style="'. $estiloCeldaDays . '">'.$diferenciaDias.'</p></td>
					<td><p style="text-align:center; "><a href=' . $root_url . '/dolibarr/societe/card.php?id=' . $CABECERACO['idcustomer'] . ' style="color: black;">' . $filaAlumnos2['customer'] . '</a></td>
					<td><p style="text-align:center; "><a href=' . $root_url . '/dolibarr/contact/card.php?id=' . $CABECERACO['idcontact'] . ' style="color: black;">' . $filaAlumnos2['contact'] . '</p></td>
					<td><p style="text-align:center; "><a href=' . $root_url . '/dolibarr/comm/propal/card.php?id=' . $CABECERACO['prid'] . ' style="color: black;">' . $filaAlumnos2['ref'] . '</p></td>
					<td><p style="text-align:center; color: black;">' . round($filaAlumnos2['amount']) . '</td>
					<td><p style="text-align:center;  color: black;">' . $test . '</td>
					<td><p style="text-align:center; color: black;"><a href="mailto:'. $filaAlumnos2['email'] .'">' . $filaAlumnos2['email'] . '</a></p></td>
					<td><p style="text-align:left; color: black;"><a href="tel:'. $filaAlumnos2['phone'] .'">' . $filaAlumnos2['phone'] . '</a></p></td>
					<td><p style="text-align:left; color: black;"><a href="tel:'. $filaAlumnos2['phonem'] .'">' . $filaAlumnos2['phonem'] . '</a></td>';

					// <td><select  id="status_' . $filaAlumnos2['id'] . '" name="status_' . $filaAlumnos2['id'] . '">';

					// // Agrega opciones al campo de selección
					// foreach ($statusOptions as $value => $label) {
					// 	$selected = ($test == $value) ? 'selected' : '';
					// 	$tabla .= '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
					// }
					// $fechaHoraActual = date('Y-m-d H:i:s');
					// $tabla .= '</select></td>
					// $tabla .= '<td><textarea id="note_' . $filaAlumnos2['id'] . '" name="note_' . $filaAlumnos2['id'] . '" rows="50" cols="10" style="margin-top: 5px; width: 90%" class="flat"></textarea></td>
					$tabla .= '<td><input class="flat" type="text"  id="note_' . $filaAlumnos2['id'] . '" name="note_' . $filaAlumnos2['id'] . '"  value=""></td>

					</tr>';
			}elseif($filaAlumnos2['FollowUpDays']==0){
				$tabla .=
				'<tr style="text-align:center; ' . $estiloCeldaNull . ' ">
				    <td><p style="text-align:center; "><a href=' . $root_url . '/dolibarr/comm/action/card.php?id=' . $CABECERACO['id'] . ' style="color: black;">' . $filaAlumnos2['id'] . '</a></td>
					<td><p style="text-align:center; color: black;">' . $filaAlumnos2['label'] . '</td>
					<td><p style="text-align:center; color: black;">'  . $statusText . '</td>
					<td id ="followdate_' . $CABECERACO['id'] .'"><p style="text-align:center; color: black;">' . $filaAlumnos2['followdate'] . '</p></td>
					<td><p class="countdays ' . $checkboxClass . '" style="'. $estiloCeldaDays . '">'.$diferenciaDias.'</p></td>
					<td><p style="text-align:center; "><a href=' . $root_url . '/dolibarr/societe/card.php?id=' . $CABECERACO['idcustomer'] . ' style="color: black;">' . $filaAlumnos2['customer'] . '</a></td>
					<td><p style="text-align:center; "><a href=' . $root_url . '/dolibarr/contact/card.php?id=' . $CABECERACO['idcontact'] . ' style="color: black;">' . $filaAlumnos2['contact'] . '</p></td>
					<td><p style="text-align:center; "><a href=' . $root_url . '/dolibarr/comm/propal/card.php?id=' . $CABECERACO['prid'] . ' style="color: black;">' . $filaAlumnos2['ref'] . '</p></td>
					<td><p style="text-align:center; color: black;">' .round($filaAlumnos2['amount']) . '</td>
					<td><p style="text-align:center;  color: black;">' . $test . '</td>
					<td><p style="text-align:center; color: black;"><a href="mailto:'. $filaAlumnos2['email'] .'">' . $filaAlumnos2['email'] . '</a></p></td>
					<td><p style="text-align:left; color: black;"><a href="tel:'. $filaAlumnos2['phone'] .'">' . $filaAlumnos2['phone'] . '</a></p></td>
					<td><p style="text-align:left; color: black;"><a href="tel:'. $filaAlumnos2['phonem'] .'">' . $filaAlumnos2['phonem'] . '</a></td>';


					// <td><select  id="status_' . $filaAlumnos2['id'] . '" name="status_' . $filaAlumnos2['id'] . '">';

					// // Agrega opciones al campo de selección
					// foreach ($statusOptions as $value => $label) {
					// 	$selected = ($test == $value) ? 'selected' : '';
					// 	$tabla .= '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
					// }
					// $fechaHoraActual = date('Y-m-d H:i:s');
					// $tabla .= '</select></td>
					// $tabla .= '<td><textarea id="note_' . $filaAlumnos2['id'] . '" name="note_' . $filaAlumnos2['id'] . '" rows="50" cols="10" style="margin-top: 5px; width: 90%" class="flat"></textarea></td>
					$tabla .= '<td><input class="flat" type="text"  id="note_' . $filaAlumnos2['id'] . '" name="note_' . $filaAlumnos2['id'] . '"  value=""></td>

					</tr>';
			}else{
				$tabla .=
				'<tr style="text-align:center; ' . $estiloCelda . '">
				    <td><p style="text-align:center; "><a href=' . $root_url . '/dolibarr/comm/action/card.php?id=' . $CABECERACO['id'] . ' style="color: black;">' . $filaAlumnos2['id'] . '</a></td>
					<td><p style="text-align:center; color: black;">' . $filaAlumnos2['label'] . '</td>
					<td><p style="text-align:center; color: black;">'  . $statusText . '</td>
					<td id ="followdate_' . $CABECERACO['id'] .'"><p style="text-align:center; color: black;">' . $filaAlumnos2['followdate'] . '</p></td>
					<td><p class="countdays ' . $checkboxClass . '" style="'. $estiloCeldaDays . '">'.$diferenciaDias.'</p></td>
					<td><p style="text-align:center; "><a href=' . $root_url . '/dolibarr/societe/card.php?id=' . $CABECERACO['idcustomer'] . ' style="color: black;">' . $filaAlumnos2['customer'] . '</a></td>
					<td><p style="text-align:center; "><a href=' . $root_url . '/dolibarr/contact/card.php?id=' . $CABECERACO['idcontact'] . ' style="color: black;">' . $filaAlumnos2['contact'] . '</p></td>
					<td><p style="text-align:center; color: black;"><a href=' . $root_url . '/dolibarr/comm/propal/card.php?id=' . $CABECERACO['prid'] . ' style="color: black;">' . $filaAlumnos2['ref'] . '</p></td>
					<td><p style="text-align:center; color: black;">' . round($filaAlumnos2['amount']) . '</td>
					<td><p style="text-align:center; '  . $estiloDays . '">' . $filaAlumnos2['FollowUpDays'] . '</td>
					<td><p style="text-align:center; color: black;"><a href="mailto:'. $filaAlumnos2['email'] .'">' . $filaAlumnos2['email'] . '</a></p></td>
					<td><p style="text-align:left; color: black;"><a href="tel:'. $filaAlumnos2['phone'] .'">' . $filaAlumnos2['phone'] . '</a></p></td>
					<td><p style="text-align:left; color: black;"><a href="tel:'. $filaAlumnos2['phonem'] .'">' . $filaAlumnos2['phonem'] . '</a></td>';
					// <td><select  id="status_' . $filaAlumnos2['id'] . '" name="status_' . $filaAlumnos2['id'] . '">';

					// // Agrega opciones al campo de selección
					// foreach ($statusOptions as $value => $label) {
					// 	$selected = ($test == $value) ? 'selected' : '';
					// 	$tabla .= '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
					// }
					// $fechaHoraActual = date('Y-m-d H:i:s');
					// $tabla .= '</select></td>
					// $tabla .= '<td><textarea id="note_' . $filaAlumnos2['id'] . '" name="note_' . $filaAlumnos2['id'] . '" rows="50" cols="10" style="margin-top: 5px; width: 90%" class="flat"></textarea></td>
					$tabla .= '<td><input class="flat" type="text"  id="note_' . $filaAlumnos2['id'] . '" name="note_' . $filaAlumnos2['id'] . '"  value=""></td>

					</tr>';
			}


			// 	print "
			// 	<script src='https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js'></script>
			// 	<script type='text/javascript'>
			// 	$(document).ready(function() {
			// 		/* console.log('Run ckeditor'); */
			// 		/* if (CKEDITOR.loadFullCore) CKEDITOR.loadFullCore(); */
			// 		/* should be editor=CKEDITOR.replace but what if serveral editors ? */
			// 		CKEDITOR.replace('note_". $filaAlumnos2['id'] ."', {
			// 			/* property:xxx is same than CKEDITOR.config.property = xxx */
			// 			customConfig: ckeditorConfig,
			// 			readOnly: false,
			// 			htmlEncodeOutput: false,
			// 			allowedContent: false,
			// 			extraAllowedContent: '',
			// 			fullPage: false,
			// 			toolbar: 'dolibarr_notes',
			// 			toolbarStartupExpanded: true,
			// 			width: '100%',
			// 			height: 200,
			// 			skin: 'moono-lisa',
			// 			language: 'en_US',
			// 			textDirection: 'ltr',
			// 			on: {
			// 				instanceReady: function(ev) {
			// 					// Output paragraphs as <p>Text</p>.
			// 					this.dataProcessor.writer.setRules('p', {
			// 						indent: false,
			// 						breakBeforeOpen: true,
			// 						breakAfterOpen: false,
			// 						breakBeforeClose: false,
			// 						breakAfterClose: true
			// 					});
			// 				}
			// 			},
			// 			disableNativeSpellChecker: true,
			// 			filebrowserBrowseUrl: ckeditorFilebrowserBrowseUrl,
			// 			filebrowserImageBrowseUrl: ckeditorFilebrowserImageBrowseUrl,
			// 			filebrowserWindowWidth: '900',fffffffffffffffffffffffffffffffffffff
			// 			filebrowserWindowHeight: '500',
			// 			filebrowserImageWindowWidth: '900',
			// 			filebrowserImageWindowHeight: '500'
			// 		})
			// 	});
			// </script>";

		}
		$tabla = $tbody . $tabla . '</tbody>';

		/*
		 * FIN FILTRADO X   CO
		 * */


		 $tabla .=
		 '<tr>
		 	<td colspan="14" style="border-bottom: 3px dashed #000;"></td>
		 </tr</table>';
	$numline++;
	}
} else {
	$tabla = "No coincidences Found.";
}
echo $processall .$countDays  . $tabla;
