<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 * 
 */
    if(!defined('INC_FROM_DOLIBARR')) {
        define('INC_FROM_CRON_SCRIPT', true);
        require('../config.php');
        $ATMdb=new TPDOdb;
        $ATMdb->debug=true;
    }
    else{
        $ATMdb=new TPDOdb;
        
    }
	
	global $db;
	
	dol_include_once('/assetatm/class/asset.class.php');
	
    if(!class_exists('modAssetatm')) dol_include_once('/assetatm/core/modules/modassetatm.class.php');
    Tools::setVersion($db, 'modAssetatm');
	
	$o=new TAsset_type;
	$o->init_db_by_vars($ATMdb);
	
	$o=new TAssetLink;
	$o->init_db_by_vars($ATMdb);
	
	$o=new TAsset_field;
	$o->init_db_by_vars($ATMdb);
	
	$o=new TAssetLot;
	$o->init_db_by_vars($ATMdb);

	$o=new TAsset;
	$o->init_db_by_vars($ATMdb);
	
	$o=new TAssetCommandedet;
	$o->init_db_by_vars($ATMdb);
	
	$o=new TAssetFacturedet;
	$o->init_db_by_vars($ATMdb);
	
	$o=new TAssetStock;
	$o->init_db_by_vars($ATMdb);
	
	
	$o=new TAssetPropaldet;
	$o->init_db_by_vars($ATMdb);
	
	$o=new TAssetPropal;
	$o->init_db_by_vars($ATMdb);
	
	$o=new TAssetCommande;
	$o->init_db_by_vars($ATMdb);
	
	$o=new TAssetFacture;
	$o->init_db_by_vars($ATMdb);
	
	
	//Obligatoire pour que la fonctionnalité d'import standard fonctionne
	$ATMdb->Execute("ALTER TABLE ".MAIN_DB_PREFIX."assetatm CHANGE rowid rowid INT(11) AUTO_INCREMENT");
	