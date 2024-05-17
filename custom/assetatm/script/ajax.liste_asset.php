<?php
require("../config.php");
dol_include_once('/assetatm/class/asset.class.php');
dol_include_once('/core/lib/product.lib.php');

$langs->load('other');

/*if(isset($_REQUEST['fk_product'])){
	$id = $_REQUEST['fk_product'];
}
else {
	return false;
}*/
if(isset($_REQUEST['fk_soc'])){
	$socid = $_REQUEST['fk_soc'];
}
else {
	return false;
}

$PDOdb = new TPDOdb;
$Tres = array();

$sql = "SELECT a.rowid, a.serial_number, a.lot_number, a.contenancereel_value, a.contenancereel_units, p.ref as pref
		FROM ".MAIN_DB_PREFIX."assetatm as a
	    LEFT JOIN ".MAIN_DB_PREFIX."product as p ON p.rowid=a.fk_product
		WHERE a.fk_soc = ".$socid."
		ORDER BY a.contenancereel_value DESC";
		
$PDOdb->Execute($sql);

while($PDOdb->Get_line()){
	$label = $PDOdb->Get_field('serial_number');
	$ref= $PDOdb->Get_field('pref');
	if (!empty($ref)) {
		$label .= ' - ' . $ref;
	}
	/*$label.= " / Lot ".$PDOdb->Get_field('lot_number');
	$label.= " / ".number_format($PDOdb->Get_field('contenancereel_value'),2,",","")." ".measuring_units_string($PDOdb->Get_field('contenancereel_units'),"weight");*/
	
	$Tres[] = array(
		"id" => $PDOdb->Get_field('rowid')
		,"label" => $label
	);
}

echo json_encode($Tres);
