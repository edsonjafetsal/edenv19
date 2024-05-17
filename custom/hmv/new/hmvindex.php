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
 *	\file       hmv/hmvindex.php
 *	\ingroup    hmv
 *	\brief      Home page of hmv top menu
 */

header ("Expires: Fri, 14 Mar 1980 20:53:00 GMT"); //la pagina expira en fecha pasada
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); //ultima actualizacion ahora cuando la cargamos
header ("Cache-Control: no-cache, must-revalidate"); //no guardar en CACHE
header ("Pragma: no-cache"); //PARANOIA, NO GUARDAR EN CACHE


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
$langs->loadLangs(array("hmv@hmv"));

print '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="peticion.js"></script>
';
$action = GETPOST('action', 'aZ09');
$actionup = GETPOST('actionup', 'aZ09');
$actionid = GETPOST('actionid', 'aZ09');
$cliente = GETPOST('cliente', 'aZ09');
$adas= $_POST['value'];
$product = GETPOST('value', 'aZ09');
$cat = GETPOST('cat', 'aZ09');
$rep = GETPOST('rep', 'aZ09');



// Security check
//if (! $user->rights->hmv->myobject->read) accessforbidden();
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

llxHeader("", $langs->trans("HMVArea"));

print load_fiche_titre($langs->trans("HMVArea"), '', 'hmv.png@hmv');

print '<div class="fichecenter"><div class="fichethirdleft">';



print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


$NBMAX = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;
$max = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;

print '</div></div></div>';

/*INICIA CODIGO*/



/*
 * 			AJAX
 * */
print ' ';
print "<script type='text/javascript'>
        $(document).ready(function(){
          $('#product').on('change',function(){
            let valor = $('#product').val();
            console.log(valor);
            $.ajax({
               type: 'POST',
               url : 'hmvindex.php',
               data: {value: valor},
               success: function(data)
               {
        
               }
             });
          });
        });
       $(obtener_registros());
    
    function obtener_registros(alan)
    {
        $.ajax({
            url : 'consulta.php',
            type : 'POST',
            dataType : 'html',
            data : { alan: alan },
            })
    
        .done(function(resultado){
            $('#tabla_resultado').html(resultado);
        })
    }
    
    $(document).on('keyup', '#product', function()
    {
        var valorBusqueda=$('#product').val();
        if (valorBusqueda!= '')
        {
            obtener_registros(valorBusqueda);
        }
        else
        {
            obtener_registros();
        }
    })
    
    function Datos()
    {
      cliente   =   $('#cliente').val();
      producto  =   $('#product').val();
      cate       =   $('#cate').val();
      sales     =   $('#rep').val();
      date1      =   $('#date1').val();
      $.ajax({
        url  : 'consulta.php',
        type : 'post',
        data: {cliente :cliente, producto:producto, cate:cate, sales:sales, date1:date1},
        success:function(respuesta){
            $('#resultados').html(respuesta)
        }
      })
      
    }


    </script>
    ";

/*
 * 			AJAX
 * */


    print '<label class ="form-label"> DATE</label><input type="date" class="date-range" id="date1" name="date1" value="'.$cliente['rowid'].'"><br>';

    $sql = "SELECT rowid, nom FROM llx_societe ";
    $resql = $db->query($sql);
    print '<label class ="form-label">Customer</label>';

    print '<select class="form-select" aria-label="Default select example" name = "cliente" id = "cliente">';
        print '<option value = "" selected>         </option>';
        foreach ($resql as $cliente){
        print "<option value = ".$cliente['rowid']." >".$cliente['nom']."</option>";
    }
    print '</select>';

    $sql = "SELECT rowid, ref FROM llx_product ";
    $resql = $db->query($sql);
    $i = 0;
    $num = $db->num_rows($resql);
        $b=0;
    foreach($resql as $values1)
    {
        foreach($values1 as $value1)
        {
            $prod[$b][].=	$value1;
        }
        $b=$b+1;
    }

    print '<label class ="form-label"> Product</label>';
    print '<select class="form-select" aria-label="Default select example" id = "product" name = "product">';
    print '<option value = "" selected>         </option>';
    for ($i=0; $i < $num; $i++)
    {
        print '<option value = "'.$prod[$i][0].'" >'.$prod[$i][1].'</option>';

    }
    print '</select>';

    //print '<pre>product ';print_r($prod[$i]);print '</pre>'; verificacion de datos

    $sql = "SELECT rowid, label FROM llx_categorie ";
    $resql = $db->query($sql);
    print '<label class ="form-label">Tag/Category</label>';

    print '<select class="form-select" aria-label="Default select example" name = "cate" id = "cate">';
    print '<option value = "" selected>         </option>';
        foreach ($resql as $cat){
            print "<option value = ".$cat['rowid']." >".$cat['label']."</option>";
        }
    print '</select>';

    $sql = "SELECT  u.rowid, CONCAT(u.firstname,' ',u.lastname) AS user FROM llx_user AS u LEFT JOIN  llx_usergroup_user AS gu ON u.rowid = gu.fk_user WHERE gu.fk_usergroup = 5";
    $resql = $db->query($sql);
    print '<div>';
        print '<label class ="form-label"> Sales Representative</label>';
        print '<select class="form-select" aria-label="Default select example" name = "rep" id = "rep">';
            print '<option value = "" selected>         </option>';
            foreach ($resql as $rep){
                print "<option value = ".$rep['rowid']." >".$rep['user']."</option>";
            }
        print '</select>';
    print '</div>';
print ' <div>
        <a class="butAction btn4" aria-current="page" id="btn4" onclick="Datos()">Execute</a>
        </div>';
//print '<div class="input-group">
  //<div class="input-group-prepend">
    //<div class="input-group-text">
      //<input type="checkbox" aria-label="Hide CO Ref.">
    //</div>
  //</div>
  //<input type="text" class="form-control" aria-label="Hide CO Ref.">
//</div>';

    print '<section>';
    print			'<input type="text" name="busqueda" id="busqueda" placeholder="Search...">';


    print'</section>';
    print			'<section id="tabla_resultado">';
    //		<!-- AQUI SE DESPLEGARA NUESTRA TABLA DE CONSULTA -->
    print'</section>';



//    print '<br><label>HIDE COLUMNS</label><br><label><input type="checkbox" id="cbox1" value=""> Hide CO Ref.</label><input type="checkbox" id="cbox2" value=""> Hide Product</label> <input type="checkbox" id="cbox3" value=""> Hide Shipment</label><label><input type="checkbox" id="cbox4" value=""> Hide Lot</label><br>';
  //  print '<label><input type="checkbox" id="cbox4" value=""> Hide Lot</label> <input type="checkbox" id="cbox5" value=""> Hide Customer</label><label><input type="checkbox" id="cbox6" value=""> Hide Description</label><label><input type="checkbox" id="cbox7" value=""> Hide Selling Price</label><label><input type="checkbox" id="cbox8" value=""> Hide Buying Price </label><br>';
    //print '<br><label>DROPSHIPPED</label><br><label><input type="checkbox" id="cbox9" value="">Yes </label><label><input type="checkbox" id="cbox10" value="">No </label><br><br>';

    //print '<a  class="butAction" href="'.DOL_URL_ROOT.'/custom/hmv/hmvindex.php?actionup=execute">'.$langs->trans("GO").'</a>';

    if ($actionup == 'execute') {
        $sql = "SELECT CONCAT(u.firstname,' ',u.lastname) AS salesrep, (cod.buy_price_ht*cod.qty) AS extension, FORMAT(cod.total_ht, 2) AS total_line, co.ref AS co, p.ref AS product, ex.ref AS shipment, eba.batch AS lot_shipment, CASE(p.tobatch) WHEN '0' THEN 'NO' WHEN '1' THEN 'YES' END AS is_lot, soc.nom AS third,  p.description AS prod_desc, FORMAT(cod.subprice, 2) AS sell_pri, FORMAT(cod.buy_price_ht, 2) AS buy_pri, co.date_commande AS co_date, cod.qty As qty, cat.label AS categorie, CONCAT(ROUND(100-(((cod.buy_price_ht*cod.qty)* 100)/cod.total_ht) , 0), '%') AS margin2, co.rowid, coex.proc, CASE(coex.proc) WHEN'0' THEN 'NO' WHEN '1' THEN 'YES' END AS prc";
        $sql .= " FROM " . MAIN_DB_PREFIX . "commande AS co";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "commandedet AS cod ON co.rowid = cod.fk_commande";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "commande_extrafields AS coex ON co.rowid = coex.fk_object";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "expeditiondet AS exde ON  cod.rowid = exde.fk_origin_line";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "expedition AS ex ON  exde.fk_expedition = ex.rowid";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "expeditiondet_batch AS eba ON exde.rowid = eba.fk_expeditiondet";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "product AS p ON  cod.fk_product = p.rowid";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe AS soc ON co.fk_soc = soc.rowid ";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "element_contact as ec ON ec.element_id = co.rowid and ec.fk_c_type_contact in (91) ";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_type_contact AS ct ON ec.fk_c_type_contact = ct.rowid AND ct.element LIKE 'commande' ";
        $sql .= " left join " . MAIN_DB_PREFIX . "user as u ON u.rowid = ec.fk_socpeople ";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "categorie_product cats ON cats.fk_product = p.rowid ";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "categorie cat ON cat.rowid = cats.fk_categorie ";
        $sql .= " WHERE 1 = 1 ";
        if ($product != "") {
            $sql .= "AND  p.rowid  = " . $product;
            $sql .= " AND p.ref IS NOT NULL ";
        }
        $sql .= " GROUP BY  co.rowid";

        //if (! $user->rights->societe->client->voir && ! $socid) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
        $resql = $db->query($sql);
        if ($resql) {
            $a1 = 0;
            $b = 0;
            foreach ($resql as $value) {
                foreach ($value as $a) {

                    $datoss[$b][] .= $a;
                }
                $b = $b + 1;
                $a1 = 0;
            }
        } else {
            print "Not Found";
        }

    }







// End of page
llxFooter();
$db->close();
