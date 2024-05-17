<?php

/**
* Written by INOVEA-CONSEIL <info@inovea-conseil.com>
* Copyright 2017
 *
 *  AGOSTO 2021 CAMBIOS REALIZADOS AGREGARON 3 CATEGORIAS POR CLIENTE SOLCIITADO EN LINES
 *  AUGUST 2021 CHANGES MADE ADDED 3 CATEGORIES PER CUSTOMER REQUESTED IN LINES
 *  Written by INOVEA-CONSEIL <info@inovea-conseil.com>
 *  MODIFIED by ALAN MONTOYA <mstoluca@gmail.com>
 **/
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';					// to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';			// to work if your module directory is into a subdir of root htdocs directory
if (! $res && file_exists("../../../main.inc.php")) $res=@include '../../../main.inc.php';			// to work if your module directory is into a subdir of root htdocs directory
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../dolibarr/htdocs/main.inc.php';     // Used on dev env only
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../../dolibarr/htdocs/main.inc.php';   // Used on dev env only
if (! $res) die("Include of main fails");
global $langs,$conf;
$langs->load('importline@importline');
require_once(DOL_DOCUMENT_ROOT .'/comm/propal/class/propal.class.php');
require_once(DOL_DOCUMENT_ROOT .'/commande/class/commande.class.php');
require_once(DOL_DOCUMENT_ROOT .'/compta/facture/class/facture.class.php');

$url = dol_buildpath('/importline/js/ajax.php',1);
$id = $_POST['id'];
$source=GETPOST('src');
$dest=GETPOST('dst');
 $object = new $source($db);
$object->fetch($id);
$object->fetch_lines();
$send = GETPOST('send');
$idactual = $_POST['idactual'];

if($send && $dest == 'Propal'){
    $o = new $dest($db);
    $o->fetch($idactual);


    foreach(GETPOST('toSend') as $tos){
        $tos=preg_replace('#\'#','',$tos);

        foreach($object->lines as $l){

            if($tos==$l->rowid){

               $o->addline($l->desc, $l->subprice, $l->qty, $l->tva_tx, $l->localtax1_tx, $l->localtax2_tx, $l->fk_product, $l->remise_percent, 'HT', $l->pu_ttc, $l->info_bits, $l->product_type, '', $l->special_code, $l->fk_parent_line, $l->fk_fournprice, $l->pa_ht, $l->libelle,$l->date_start, $l->date_end,$l->array_options, $l->fk_unit, $l->origin, $l->origin_id, $l->pu_ht_devise, $l->fk_remise_except);
            }
        }

    }
    $return=$langs->trans("newlinefromother");
}elseif($send && $dest == 'Commande'){
    $o = new $dest($db);
    $o->fetch($idactual);


    foreach(GETPOST('toSend') as $tos){
        $tos=preg_replace('#\'#','',$tos);

        foreach($object->lines as $l){

            if($tos==$l->rowid){

               $o->addline($l->desc, $l->subprice, $l->qty, $l->tva_tx, $l->localtax1_tx, $l->localtax2_tx, $l->fk_product, $l->remise_percent, $l->info_bits, $l->fk_remise_except, 'HT', $l->pu_ttc, $l->date_start , $l->date_end, $l->product_type, $l->rang, $l->special_code, $l->fk_parent_line, $l->fk_fournprice, $l->pa_ht, $l->libelle,$l->array_options, $l->fk_unit, $l->origin, $l->origin_id, $l->pu_ht_devise);
            }

        }

    }
    $return=$langs->trans("newlinefromother");
}else if($send && $dest == 'Facture'){
    $o = new $dest($db);
    $o->fetch($idactual);

    foreach(GETPOST('toSend') as $tos){
        $tos=preg_replace('#\'#','',$tos);

        foreach($object->lines as $l){

            if($tos==$l->rowid){
                $o->addline($l->desc, $l->subprice, $l->qty, $l->tva_tx, $l->localtax1_tx, $l->localtax2_tx, $l->fk_product, $l->remise_percent, $l->date_start, $l->date_end, $l->info_bits, 'HT',$l->fk_remise_except, $l->pu_ttc, $l->product_type, $l->rang, $l->special_code, $l->origin, $l->origin_id, $l->fk_parent_line, $l->fk_fournprice, $l->pa_ht, $l->libelle,$l->array_options, null, null, $l->fk_unit, $l->pu_ht_devise);
            }
        }

    }
    $return=$langs->trans("newlinefromother");
}else{
$return = "<table class='table-responsive'>"; //border centpercent
$return .="<tr class='liste_titre nodrag nodrop '><th>".$langs->trans("CAT1")."</th><th>".$langs->trans("CAT2")."</th><th>".$langs->trans("CAT3")."</th><th>".$langs->trans("Ref")."</th><th>".$langs->trans("Label")."</th><th>".$langs->trans("Description")."</th><th>".$langs->trans("UnitPrice")."</th><th>".$langs->trans("Qty")."</th><th>".$langs->trans("Total")."</th><th><input class='flat check' type='checkbox' id='all'></th></tr>";
foreach($object->lines as $l){
    $cats= $db->query ("select lp.rowid, lp.ref, lp.label,lc.rowid, lc.label as CAT3, lc2.label  as CAT2, lc3.label  as CAT1   
        from " . MAIN_DB_PREFIX . "product lp 
        left join " . MAIN_DB_PREFIX . "categorie_product lcp  on lp.rowid  = lcp.fk_product 
        left JOIN  " . MAIN_DB_PREFIX . "categorie lc on lcp.fk_categorie  = lc.rowid 
        left join " . MAIN_DB_PREFIX . "categorie lc2 on lc.fk_parent = lc2.rowid 
        left join " . MAIN_DB_PREFIX . "categorie lc3 on lc2.fk_parent = lc3.rowid 
        where lp.ref  = '".$l->ref."'");
    foreach($cats as $cat){
        $categoria[] = $cat;
    }

    $CAT3 = $categoria[0]['CAT3'];

    $CAT2 = $categoria[0]['CAT2'];
    $CAT1 = $categoria[0]['CAT1'];
	if($CAT1 == null){
		$CAT1 = $CAT2;
		$CAT2 =$CAT3;
		$CAT3 = NULL;
	}
	if($CAT2 == null){
		$CAT2 = $CAT3;
		$CAT3 = null;
	}
	if($CAT3 == null){
		$CAT3 = ' ';
	}

    $return .= "<tr class='drag drop oddeven'><td class='cursorpointer classfortooltip '>"; //inline-block  clase que se quito
    if(!empty($l->fk_parent_line))
    $return .=img_picto('', 'rightarrow');
    $return .="$CAT1</td><td>$CAT2</td><td>$CAT3</td><td>$l->ref</td><td>$l->libelle</td><td>$l->desc</td><td>".price($l->subprice)."</td><td>$l->qty</td><td>".price($l->total_ht)."</td><td><input class='flat checkforsend sendid' type='checkbox' name='toSend' value=\"'$l->rowid'\"></tr>";


}
$return .="</table>";
$trad = $langs->trans("send");
$return .="<div style='text-align:center; width:100%;padding-top:30px;'><input type='button' class='butAction' value='".$langs->trans("ImportLineFrom")."' id='submit'>";
$return .="
               <script>
               $(document).ready(function() {
                $('#submit').click(function(){
                var idChecked = Array();
                $('.sendid').each(function(i) {
                    if($(this).is(':checked')){
                     idChecked[i] = $(this).val();
                    }
                });
                src='$source';
                        $.post( '$url', { id: $id, idactual:$idactual,src:src,send:1,dst:'$dest',toSend:idChecked},function(data){
                    $('#formcontent').html(data);
                    });
                });
                $('#all').change(function(){
                    if($(this).is(':checked')){
                        $('.sendid').prop('checked', true);
                    }else{
                        $('.sendid').prop('checked', false);
                    }
                });
               });
               </script>
       ";
}
echo $return;