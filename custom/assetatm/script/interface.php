<?php

define('INC_FROM_CRON_SCRIPT', true);
if (!defined("NOCSRFCHECK")) define('NOCSRFCHECK', 1);
if (! defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', 1);
}

set_time_limit(0);
require('../config.php');
require('../lib/asset.lib.php');
require('../class/asset.class.php');

//Interface qui renvoie les emprunts de ressources d'un utilisateur
$PDOdb=new TPDOdb;

$get = __get('get','emprunt');

traite_get($PDOdb, $get);

function traite_get(&$PDOdb, $case) {
	switch (strtolower($case)) {
        case 'autocomplete':
            __out(_autocomplete($PDOdb,GETPOST('fieldcode'),GETPOST('term'),GETPOST('fk_product'),GETPOST('type_product')));
            break;
        case 'autocomplete-serial':
            __out(_autocompleteSerial($PDOdb,GETPOST('lot_number'), GETPOST('fk_product')));
            break;
		case 'measuringunits':
			__out(_measuringUnits(GETPOST('type'), GETPOST('name')), 'json');
			break;
	}
}



function _autocompleteSerial(&$PDOdb, $lot='', $fk_product=0) {
    global $conf;

    //$sql = 'SELECT DISTINCT(a.serial_number) ';
    $sql = 'SELECT a.rowid, a.serial_number, a.contenancereel_value ';
    $sql .= 'FROM '.MAIN_DB_PREFIX.'assetatm as a WHERE 1 ';

	if($conf->global->ASSET_NEGATIVE_DESTOCK) $sql .= ' AND a.contenancereel_value > 0 ';

    if ($fk_product > 0) $sql .= ' AND fk_product = '.(int) $fk_product.' ';
    if (!empty($lot)) $sql .= ' AND lot_number LIKE '.$PDOdb->quote('%'.$lot.'%').' ';

    $sql .= 'ORDER BY a.serial_number';
    //  print $sql;
    $PDOdb->Execute($sql);
    while ($PDOdb->Get_line())
    {
    	$serial = $PDOdb->Get_field('serial_number');

		/* Merci de conserver les crochets autour de l'ID et de le laisser en début de chaine
		 * je m'en sert pour matcher côté js pour retrouver facilement l'ID dans la chaîne pour le lien d'ajout
		 */
        $TResult[] = '['.$PDOdb->Get_field('rowid').'] Numéro : '.($serial ? $serial : '(vide)').', contenance actuelle : '.$PDOdb->Get_field('contenancereel_value');
    }

    $PDOdb->close();
    return $TResult;

}
//Autocomplete sur les différents champs d'une ressource
function _autocomplete(&$PDOdb,$fieldcode,$value,$fk_product=0,$type_product='NEEDED')
{
	global $conf;

	$value = trim($value);

	$sql = 'SELECT DISTINCT(al.'.$fieldcode.') ';
	$sql .= 'FROM '.MAIN_DB_PREFIX.'assetatmlot as al ';

	if($fk_product)
	{
		$sql .= 'LEFT JOIN '.MAIN_DB_PREFIX.'assetatm as a ON (a.'.$fieldcode.' = al.'.$fieldcode.' '.(($type_product == 'NEEDED' && $conf->global->ASSET_NEGATIVE_DESTOCK) ? 'AND a.contenancereel_value > 0' : '').') ';
		//var_dump($sql);
		$sql .= 'LEFT JOIN '.MAIN_DB_PREFIX.'product as p ON (p.rowid = a.fk_product) ';
	}

	if (!empty($value)) $sql .= 'WHERE al.'.$fieldcode.' LIKE '.$PDOdb->quote($value.'%').' ';

	if (!empty($value) && $fk_product && $type_product == 'NEEDED') $sql .= 'AND p.rowid = '.(int) $fk_product.' ';
	elseif ($fk_product && $type_product == 'NEEDED') $sql .= 'WHERE p.rowid = '.(int) $fk_product.' ';

	$sql .= 'ORDER BY al.'.$fieldcode;
//		print $sql;
	$PDOdb->Execute($sql);
	while ($PDOdb->Get_line())
	{
		$TResult[] = $PDOdb->Get_field($fieldcode);
	}

	$PDOdb->close();
	return $TResult;
}


function _measuringUnits($type, $name)
{
	global $db;

	require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';

	$html=new FormProduct($db);

	if($type == 'unit') return array(' unité(s)');
	else{
		if(intval(DOL_VERSION) < 10 ) {
			return array($html->load_measuring_units($name, $type, 0));
		}
		else{
			return array($html->selectMeasuringUnits($name, $type, 0));
		}
	}
}
