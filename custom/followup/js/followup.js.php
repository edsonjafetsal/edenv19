<?php
/* Copyright (C) 2023 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Library javascript to enable Browser notifications
 */

if (!defined('NOREQUIREUSER')) define('NOREQUIREUSER', '1');
if (!defined('NOREQUIREDB')) define('NOREQUIREDB', '1');
if (!defined('NOREQUIRESOC')) define('NOREQUIRESOC', '1');
if (!defined('NOREQUIRETRAN')) define('NOREQUIRETRAN', '1');
if (!defined('NOCSRFCHECK')) define('NOCSRFCHECK', 1);
if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', 1);
if (!defined('NOLOGIN')) define('NOLOGIN', 1);
if (!defined('NOREQUIREMENU')) define('NOREQUIREMENU', 1);
if (!defined('NOREQUIREHTML')) define('NOREQUIREHTML', 1);
if (!defined('NOREQUIREAJAX')) define('NOREQUIREAJAX', '1');


/**
 * \file    followup/js/followup.js.php
 * \ingroup followup
 * \brief   JavaScript file for module Followup.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/../main.inc.php")) $res = @include substr($tmp, 0, ($i + 1)) . "/../main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

// Define js type
header('Content-Type: application/javascript');
// Important: Following code is to cache this file to avoid page request by browser at each Dolibarr page access.
// You can use CTRL+F5 to refresh your browser cache.
if (empty($dolibarr_nocache)) header('Cache-Control: max-age=3600, public, must-revalidate');
else header('Cache-Control: no-cache');
?>

/* Javascript library of module Followup */
$(function () {
	if (window.location.toString().indexOf('/comm/action/card.php') > -1) {
		$("#actioncode").on("change", function (e) {
			//obtener el parametro socid de la url actual
			try {
				var socid = window.location.href.match(/socid=([0-9]+)/)[1];
			} catch (e) {
				return	;
			}

			//ajax call to get thirdparty info

			$.ajax({
				type: "post",
				data: {socid: socid,action:"getthirdpartyinfo",token: "<?php echo $_SESSION['newtoken']; ?>"},
				url: '../../custom/followup/admin/ajax.php',
				success: function (data) {
					var sd=JSON.parse(data)
					if (sd.result == "ok") {
						//get select option  selected value and text
						var actioncode = $("#actioncode option:selected").text();
						var thirdpartyinfo = sd.data.name;
						var newactioncode = thirdpartyinfo + " - " + actioncode;
						$("#label").val(newactioncode);
					}

				}
			});

		});
	}

});

jQuery(document).ready(function () {
<?php if (!$conf->global->FOLLOWUP_DISABLE_{$socid}){
	?>
	$(".deleteevent").off().click(function (e) {
		e.preventDefault();
		var id = $(this).data('id');
		var url='ajax.php?action=deleteevent&id=' + id;
		var $this = $(this);
		//sweet alert confirm delete and ajax call
		Swal.fire({
			title: "¿Delete Event?",
			text: "¡Are you Sure to Delete this Event!",
			type: 'warning',
			confirmButtonText: 'Yes, Delete it!',
			cancelButtonText: 'No, Cancel it!',
			confirmButtonColor: "#DD6B55",
			showCancelButton: true,
			closeOnConfirm: false,
			closeOnCancel: false
		}).then((result) => {
			if (result.value) {
				$.ajax({
					type: "POST",
					contentType: "application/json; charset=utf-8",
					dataType: "json",
					data: {id: id},
					url: url,
					success: function (data) {
						if (data.result== "ok") {
							$.jnotify("Event Deleted Successfully", "success");
							window.location.reload();

						} else {

						}
					},
					error: function (data) {
						$.jnotify("Error Deleting Event", "error");
					}
				});

				return false;
			}
		});

	});
	$(".editevent").off().click(function (e) {
		e.preventDefault();
		var id = $(this).data('id');
		var descripcion=$(this).parent().prev('.editable').text().trim();
		var url='ajax.php?action=editevent&id=' + id;
		var $this = $(this);
		Swal.fire({
			title: "Edit Event",
			text: "Edit Event Description",
			input: 'textarea',
			inputValue:descripcion,
			inputAttributes: {
				'aria-label': 'Type your message here'
			},
			showCancelButton: true,
			confirmButtonText: 'Save',
			cancelButtonText: 'Cancel',
			confirmButtonColor: "#DD6B55",
			closeOnConfirm: false,
			closeOnCancel: false
		}).then((result) => {
			if (result.value) {
				$.ajax({
					type: "POST",
					contentType: "application/json; charset=utf-8",
					dataType: "json",
					data: {id: id, description: result.value},
					url: url + '&description=' + result.value,
					success: function (data) {
						if (data.result== "ok") {
							$.jnotify("Event Edited Successfully", "success");
							window.location.reload();

						} else {

						}
					},
					error: function (data) {
						$.jnotify("Event Edited Successfully", "success");
						window.location.reload();
					}
				});

				return false;
			}
		});


	});
	<?php
	}

 ?>


	$(".tab_list a").click(function (e) {
	principal();
	})
	$("#btn_list").click(function (e){

		e.preventDefault()
		$('#tab_list').block({
			message: '<h1>Processing</h1>',
			css: { border: '3px solid #a00' }
		});
		var url_string = window.location.href;
		var url = new URL(url_string);
		var socid = url.searchParams.get("socid");
		//$("#tab_list").load('../../../comm/action/index.php?search_socid=44&search_filtert=-1 #id-right');
		$( "#listado" ).load( '../../../comm/action/list.php?action=show_list&search_socid='+socid+'&search_actioncode=AC_NON_AUTO&limit=500&search_filtert=-1 #id-right', function( response, status, xhr ) {
			$("#searchFormList > div.nowrap.inline-block.minheight30").hide();
			$(".table-fiche-title").hide();
			$("#tab_list").unblock();
			$("#searchFormList > div:nth-child(4)").hide();
			$("#searchFormList > div.liste_titre.liste_titre_bydiv.centpercent").hide();
			$(".liste_titre_filter").hide();
			$(".liste_titre").hide();
			$("#btn_month").css("border-color", "initial");
			$("#btn_list").css("border", "1px solid #ccc");
		});
	});
	$("#btn_month").click(function(e){
		e.preventDefault();
		principal();
	});
function principal(){
	$('#tab_list').block({
		message: '<h1>Processing</h1>',
		css: { border: '3px solid #a00' }
	});
//get url parameter socid
	var url_string = window.location.href;
	var url = new URL(url_string);
	var socid = url.searchParams.get("socid");

	//$("#tab_list").load('../../../comm/action/index.php?search_socid=44&search_filtert=-1 #id-right');
	$( "#listado" ).load( '../../../comm/action/index.php?search_socid='+socid+'&search_filtert=-1 #id-right', function( response, status, xhr ) {
		$("#searchFormList > div.nowrap.inline-block.minheight30").hide();
		$(".table-fiche-title").hide();
		$("#tab_list").unblock();
		$("#searchFormList > div:nth-child(4)").hide();
		$("#searchFormList > div.liste_titre.liste_titre_bydiv.centpercent").hide();
	});
}

});




