<?php


function getSelect($table, $filtros = '1 = 1'){

	global $db;
	if( $table == 'Thickness1' ||$table == 'Thickness2' || $table == 'Length1' ||$table == 'Length2' ||$table == 'Width1' ||$table == 'Width2') {


		global $db;

		if($table == 'Width1' ||  $table == 'Width2' )   $campo = 'wide';
		if($table == 'Thickness1' || $table == 'Thickness2'  ) $campo = 'height';
		if($table == 'Length1' || $table == 'Length2' )   $campo = 'length';
// width
////weight
//	length

		//select p.weight  from llxas_product as p
		if($table == 'Width1' ||  $table == 'Width2' ){
			$sql = " select ".$campo." from " . MAIN_DB_PREFIX . "product_extrafields as p group by  ".$campo;
		}else{
			$sql = " select ".$campo." from " . MAIN_DB_PREFIX . "product as p group by  ".$campo;
		}



		$result = $db->query($sql);

		if ($result) {
			$num = $db->num_rows($result);
			$i = 0;
			while ($i < $num) {
				$obj = $db->fetch_object($result);
				$template[] = $obj;
				$i++;
			}
		}
		$out = '<select class="flat maxwidth200 onsmartphone minwidth200" name="select_'.$table.'" id="select_'.$table.'">';
		$out .= '	<option value="">Select option</option>';
//width
		//weight
		//length
		$valor=0;
		for($a=0; $a<$num; $a++) {
		if($template[$a]->length!=null){

			$out .= '	<option value="'.$template[$a]->length.'">'.$template[$a]->length.'</option>';
		}
			if($template[$a]->wide!=null){
				$valor=$valor+1;
				$out .= '	<option value="'.$template[$a]->wide.'">'.$template[$a]->wide.'</option>';
			}
			if($template[$a]->height!=null){

				$out .= '	<option value="'.$template[$a]->height.'">'.$template[$a]->height.'</option>';
			}

	}
		if($valor>1)$out .= '	<option value="both">Both</option>';
		$out .=  '</select>';

	}


	return $out;

}//REGRESA  select societe

function getRecords($table, $id= '')
{

	global $db;
	$sql = " select * from " . MAIN_DB_PREFIX . "" . $table;

	if ($id != '')$sql .= " where rowid = ".$id;

	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($result);
			$template[] = $obj;
			$i++;
		}
	}
	return $template;
}



/*function getDataReport1($idSociete,$idCommande,$idFacture,$idProduct,$idCategorie,$idUser,$id_c_product_nature, $date, $datestart, $dateend, $workstation, $nomenclature  ){

	global $db;
	$sql = " SELECT ";
	$sql.= " lp.label as label, ";
	$sql .= " ln2.title as title, ";
	$sql .= " lc2.ref as  Commande, ";
	$sql .= " lc2.rowid AS ORDERROWID, ";
	$sql .= " (select lp2.label from ".MAIN_DB_PREFIX."product lp2 where  lnd2.fk_product  = lp2.rowid) as Product2, ";
	$sql .= " lnd2.qty  as Qty, ";
	$sql .= " (SELECT lw.name  from ".MAIN_DB_PREFIX."workstation lw WHERE lnw.fk_workstation = lw.rowid   ) as Workstation1, ";
	$sql .= " lnw.rang as rango, ";
	$sql .= " lnw.nb_hour as  hour, ";
	$sql .= " lnw.nb_hour_prepare as hourprepare , ";
	$sql .= " lnw.nb_hour_manufacture as hourmanufacture, ";
	$sql .= " lc2.date_commande as Datecomande, ";
	$sql .= " lc2.date_valid  as Datedeliver, ";
	$sql .= "  (select lp3.label from ".MAIN_DB_PREFIX."product lp3 where lnd2.fk_product= lp3.rowid) as RAWMATERIAL, ";
	$sql .= " s.nom   as societe, ";
	$sql .= " s.rowid  as societerowid ";
	$sql .= " FROM ".MAIN_DB_PREFIX."product lp  ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."nomenclature ln2  ON lp.rowid = ln2.fk_object ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."nomenclature_workstation lnw  ON ln2.rowid  = lnw.fk_nomenclature ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."nomenclaturedet lnd2  ON ln2.rowid = lnd2.fk_nomenclature ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."commandedet lc ON lnd2.fk_product ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."commande lc2 ON lc.fk_commande = lc2.rowid ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe s ON lc2.fk_soc = s.rowid ";
	$sql .= " WHERE ";
	if($idProduct != '') $sql .= "  lp.rowid = ".$idProduct." AND ";
	if($idSociete != '') $sql .= "  ls.rowid = ".$idSociete ." AND ";
	if($idCommande != '') $sql .= " lc.rowid = ".$idCommande." AND ";
	//if($idFacture != '') $sql .= "  ls.rowid = ".$idFacture." AND ";
	if($id_c_product_nature  != '') $sql .= "  lp.rowid = ".$id_c_product_nature ." AND ";
	if($datestart  != '' &&  $dateend != '' ) $sql .= "   ls.date_livraison  BETWEEN '".$datestart."' AND '".$dateend."' AND ";
	if($date  != ''  ) $sql .= "   lc.date_commande = '".$date."' and ";
	if($nomenclature  != ''  ) $sql .= "   lnd2.rowid = '".$nomenclature."' and ";
	if($workstation  != ''  ) $sql .= "    lnw.fk_workstation = '".$workstation."' and ";

	//if($idCategorie != '') $sql .= "ls.rowid = ".$idCategorie." AND ";
	//if($idUser != '') $sql .= "     ls.rowid = ".$idUser." AND ";
	$sql .= " 1 = 1 ";
	$sql .= " group by lnw.rowid ";

	 //print $sql;
	print "<br>";
	 $result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($result);
			$template[] = $obj;
			$i++;
		}
	}
	return $template;


}

*/

function getDataReport($idSociete,$idCommande,$idFacture,$idProduct,$idCategorie,$idUser,$id_c_product_nature, $date, $datestart, $dateend, $workstation, $nomenclature, $dateendcommande  ,$Width1,$Width2, $Thickness1, $Thickness2,$Length1, $Length2,$SalesOrder ){
	/*

	*/

	global $db;
	$sql = " SELECT ";
	$sql.= " lp.rowid as RowidProductPrincipal, ";
	$sql.= " lp.label as label, ";
	$sql.= " lnd2.fk_product as principalid, ";
	$sql .= " ln2.title as title, ";
	$sql .= " ln2.rowid as idbom, ";
	$sql .= " lc2.ref as  Commande, ";
	$sql .= " lc2.rowid AS ORDERROWID, ";
	$sql .= " (select concat(lp2.ref , '  -  ',lp2.label) as label from ".MAIN_DB_PREFIX."product lp2 where  lc.fk_product  = lp2.rowid) as Product2, ";
	$sql .= " lc.fk_product  as idfinishedproduct, ";
	$sql .= " (select lp5.stock from ".MAIN_DB_PREFIX."product lp5 where  lc.fk_product  = lp5.rowid) as fpstock, ";
	$sql .= " lnd2.qty  as Qty, ";
	$sql .= "  ( select lcu.short_label  from ".MAIN_DB_PREFIX."product lp4  LEFT JOIN ".MAIN_DB_PREFIX."c_units lcu  ON lp4.weight_units  =lcu.scale   where lp4.rowid=lp.rowid group by lp4.rowid ) as Unidades , ";
	//$sql .= " (SELECT lw.name  from ".MAIN_DB_PREFIX."workstation lw WHERE lnw.fk_workstation = lw.rowid   ) as Workstation1, ";
	//$sql .= " lnw.rang as rango, ";
	//$sql .= " lnw.nb_hour as  hour, ";
	//$sql .= " lnw.nb_hour_prepare as hourprepare , ";
	//$sql .= " lnw.nb_hour_manufacture as hourmanufacture, ";
	$sql .= " DATE_FORMAT(lc2.date_commande, '%m-%d-%Y') as Datecomande, ";
	$sql .= " DATE_FORMAT(lc2.date_livraison, '%m-%d-%Y') as Datedeliver, ";
	//$sql .= " DATE_FORMAT(lc2.date_valid, '%m-%d-%Y') as Datedeliver, ";
	$sql .= " lc.qty as Commandederqty, ";
	$sql .= "  (select concat(lp3.ref , '  -  ',lp3.label) as label from ".MAIN_DB_PREFIX."product lp3 where lnd2.fk_product= lp3.rowid LIMIT 1) as RAWMATERIAL, ";
	$sql .= "  ( select lcu.short_label  from ".MAIN_DB_PREFIX."product lp4  LEFT JOIN ".MAIN_DB_PREFIX."c_units lcu  ON lp4.weight_units  =lcu.scale   where lp4.rowid=lp.rowid group by lp4.rowid ) as Unidades , ";
	$sql .= "  (select lcu.label  from ".MAIN_DB_PREFIX."product_extrafields lpe left join ".MAIN_DB_PREFIX."c_units lcu on lpe.um = lcu.rowid  where lpe.fk_object  = lnd2.fk_product) as um, ";
	$sql .= "  (select lcu.label  from ".MAIN_DB_PREFIX."product_extrafields lpe left join ".MAIN_DB_PREFIX."c_units lcu on lpe.uma = lcu.rowid  where lpe.fk_object   =  lnd2.fk_product) as uma, ";
	$sql .= " (qty_reference * lnd2.qty ) as  qtyneeded , ";
	//$sql .= " (select lps.reel  from ".MAIN_DB_PREFIX."product_stock lps   where  lps.fk_product =  lnd2.fk_product limit 1) as rmstock, ";
	$sql .= " (select lps.reel  from ".MAIN_DB_PREFIX."product_stock lps   where  lps.fk_product =  lp.rowid) as rmstock, ";
	$sql .= " (select delivery_time_days from ".MAIN_DB_PREFIX."product_fournisseur_price  where fk_product  =  lnd2.fk_product LIMIT 1) as leadtime, ";
	$sql .= " ((lc.qty *lc.price) -(lc.qty  * lc.buy_price_ht ) ) as margen, ";
	$sql .= " s.nom   as societe, ";
	$sql .= " lao.numero   as ofnumero, ";
	$sql .= " lao.status   as ofstatus, ";
	$sql .= " lao.rowid   as ofrowid, ";
	$sql .= " ( select  lcf.ref  from ".MAIN_DB_PREFIX."element_element lee left join ".MAIN_DB_PREFIX."commande_fournisseur lcf on lee.fk_target = lcf.rowid  where lee.sourcetype  = 'commande' and lee.targettype = 'order_supplier' and lee.fk_source = lc2.rowid LIMIT 1) as  POref, ";
	$sql .= " ( select  lcf.rowid  from ".MAIN_DB_PREFIX."element_element lee left join ".MAIN_DB_PREFIX."commande_fournisseur lcf on lee.fk_target = lcf.rowid  where lee.sourcetype  = 'commande' and lee.targettype = 'order_supplier' and lee.fk_source = lc2.rowid LIMIT 1) as  POrowid, ";
	$sql .= " lao.fk_assetOf_parent   as fk_assetOf_parentrowid, ";
	$sql .= " (select lao2.numero from ".MAIN_DB_PREFIX."assetOf lao2  where lao2.rowid=lao.fk_assetOf_parent )  as fk_assetOf_parent, ";
	$sql .= " lao.temps_estime_fabrication   as temps_estime_fabrication, ";
	$sql .= " lnd2.fk_product as id_fkproduct, ";
	$sql .= " s.rowid  as societerowid, ";
	$sql .= " lp.length  as productlength, ";
	$sql .= " lp.width  as productwidth, ";
	$sql .= " lpe.wide  as productwide, ";
	$sql .= " lp.height  as productheight ";
	$sql .= " FROM ".MAIN_DB_PREFIX."product lp  ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."nomenclature ln2  ON lp.rowid = ln2.fk_object ";
	//$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."nomenclature_workstation lnw  ON ln2.rowid  = lnw.fk_nomenclature ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."nomenclaturedet lnd2  ON ln2.rowid = lnd2.fk_nomenclature ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."commandedet lc ON lp.rowid  = lc.fk_product  ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."commande lc2 ON lc.fk_commande = lc2.rowid ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe s ON lc2.fk_soc = s.rowid ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."assetOf lao ON lc2.rowid = lao.fk_commande ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_extrafields lpe ON lp.rowid = lpe.fk_object ";
	$sql .= " WHERE ";
	if($idProduct != '') $sql .= "  lp.rowid = ".$idProduct." AND ";
	if($idSociete != '') $sql .= "  s.rowid = ".$idSociete ." AND ";
	if($idCommande != '') $sql .= " lc2.rowid = ".$idCommande." AND ";
	//if($idFacture != '') $sql .= "  ls.rowid = ".$idFacture." AND ";
	if($id_c_product_nature  != '') $sql .= "  lp.rowid = ".$id_c_product_nature ." AND ";
	if($datestart  != '' &&  $dateend != '' ){

		$sql .= "   lc2.date_livraison  BETWEEN '".$datestart."'  AND '".$dateend."' AND ";
	}
	if($date  != '' &&  $dateendcommande != '' ) {

		$sql .= "   lc2.date_commande  BETWEEN '".$date."' AND '".$dateendcommande."' AND ";
	}
//$Width1,$Width2, $Thickness1, $Thickness2,$Length1, $Length2
	if(($Width1  ==  $Width2) and ($Width1  != '')  and $Width1  != 'both'  ){
		$sql .= " lpe.wide  =". $Width1." and ";
	}elseif(($Width1  != '' AND  $Width2  != '') and ($Width2  != 'both' ||$Width1  != 'both' )) {
		$sql .= " lpe.wide   BETWEEN   ".$Width1." and   ".$Width2." and ";
	}elseif( $Width1  == 'both'  or  $Width2  == 'both')
		$sql .= " lpe.wide  in (4.00, 8.00) and ";
	if(($Thickness1  ==  $Thickness2) AND  $Thickness2  != ''){
		$sql .= " lp.height  =". $Thickness1." and ";
	}elseif($Thickness1  != '' AND  $Thickness2  != ''  ){
		$sql .= " lp.height  BETWEEN  ".$Thickness1." and  ".$Thickness2." and ";
	}

	if($SalesOrder  >0 ){
		$sql .= "  lc2.rowid  in(". $SalesOrder.") and ";
	}

	if(($Length1  ==  $Length2) AND  $Length2  != ''){
		$sql .= " lp.length  =". $Length1." and ";
	}elseif($Length1  != '' AND  $Length2  != ''  ){
		$sql .= " lp.length BETWEEN  ".$Length1." and  ".$Length2." and ";
	}


	//if($idCategorie != '') $sql .= "ls.rowid = ".$idCategorie." AND ";
	//if($idUser != '') $sql .= "     ls.rowid = ".$idUser." AND ";
	$sql .= " 1 = 1 and lc2.fk_statut = 1";
	//$sql .= " GROUP  by lc2.rowid, lnd2.fk_product ";
	$sql .= " order by lc2.date_valid asc ";
	//$sql .= " group by lc.rowid, lnw.rowid";
	//$sql .= " group by lp.rowid";
	//$sql .= " group by lc.fk_product ";


	 //print $sql;
	print "<br>";
	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($result);
			$template[] = $obj;
			$i++;
		}
	}
	return $template;


}







function geSchedulingReport($ArrayProducts){
	/*

	*/

	global $db;
	$sql = " SELECT ";
	$sql.= " lp.rowid as RowidProductPrincipal, ";
	$sql.= " lp.label as label, ";
	$sql.= " lnd2.fk_product as principalid, ";
	$sql .= " ln2.title as title, ";
	$sql .= " ln2.rowid as idbom, ";
	$sql .= " lc2.ref as  Commande, ";
	$sql .= " lc2.rowid AS ORDERROWID, ";
	$sql .= " (select concat(lp2.ref , '  -  ',lp2.label) as label  from ".MAIN_DB_PREFIX."product lp2 where  lc.fk_product  = lp2.rowid limit 1) as Product2, ";
	$sql .= " lc.fk_product  as idfinishedproduct, ";
	$sql .= " (select lp5.stock from ".MAIN_DB_PREFIX."product lp5 where  lc.fk_product  = lp5.rowid limit 1) as fpstock, ";
	$sql .= " lnd2.qty  as Qty, ";
	$sql .= "  ( select lcu.short_label  from ".MAIN_DB_PREFIX."product lp4  LEFT JOIN ".MAIN_DB_PREFIX."c_units lcu  ON lp4.weight_units  =lcu.scale   where lp4.rowid=lp.rowid group by lp4.rowid limit 1) as Unidades , ";
	//$sql .= " (SELECT lw.name  from ".MAIN_DB_PREFIX."workstation lw WHERE lnw.fk_workstation = lw.rowid   ) as Workstation1, ";
	//$sql .= " lnw.rang as rango, ";
	//$sql .= " lnw.nb_hour as  hour, ";
	//$sql .= " lnw.nb_hour_prepare as hourprepare , ";
	//$sql .= " lnw.nb_hour_manufacture as hourmanufacture, ";
	$sql .= " DATE_FORMAT(lc2.date_commande, '%m-%d-%Y') as Datecomande, ";
	$sql .= " DATE_FORMAT(lc2.date_valid, '%m-%d-%Y') as Datedeliver, ";
	$sql .= " lc.qty as Commandederqty, ";
	$sql .= "  (select lp3.label from ".MAIN_DB_PREFIX."product lp3 where lnd2.fk_product= lp3.rowid limit 1) as RAWMATERIAL, ";
	$sql .= "  ( select lcu.short_label  from ".MAIN_DB_PREFIX."product lp4  LEFT JOIN ".MAIN_DB_PREFIX."c_units lcu  ON lp4.weight_units  =lcu.scale   where lp4.rowid=lp.rowid group by lp4.rowid limit 1) as Unidades , ";
	$sql .= "  (select lcu.label  from ".MAIN_DB_PREFIX."product_extrafields lpe left join ".MAIN_DB_PREFIX."c_units lcu on lpe.um = lcu.rowid  where lpe.fk_object  = lnd2.fk_product limit 1 ) as um, ";
	$sql .= "  (select lcu.label  from ".MAIN_DB_PREFIX."product_extrafields lpe left join ".MAIN_DB_PREFIX."c_units lcu on lpe.uma = lcu.rowid  where lpe.fk_object   =  lnd2.fk_product limit 1 ) as uma, ";
	$sql .= " (qty_reference * lnd2.qty ) as  qtyneeded , ";
	$sql .= " (select lps.reel  from ".MAIN_DB_PREFIX."product_stock lps   where  lps.fk_product =  lnd2.fk_product limit 1) as rmstock, ";
	$sql .= " (select delivery_time_days from ".MAIN_DB_PREFIX."product_fournisseur_price  where fk_product  =  lnd2.fk_product limit 1) as leadtime, ";
	$sql .= " ((lc.qty *lc.price) -(lc.qty  * lc.buy_price_ht ) ) as margen, ";
	$sql .= " s.nom   as societe, ";
	$sql .= " lao.numero   as ofnumero, ";
	$sql .= " lao.status   as ofstatus, ";
	$sql .= " lao.rowid   as ofrowid, ";
	$sql .= " ( select  lcf.ref  from ".MAIN_DB_PREFIX."element_element lee left join ".MAIN_DB_PREFIX."commande_fournisseur lcf on lee.fk_target = lcf.rowid  where lee.sourcetype  = 'commande' and lee.targettype = 'order_supplier' and lee.fk_source = lc2.rowid LIMIT 1) as  POref, ";
	$sql .= " ( select  lcf.rowid  from ".MAIN_DB_PREFIX."element_element lee left join ".MAIN_DB_PREFIX."commande_fournisseur lcf on lee.fk_target = lcf.rowid  where lee.sourcetype  = 'commande' and lee.targettype = 'order_supplier' and lee.fk_source = lc2.rowid LIMIT 1) as  POrowid, ";
	$sql .= " lao.fk_assetOf_parent   as fk_assetOf_parentrowid, ";
	$sql .= " (select lao2.numero from ".MAIN_DB_PREFIX."assetOf lao2  where lao2.rowid=lao.fk_assetOf_parent limit 1)  as fk_assetOf_parent, ";
	$sql .= " lao.temps_estime_fabrication   as temps_estime_fabrication, ";
	$sql .= " lnd2.fk_product as id_fkproduct, ";
	$sql .= " s.rowid  as societerowid ";
	$sql .= " FROM ".MAIN_DB_PREFIX."product lp  ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."nomenclature ln2  ON lp.rowid = ln2.fk_object ";
	//$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."nomenclature_workstation lnw  ON ln2.rowid  = lnw.fk_nomenclature ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."nomenclaturedet lnd2  ON ln2.rowid = lnd2.fk_nomenclature ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."commandedet lc ON lp.rowid  = lc.fk_product  ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."commande lc2 ON lc.fk_commande = lc2.rowid ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe s ON lc2.fk_soc = s.rowid ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."assetOf lao ON lc2.rowid = lao.fk_commande ";
	$sql .= " WHERE ";

	if($ArrayProducts != '') $sql .= " lao.rowid in (".$ArrayProducts.")  ";
	//if($idFacture != '') $sql .= "  ls.rowid = ".$idFacture." AND ";


	//if($idCategorie != '') $sql .= "ls.rowid = ".$idCategorie." AND ";
	//if($idUser != '') $sql .= "     ls.rowid = ".$idUser." AND ";
	//$sql .= " 1 = 1 and lc2.fk_statut = 1";
	//$sql .= " order by lc2.date_valid asc";
	//$sql .= " group by lc.rowid, lnw.rowid";
	//$sql .= " group by lp.rowid";

	//print $sql;
	print "<br>";
	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($result);
			$template[] = $obj;
			$i++;
		}
	}
	return $template;


}

function getNomenclature($id_product){

	global $db;
	$sql = " SELECT rowid FROM ".MAIN_DB_PREFIX."nomenclature WHERE fk_object= ".$id_product." AND object_type='product' ";
	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($result);
			$template[] = $obj->rowid;
			$i++;
		}
	}
	return( $template[0]);

}//REGRESA pos de template


function getOptionsCO($cos){

	global $db;
	$num=count($cos);
	$valores=null;
	for($a=0;$a<$num;$a++){
		$valores .= '<option value="'.$cos[$a]->id.'"> '.$cos[$a]->label.' </option>';
	}
	return $valores;

}
function getCO(){
	global $db;
	$sql  =" select c.rowid as id, concat(c.ref, ' - ', s.nom, ' - ',  IFNULL(c.ref_client, '')) as label  ";
	$sql .=" from ".MAIN_DB_PREFIX."commande as c ";
	$sql .=" left join llxas_societe as s on c.fk_soc = s.rowid ";
	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($result);
			$template[] = $obj;
			$i++;
		}
	}

	return $template;

}

function getSubproducts($nomenclature){

	global $db;

	$sql = " select lp.label, lps.reel, lp.rowid ";
	$sql .= " from " . MAIN_DB_PREFIX . "nomenclaturedet ndet ";
	$sql .= " left join " . MAIN_DB_PREFIX . "nomenclature ln2 on ndet.fk_product = ln2.fk_object ";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "product lp on ndet.fk_product = lp.rowid ";
	$sql .= " left join " . MAIN_DB_PREFIX . "product_stock lps on lp.rowid = lps.fk_product ";
	$sql .= " where fk_nomenclature =".$nomenclature;


	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($result);
			$template[] = $obj;
			$i++;
		}
	}
	$table='<table class="tg">';
	$table .= '<tbody>';

	for($a=0; $a<$num; $a++) {
		//$template[$a]->label

		$as .= '<a href="'.DOL_URL_ROOT.'product/card.php?id='.$template[$a]->rowid.'" target="blank">'.$template[$a]->label.'</a> - '.$template[$a]->reel.'<br>';
	}
		return $as;

}//REGRESA pos de template

function getWorkstation($nomenclature){

	global $db;

	$sql = " select lw.name, lw.rowid  ";
	$sql .= " from " . MAIN_DB_PREFIX . "nomenclature_workstation lnw ";
	$sql .= " left join " . MAIN_DB_PREFIX . "workstation lw on lnw.fk_workstation =lw.rowid  ";
	$sql .= " where fk_nomenclature =".$nomenclature;


	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($result);
			$template[] = $obj;
			$i++;
		}
	}
	$table='<table class="tg">';
	$table .= '<tbody>';

	for($a=0; $a<$num; $a++) {
		//$template[$a]->label

		$as .= '<a href="'.DOL_URL_ROOT.'/custom/workstationatm/workstation.php?action=view&id='.$template[$a]->rowid.'" target="blank">'.$template[$a]->name.'</a> <br>';
	}
	return $as;

}//REGRESA pos de template

function getProducts1($id){

	global $db;


	$sql = " select label ";
	$sql .= " from " . MAIN_DB_PREFIX . "product lnw ";
	$sql .= " where rowid =".$id;


	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($result);
			$template[] = $obj;
			$i++;
		}
	}


	for($a=0; $a<$num; $a++) {
		//$template[$a]->label

		$as .= ''.$template[$a]->label.'<br>';
	}
	return $as;


}//REGRESA pos de template


function getProductPO($idproduct, $refcommande){

	global $db;


	$sql = " select * ";
	$sql .= " from " . MAIN_DB_PREFIX . "commande_fournisseurdet lcf ";
	$sql .= " where  fk_product =".$idproduct;
	$sql .= " and fk_commande =".$refcommande;


	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($result);
			$template[] = $obj;
			$i++;
		}
	}


	return $template;


}//REGRESA pos de template


function getFecha($date)
{
	// floatval(str_replace(",", "", $dataCommandeFor[$datacomm]));
	$nowarray = dol_getdate($date, true);
	$day = $nowarray['mday'];
	$month = $nowarray['mon'];
	$year = $nowarray['year'];
	$hours = $nowarray['hours'];
	$minutes = $nowarray['minutes'];
	$seconds = $nowarray['seconds'];
	$date_day = $day . '-' . $month . '-' . $year;
	$date_day = $year . '' . $month . '' . $day;
	$date_ref = $year . '' . $month;
	$date_insert = $year . '-' . $month . '-' . $day;
	$dates = array($date_day, $date_ref, $date_insert);

	return $dates;


}

function getTotalTable($table){

	global $db;


	$sql = " select count(rowid) as id  ";
	$sql .= " from " . MAIN_DB_PREFIX . "".$table;



	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($result);
			$template[] = $obj;
			$i++;
		}
	}


	return $template;


}//REGRESA total of



function getworkstationOF($idof){

	global $db;
/*	$sql = " select * ";
	$sql .= " FROM " . MAIN_DB_PREFIX . "workstation WHERE rowid ";
	$sql .= "  IN (SELECT fk_asset_workstation FROM " . MAIN_DB_PREFIX . "asset_workstation_of WHERE fk_assetOf = ".$idof.")";*/


 	$sql = "  select lw.* from llxas_nomenclature ln2 ";
 	$sql .= "  left join llxas_nomenclaturedet ln3 on ln2.rowid = ln3.fk_nomenclature ";
 	$sql .= "  left join   llxas_nomenclature_workstation lnw  on ln2.rowid  = lnw.fk_nomenclature ";
 	$sql .= " left join llxas_workstation lw on lnw.fk_workstation = lw.rowid   ";
	$sql .= "  where ln2.fk_object = ".$idof." and ln2.object_type = 'product'  and ln2.is_default =1 ";
	$sql .= "  GROUP by lw.rowid ";


	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($result);
			$template[] = $obj;
			$i++;
		}
	}


	return $template;


}//REGRESA total of


function getNomenclarureProduct($id){

	global $db;
	/*

  left join  llxas_nomenclaturedet lnn2 on lnn.rowid = lnn2.fk_nomenclature
  where lnn.fk_object = 1 and object_type = 'product'
	*/
	$sql  = " select lnn2.fk_product , lnn2.rang , lnn2.qty , lnn2.price, lnn2.buying_price ";
	$sql .= " from " . MAIN_DB_PREFIX . "nomenclature  lnn ";
	$sql .= " left join  " . MAIN_DB_PREFIX . "nomenclaturedet lnn2 on lnn.rowid = lnn2.fk_nomenclature ";
	$sql .= " where lnn.fk_object = ".$id." and object_type = 'product' ";



	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($result);
			$template[] = $obj;
			$i++;
		}
	}


	return $template;


}//REGRESA total of


function color_random() {
	return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
}
