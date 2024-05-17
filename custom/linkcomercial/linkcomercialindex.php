<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *    \file       htdocs/linkcomercial/template/linkcomercialindex.php
 *    \ingroup    linkcomercial
 *    \brief      Home page of linkcomercial top menu
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include($_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
    $i--;
    $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) $res = @include(substr($tmp, 0, ($i + 1)) . "/main.inc.php");
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) $res = @include(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php");
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include("../main.inc.php");
if (!$res && file_exists("../../main.inc.php")) $res = @include("../../main.inc.php");
if (!$res && file_exists("../../../main.inc.php")) $res = @include("../../../main.inc.php");
if (!$res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/linkcomercial/class/linkpresupuesto.class.php';
require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
require_once(DOL_DOCUMENT_ROOT . "/commande/class/commande.class.php");
require_once DOL_DOCUMENT_ROOT . '/supplier_proposal/class/supplier_proposal.class.php';

//if (!$user->rights->linkcomercial->read) accessforbidden();
if (!$user->id > 0) {
    accessforbidden();
}
$langs->load("linkcomercial@linkcomercial");
$action = GETPOST('action', 'alpha');
$origin = GETPOST('origin', 'alpha');
$iddoc = GETPOST('originid', 'alpha');
$thirdid = GETPOST('socid', 'alpha');

if ($action == 'presupcliente') {
    $presupuesto = new SupplierProposal($db);
    $presupuesto->fetch($iddoc);

    $nuevopcliente = new linkpresupuesto();
    //TODO Alberto averiguar el primer proveedor que tenga precios para la lista de productos y pasarlo como $thirdid

    $id = $nuevopcliente->convierte_presupuesto_cliente($presupuesto, $origin, $thirdid, $action, $iddoc);
    if ($id){

        $url=DOL_URL_ROOT.'/comm/propal/card.php?id='.$id;
        header("Location: ".$url);
        exit;
    }
    else
    {
        $previous = "javascript:history.go(-1)";
        if(isset($_SERVER['HTTP_REFERER'])) {
            $previous = $_SERVER['HTTP_REFERER'];
        }
    }
   /* print '<script type="text/javascript">
			$(document).ready(function() {
					var id='.$id.';
					var url="'.$url. ' ;
					//window.location.href = "'.$_SERVER["PHP_SELF"].'?action=create&socid="+socid+"&ref_client="+$("input[name=ref_client]").val();
					window.location = url ;

			});
			</script>';*/

}
if ($action == 'pedidocliente') {
    //$form=new Form($db);
    // print $form->formconfirm( $_SERVER["PHP_SELF"], $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_delete','',0,1);
    $presupuesto = new Commande($db);
    $presupuesto->fetch($iddoc);

    $nuevopcliente = new linkpresupuesto();
    //$id = $nuevopcliente->convierte_pedido_proveedor($presupuesto, $origin, $thirdid, $action, $iddoc);
	//header("Location: ".$_SERVER["PHP_SELF"].'?socid='.$socid.'&action=create&origin=commande&originid='.$originid);
	header("Location: ".DOL_URL_ROOT.'/custom/linkcomercial/commandcard.php?socid='.$thirdid.'&action=create&origin=commande&originid='.$iddoc);

//	if ($id>0){
//
//        $url=DOL_URL_ROOT.'/fourn/commande/card.php?id='.$id;
//        header("Location: ".$url);
//        exit;
//    }
//    else{
//        //$previous = "javascript:history.go(-1)";
//        if(isset($_SERVER['HTTP_REFERER'])) {
//            setEventMessage('El cliente debe ser proveedor y tener precios de productos de compra', 'errors');
//            header('Location: ' . $_SERVER['HTTP_REFERER']);
//        }
//    }
}


