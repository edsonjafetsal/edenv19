<?php
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
 * \file    htdocs/modulebuilder/template/js/mymodule.js.php
 * \ingroup mymodule
 * \brief   JavaScript file for module MyModule.
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

$(function () {
	if (typeof changeTiers != 'undefined' && typeof urlConf != 'undefined') {
		if (window.location.pathname == urlConf && changeTiers == true) {
			//$( "<a href='#' id='changetiersbtn'><img class='valigntextbottom' src='"+pictoChangeTiers+"'></a>" ).insertAfter($('.refidno a[href*="societe/card.php?socid="], .refidno a[href*="societe/soc.php?socid="]').last());
			$("<a href='#' id='changetiersbtn'><span class='fas fa-pencil-alt' style=' color: #444;' title='Modify Third Party'> : </span>").replaceAll($('#id-right > div > div.tabBar > div.arearef.heightref.valignmiddle.centpercent > div > div.inline-block.floatleft.valignmiddle.maxwidth750.marginbottomonly.refid.refidpadding > div > a.classfortooltip.refurl > span'));

			$(document).on('click', '#changetiersbtn', function () {

				//$(formTiers).insertAfter(this);
				$(formTiers).insertAfter(".refidno");
				//$('.refidno a[href*="societe/card.php?socid="], .refidno a[href*="societe/soc.php?socid="]').hide();
				$('.refidno a[href*="comm/card.php?socid="], .refidno a[href*="societe/soc.php?socid="]').hide();
				$(this).hide();
				eval(scriptTiers);
				//$("#id-right a.classfortooltip.refurl").hide();
				return false;
			});

			$(document).on('click', '#changetierscancelbtn', function () {
				if (window.location.href.indexOf("/linkcomercial/commandcard.php")>-1){
                   $("#formchangetiers").remove();
                   $("#modify_commande_tparty").show();
				}
				else{

					$(this).parents('form').remove();
				}
				//$('.refidno a[href*="societe/card.php?socid="], .refidno a[href*="societe/soc.php?socid="]').show();
				$('.refidno a[href*="comm/card.php?socid="], .refidno a[href*="societe/soc.php?socid="]').show();
				$('#changetiersbtn').show();

				return false;
			});
		}
	}

});
if (window.location.href.indexOf("/commande/card.php") > -1 || window.location.href.indexOf("/commande/list.php") > -1) {
	$(document).ready(function () {
		$('[data-key="dropship"] input[type=checkbox]').each(function () {
			if (this.checked) {
				$(this).replaceWith("<span class=\"fas fa-shipping-fast green paddingleft\" style=\" color: #a69944;\"></span>");
			} else {
				$(this).replaceWith("<span class=\"fas fa-shipping-fast gray paddingleft\" style=\" color: #7d7b75;\"></span>");
			}

		});
		$('.commande_extras_dropship input[type=checkbox]').each(function () {
			if (this.checked) {
				$(this).replaceWith("<span class=\"fas fa-shipping-fast green paddingleft\" style=\" color: #a69944;\"></span>");
			} else {
				$(this).replaceWith("<span class=\"fas fa-shipping-fast gray paddingleft\" style=\" color: #7d7b75;\"></span>");
			}

		});

});
}
if (window.location.href.indexOf("/linkcomercial/commandcard.php")>-1){
	$(document).on('click', '#modify_commande_tparty', function () {
		$(formTiers).insertAfter("#modify_commande_tparty");
		//$('.refidno a[href*="societe/card.php?socid="], .refidno a[href*="societe/soc.php?socid="]').hide();
		$('.refidno a[href*="comm/card.php?socid="], .refidno a[href*="societe/soc.php?socid="]').hide();
		$(this).hide();

	});
}
