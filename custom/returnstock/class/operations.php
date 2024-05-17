<?php
function getall($table, $filter= '1=1'  ) {

	global $db, $dolibarr_main_url_root;
	//$A=getProductNull($object);


	$sql  = " SELECT * FROM ".MAIN_DB_PREFIX."".$table;
	if($filter != '1=1' ) $sql .= " where ".$filter;

	$resqle = $db->query($sql);

	if ($resqle) {
		$num = $db->num_rows($resqle);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($resqle);
			$template[] = $obj;
			$i++;
		}
	}


	return $template;

}



function buscarShipment($descuentoStock, $origen,$id ) {

	global $db, $dolibarr_main_url_root;
	//$A=getProductNull($object);


	$SQL1= "select * from ".MAIN_DB_PREFIX."element_element as lee where sourcetype='".$descuentoStock."'and targettype='".$origen."'  and fk_target =".$id;
	$resqle = $db->query($SQL1);


	if ($resqle) {
		$num = $db->num_rows($resqle);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($resqle);
			$template[] = $obj;
			$i++;
		}
	}


	return $template;

}
