<?php

if(!class_exists('TObjetStd')) {
	dol_include_once('/abricot/inc.core.php');

}

class TAsset extends TObjetStd{
/*
 * Gestion des équipements
 * */

	function __construct() {
		global $langs, $conf;

		$this->set_table(MAIN_DB_PREFIX.'assetatm');
		$this->TChamps = array();
		$this->add_champs('fk_soc,fk_product,fk_societe_localisation,entity,fk_asset_type',array('type'=>'integer','index'=>true),array('min'=>0,'autoset'=>true));
		// contenancereel_value, contenance courante
		// contenance_value, contenance maximale de l'équipement
		$this->add_champs('valeur,contenancereel_value, contenance_value,point_chute', array('type'=>'float'));
		$this->add_champs('contenance_units, contenancereel_units, fk_entrepot, fk_dol_moov', array('type'=>'integer'));
		$this->add_champs('commentaire,lot_number,gestion_stock,reutilisable,status', array('type'=>'string'));
		$this->add_champs('dluo',array('type'=>'date'));
		$this->add_champs('import_key,serial_number');//Obligatoire pour que la fonctionnalité d'import standard Dolibarr fonctionne

		$this->_init_vars();

		    $this->start();

		$this->TLink=array(); // liaison document
		$this->TStock=array(); // liaison mouvement stock
		$this->error='Erreur dans objet equipement';

		$this->TGestionStock = array(
				'UNIT'=>$langs->trans('AssetStockManagementUNIT'),
				'QUANTITY'=>$langs->trans('AssetStockManagementQUANTITY')
			);

		$this->TStatus = array(
				'NOTUSED'=>'Non consommé',
				'PARTUSED'=>'Partiellement consommé',
				'USED'=>'Consommé'
			);

		$this->status = 'NOTUSED';
		$this->TField=array();
		$this->assetType=new TAsset_type;
		$this->TType = array();
		$this->TTraceability = array();

		$this->old_contenancereel = 0;
		$this->old_contenancereel_units = 0;

		$this->dluo = 0;

		$this->gestion_stock = 'UNIT';

		$this->contenancereel_value = (isset($conf->global->ASSET_CONTENANCE_REEL_VALUE)) ? $conf->global->ASSET_CONTENANCE_REEL_VALUE : 1;
		$this->contenance_value = 1;
	}

	function set_values($request)
	{
		if (isset($request['dluo']))
		{
			$this->set_date('dluo', $request['dluo']);
			unset($request['dluo']);
		}

		parent::set_values($request);
	}

	public static function set_element_element($fk_source, $sourceType, $fk_target, $targetType)
	{
		$PDOdb = new TPDOdb;//TODO connexion de trop, devrait être en paramètre

		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'element_element (fk_source, sourcetype, fk_target, targettype)';
		$sql.= ' VALUES (';
		$sql.= (int) $fk_source;
		$sql.= ', '.$PDOdb->quote($sourceType);
		$sql.= ', '.(int) $fk_target;
		$sql.= ', '.$PDOdb->quote($targetType);
		$sql.= ')';

		$PDOdb->Execute($sql);
	}

    public static function get_element_element($fk_source, $sourceType, $targetType)
    {
        $PDOdb = new TPDOdb; //TODO connexion de trop, devrait être en paramètre

        $sql = "SELECT fk_target FROM ".MAIN_DB_PREFIX."element_element
                WHERE fk_source = ".$fk_source." AND sourcetype='".$sourceType."' AND targettype='".$targetType."'";
        //echo $sql.'<br>';
        return TRequeteCore::_get_id_by_sql($PDOdb, $sql, 'fk_target');

    }

	public static function del_element_element(&$PDOdb, $fk_source, $fk_target, $targetType)
	{
		$sql = 'DELETE FROM '.MAIN_DB_PREFIX.'element_element WHERE fk_source = '.(int) $fk_source.' AND fk_target = '.(int) $fk_target.' AND sourcetype="TAssetOFLine" AND targettype = '.$PDOdb->quote($targetType);
		return $PDOdb->Execute($sql);
	}

	function reinit() {
		$this->rowid = 0;
		$nb=count($this->TLink);
		for($i=0;$i<$nb;$i++) {
			$this->TLink[$i]->rowid=0;
			$this->TLink[$i]->fk_asset=0;
		}
		$nb=count($this->TStock);
		for($i=0;$i<$nb;$i++) {
			$this->TStock[$i]->rowid=0;
			$this->TStock[$i]->fk_asset=0;
		}
	}

	function load(&$db, $id, $annexe=true) {
		global $conf;

		$res = parent::load($db,$id);
		if($annexe){
			$this->load_link($db);
			$this->load_stock($db);
			$this->load_asset_type($db);
		}
		//Sauvegarde de l'ancienne contenance réelle
		$this->old_contenancereel = $this->contenancereel_value;
		$this->old_contenancereel_units = $this->contenancereel_units;

		return $res;
	}

	function load_liste_type_asset(&$PDOdb){
		//chargement d'une liste de tout les types de ressources
		$temp = new TAsset_type;
		$Tab = TRequeteCore::get_id_from_what_you_want($PDOdb, MAIN_DB_PREFIX.'assetatm_type', array());

		$this->TType = array('');
		foreach($Tab as $k=>$id){
			$temp->load($PDOdb, $id);
			$this->TType[$temp->getId()] = $temp->libelle;
		}

	}

	function get_asset_type(&$PDOdb,$fk_product){

		$sql = "SELECT type_asset FROM ".MAIN_DB_PREFIX."product_extrafields WHERE fk_object = ".$fk_product;

		$PDOdb->Execute($sql);
		$PDOdb->Get_line();

		return $PDOdb->Get_field('type_asset');
	}

	function getDefaultContenance() {
        /* récupère la contenance par défaut dans le produit ou la config du type */

        return $this->assetType->getDefaultContenance($this->fk_product);


    }

	/**
	 * Renvoi l'id de l'OF si l'équipement a été fabriqué ou un tableau d'id si utilisé dans des OF
	 *
	 * @param type $PDOdb
	 * @param type $type	TO_MAKE ou NEEDED
	 * @return int | array
	 */
	function getOfId(TPDOdb &$PDOdb, $type='TO_MAKE')
	{
		$sql = 'SELECT ofl.fk_assetOf FROM '.MAIN_DB_PREFIX.'assetOf_line ofl';
		$sql.= ' INNER JOIN '.MAIN_DB_PREFIX.'element_element ee ON (ofl.rowid = ee.fk_source AND sourcetype = \'TAssetOFLine\')';
		$sql.= ' WHERE ee.targettype = \'TAsset\'';
		$sql.= ' AND ee.fk_target = '.$this->getId();
		$sql.= ' AND ofl.type = \''.$type.'\'';

		$PDOdb->Execute($sql);

		if ($type == 'TO_MAKE')
		{
			$line = $PDOdb->Get_line();

			if ($line && $line->fk_assetOf > 0) return $line->fk_assetOf;
			else return 0;
		}
		else
		{
			// NEEDED
			$TId = $PDOdb->Get_All();

			if (!empty($TId)) return $TId;
			else return array();
		}
	}

    /**
     * Renvoi l'id de l'OF si l'équipement a été fabriqué ou un tableau d'id si utilisé dans des OF
     *
     * @param type $PDOdb
     * @param type $type	TO_MAKE ou NEEDED
     * @return int | array
     */
    function getOfLine(TPDOdb &$PDOdb, $type='TO_MAKE')
    {
        dol_include_once('/of/class/ordre_fabrication_asset.class.php');

        $sql = 'SELECT ofl.rowid FROM '.MAIN_DB_PREFIX.'assetOf_line ofl';
        $sql.= ' INNER JOIN '.MAIN_DB_PREFIX.'element_element ee ON (ofl.rowid = ee.fk_source AND sourcetype = \'TAssetOFLine\')';
        $sql.= ' WHERE ee.targettype = \'TAsset\'';
        $sql.= ' AND ee.fk_target = '.$this->getId();
        $sql.= ' AND ofl.type = \''.$type.'\'';

        $PDOdb->Execute($sql);

        if ($type == 'TO_MAKE')
        {
            $line = $PDOdb->Get_line();
            $lineOf = new TAssetOFLine;
            $lineOf->load($PDOdb, $line->rowid);
            if ($lineOf && $lineOf->rowid > 0) return $lineOf;
            else return 0;
        }

    }

	/**
	 * Renvoi l'id de la commande fournisseur s'il en existe une
	 *
	 * @param type $PDOdb
	 * @return int
	 */
	function getCommandeFournId(&$PDOdb)
	{
		$sql = 'SELECT cfd.fk_commande FROM '.MAIN_DB_PREFIX.'commande_fournisseurdet cfd';
		$sql.= ' INNER JOIN '.MAIN_DB_PREFIX.'commande_fournisseurdet_asset cfda ON (cfd.rowid = cfda.fk_commandedet)';
		$sql.= ' INNER JOIN '.$this->get_table().' as a ON (cfda.serial_number = a.serial_number)';
		$sql.= ' WHERE a.rowid = '.$this->getId();
//		$sql.= ' AND cfda.lot_number = \''.$this->lot_number.'\''; // le check sur num lot me parait pas utile

		$PDOdb->Execute($sql);
		$line = $PDOdb->Get_line();

		if ($line && $line->fk_commande > 0) return $line->fk_commande;
		else return 0;
	}

	function load_asset_type(&$PDOdb) {
		//on prend le type de ressource associé
		$this->assetType->load($PDOdb, $this->fk_asset_type);

        if(empty($this->contenance_value) || $this->getId() == 0) { // On init car c'est le tout début
           // $this->contenancereel_value=$this->assetType->contenancereel_value;
            $this->contenance_value = $this->assetType->contenance_value;
            $this->contenancereel_value = $this->getDefaultContenance();
            $this->measuring_units = $this->assetType->measuring_units;
            $this->gestion_stock = $this->assetType->gestion_stock;
            $this->reutilisable = $this->assetType->reutilisable;
            $this->perishable = $this->assetType->perishable;

            if($this->perishable && !empty($this->assetType->default_dluo_nb) && empty($this->dluo)) {
                $this->dluo = strtotime('+'.$this->assetType->default_dluo);
            }

        }
        //on charge les champs associés au type.
		$this->init_variables($PDOdb);
	}

	function init_variables(&$PDOdb) {
		foreach($this->assetType->TField as $field) {

			$type = $field->type;
			$param=array('type'=>'string','length'=>30);
			if($type == 'chaine100')$param=array('type'=>'string','length'=>100);
			else if($type == 'chaine255')$param=array('type'=>'string','length'=>255);
			else if($type == 'text')$param=array('type'=>'text');
			else if($type == 'float')$param=array('type'=>'float');
			else if($type == 'entier')$param=array('type'=>'integer');
			else if($type == 'date')$param=array('type'=>'date');
			else if($type == 'checkbox' || $type == 'liste')$param=array('type'=>'string', 'length'=>30, 'index'=>true);

			$this->add_champs($field->code, $param);
		}
		//$this->_init_vars();
		$this->init_db_by_vars($PDOdb); //TODO c'est a chier

		if($this->getId()>0) parent::load($PDOdb, $this->getId());
	}

	function save(&$PDOdb, $user='', $description = "", $qty=0, $destock_dolibarr_only = false, $fk_prod_to_destock=0, $no_destock_dolibarr = false,$fk_entrepot=0,$add_only_qty_to_contenancereel=false, $subprice = 0)
	{
		global $conf,$langs;

		if(empty($description)) $description = $langs->trans('AssetManualEdit');

		if(empty($this->entity)) $this->entity = $conf->entity;
		if(!empty($qty)) $this->qty = $qty;
		if (!$fk_entrepot) $fk_entrepot = $this->fk_entrepot;


		if(!$destock_dolibarr_only)
		{
			if(empty($this->serial_number))
			{
				$this->serial_number = $this->getNextValue($PDOdb); // TODO à vérifier car il semblerait que le mask se génère tjr comme s'il été le 1er (mask : P01-{00000})
			}
            $idasset = parent::save($PDOdb);

			$this->save_link($PDOdb);
			$this->addLotNumber($PDOdb);

			// Qty en paramètre est vide, on vérifie si le contenu du flacon a été modifié
			if(empty($qty) && $this->contenancereel_value * pow(10, $this->contenancereel_units) != $this->old_contenancereel * pow(10,$this->old_contenancereel_units))
			{
				$qtyKg = $this->contenancereel_value * pow(10, $this->contenancereel_units) - $this->old_contenancereel * pow(10,$this->old_contenancereel_units);
				$qty = $qtyKg * pow(10, -$this->contenancereel_units);
			}
			else if(!empty($qty))
			{
				// " += " car il faut pouvoir mettre à jour la quantité par rapport à un update (tk7584 : update d'un TO_MAKE alors que l'OF est au statut "Terminé"), mais cela nécessite lors de la création de l'asset que la contenant ce soit à 0
				if ($add_only_qty_to_contenancereel) $this->contenancereel_value += $qty;
				else $this->contenancereel_value = $this->contenancereel_value + $qty;
				$idasset = parent::save($PDOdb);
			}
		}
		// Enregistrement des mouvements
		if(!empty($qty) && !$no_destock_dolibarr)
		{
			$this->addStockMouvement($PDOdb,$qty,$description, $destock_dolibarr_only, $fk_prod_to_destock, $fk_entrepot, $subprice);
		}

		//Spécifique Nomadic
		if(@$conf->clinomadic->enabled){ //TODO Et des triggers ! NON
			$this->updateGaranties();//TODO remove this shit !
		}

        return 	$idasset;
	}


	function updateGaranties(){

		//TODO MAJ garantie client et garantie fournisseur
	}

	function addStockMouvement(&$PDOdb, $qty, $description, $destock_dolibarr_only = false, $fk_prod_to_destock = 0, $fk_entrepot = 0, $subprice = 0)
	{

		$res = $this->addStockMouvementDolibarr($this->fk_product, $qty, $description, $destock_dolibarr_only, $fk_prod_to_destock, $fk_entrepot, $subprice);

		if (!$destock_dolibarr_only)
		{
			global $user;

			$stock = new TAssetStock;
			$stock->mouvement_stock($PDOdb, $user, $this->rowid, $qty, $description, $this->rowid, $res);
		}

        return $res;
	}

	function addStockMouvementDolibarr($fk_product, $qty, $description, $destock_dolibarr_only = false, $fk_prod_to_destock = 0, $fk_entrepot = 0, $subprice = 0)
	{
		global $db, $user,$conf;

		require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
		require_once DOL_DOCUMENT_ROOT.'/product/stock/class/mouvementstock.class.php';



		$mouvS = new MouvementStock($db);
		// We decrement stock of product (and sub-products)
		// We use warehouse selected for each line

		$conf->global->PRODUIT_SOUSPRODUITS = false; // Dans le cas asset il ne faut pas de destocke recurssif
		//if($fk_entrepot == 0) $fk_entrepot = $this->fk_entrepot;
		/*
		 * Si on est dans un cas où il faut seulement effectuer un mouvement de stock dolibarr,
		 * on valorise $fk_product qui n'est sinon pas disponible car il correspond à $this->fk_product,
		 * et ce dernier n'est pas disponible car on est dans un cas où l'on n'a pas pu charger l'objet Equipement,
		 * donc pas de $this->fk_product
		 */
		$fk_product = $destock_dolibarr_only ? $fk_prod_to_destock : $fk_product;

		//Dans le cas d'une gestion de stock quantitative, on divise la quantité destocké par la contenance total de l'équipement
		if($this->gestion_stock === 'QUANTITY' && $this->assetType->measuring_units != 'unit')
		{
			$product = new Product($db);
			$product->fetch($fk_product);

			//Bas oui parce que dans Dolibarr un coup on a 'size' et un coup on a 'lenght' pour la même chose....j'aime Dolibarr...
			$type_unit = ($this->assetType->measuring_units === 'size') ? 'length' : $this->assetType->measuring_units ;

			//Pour destocker dans la bonne unité, on met dans l'unité correspondant au produit
			$unite = $product->{$type_unit."_units"}-$this->contenance_units;
			$qty_product = (!empty($product->{$type_unit}) && $product->{$type_unit} > 0) ? $product->{$type_unit} : 1;

			$qty = $qty / ($qty_product * pow(10,$unite));
			$qty = round($qty,5);
		}

		if($fk_entrepot > 0)
		{
			//TODO finaliser cette partie spécifique
			//Si on destocke l'intégralité de notre équipement dès le premier mouvement
			/*if($this->destock_dolibarr_on_first_mvt && $this->contenancereel_value == $this->contenance_value){

				//Alors on passse la quantité à destocker = contenance maximum du produit
				$qty = $this->contenance_value * pow(10,$unite);*/
				$db->begin();
				if(!empty($this->origin)) $mouvS->origin = $this->origin;
				if($qty > 0) {
                	    //function reception($user, $fk_product, $entrepot_id, $qty, $price=0, $label='', $eatby='', $sellby='', $batch='')
				    $result=$mouvS->reception($user, $fk_product, $fk_entrepot, $qty, $subprice, $description,$this->dluo,'', $this->lot_number);
				} else {
				    //function livraison($user, $fk_product, $entrepot_id, $qty, $price=0, $label='', $datem='', $eatby='', $sellby='', $batch='', $id_product_batch=0)
					$result=$mouvS->livraison($user,$fk_product, $fk_entrepot, -$qty, $subprice, $description,time(),$this->dluo,'', $this->lot_number);
				}

				if($result<0) {
					$db->rollback();
				    var_dump($mouvS->errors);exit;

				}
				else {
					$db->commit();
					return $result;
				}

			//}
		}
	}

	function delete(&$PDOdb, $update_stock=false)
	{
		if ($update_stock)
		{
			$this->destockDolibarr($PDOdb);
		}

		parent::delete($PDOdb);
		$nb=count($this->TLink);
		for($i=0;$i<$nb;$i++) {
			$this->TLink[$i]->delete($PDOdb);
		}

        // Pour supprimer le mouvement de stock qui vient juste d'être créé plus haut parce qu'on s'en ... complétez comme bon vous semble
		$this->load_stock($PDOdb);

		$nb=count($this->TStock);
		for($i=0;$i<$nb;$i++) {
			$this->TStock[$i]->delete($PDOdb);
		}
	}

	function destockDolibarr(&$PDOdb)
	{
		global $db,$langs,$user;

		//Fonction save fait le taf pour les mvts de stock dolibarr
		$this->save($PDOdb, $user, $langs->trans('StockMovementAssetDeletion', $this->serial_number), -$this->contenancereel_value, false, $this->fk_product, false, $this->fk_entrepot);
	}

	function load_link(&$db) {
		$this->TLink=array();
		$Tab = $this->_get_link_id($db);

		foreach ($Tab as $i=>$id) {
			$this->TLink[$i]=new TAssetLink;
			$this->TLink[$i]->load($db, $id);
		}
	}

	function save_link(&$db) {
		$nb=count($this->TLink);
		for($i=0;$i<$nb;$i++) {
			$this->TLink[$i]->fk_asset=$this->rowid;
			$this->TLink[$i]->save($db, $id);
		}
	}

	function getLink($type_document='') {

		foreach($this->TLink as &$link) {
			if($link->type_document==$type_document) {
				return $link;
			}
		}

	}

    function getNomUrl($with_picto=true, $with_lot=false, $with_product=0) {

        $url = '<a href="'.dol_buildpath('/assetatm/fiche.php?id='.$this->getId(),1).'" />';
        if($with_picto)$url.=img_picto('', 'pictoasset.png@assetatm');
        if($with_lot && !empty($this->lot_number))$url.='[ '.$this->lot_number.' ] ';
        $url.=$this->serial_number;

		if($with_product && $this->fk_product>0) {
			global $db;

			$product = new Product($db);
			$product->fetch($this->fk_product);
			if ($with_product==2) {
				$url.=' - '.$product->ref;
			}
			$url.=' - '.$product->label;
		}

        $url.='</a>';



        return $url;

    }

	private function _get_link_id(&$db) {
		$db->Execute("SELECT rowid FROM ".$this->get_table()."_link WHERE fk_asset=".$this->rowid);
		$Tab=array();
		while($db->Get_line()) {
			$Tab[]=$db->Get_field('rowid');
		}

		return $Tab;
	}

	function add_link($fk_document, $type_document) {
		foreach($this->TLink as &$link) {
			if($link->fk_document==$fk_document && $link->type_document==$type_document) return false;
		}

		$i=count($this->TLink);
		$this->TLink[$i]=new TAssetLink;
		$this->TLink[$i]->fk_asset=$this->rowid;
		$this->TLink[$i]->fk_document=$fk_document;
		$this->TLink[$i]->type_document=$type_document;

		return $i;
	}

	function loadReference(&$db, $serial_number, $fk_product = null) {
		global $conf;

		$assetSearch = empty($conf->global->ASSET_SEARCH_PRODUCT_ON_MULTI_ENTITIES);

		$sql = "SELECT rowid FROM ".$this->get_table()." WHERE serial_number='".$serial_number."' AND entity IN(".getEntity('product',$assetSearch).')';

		if (!empty($fk_product)) $sql.= ' AND fk_product = '.$fk_product;
		$db->Execute($sql);

		if($db->Get_line()) {
			return $this->load($db, $db->Get_field('rowid'));
		}
		else {
			return false;
		}

	}

	function load_stock($db){
		$this->TStock=array();
		$Tab = $this->_get_stock_id($db);

		foreach ($Tab as $i=>$id) {
			$this->TStock[$i]=new TAssetStock;
			$this->TStock[$i]->load($db, $id);
		}
	}

	private function _get_stock_id(&$db) {
		$db->Execute("SELECT rowid FROM ".$this->get_table()."_stock WHERE fk_asset=".$this->rowid." ORDER BY date_mvt DESC, rowid DESC");
		$Tab=array();
		while($db->Get_line()) {
			$Tab[]=$db->Get_field('rowid');
		}

		return $Tab;
	}

	function getNextValue($PDOdb, $objSoc='')
	{
		dol_include_once('core/lib/functions2.lib.php');

		global $db;

		$mask = $this->assetType->masque;
		//get_next_value($db,$mask,$table,$field,$where='',$objsoc='',$date='',$mode='next', $bentityon=true)
		$ref = get_next_value($db,$mask,'assetatm','serial_number','',$objSoc,'','next');

        /*  Un mask peut etre partagé par plusieurstype d'équipement : AND fk_asset_type = '.$this->fk_asset_type */

		if ($ref == 'ErrorBadMask') $ref = '';
//var_dump($ref,$db);exit;
		return $ref;
	}

	function addLotNumber(&$PDOdb){

		global $conf;

		$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'assetatmlot WHERE lot_number = "'.$this->lot_number.'"';
		$PDOdb->Execute($sql);

		if($PDOdb->Get_line()){
			return true;
		}
		elseif(!empty($this->lot_number)){
			$lot = new TAssetLot;
			$lot->lot_number = $this->lot_number;
			$lot->entity = $conf->entity;
			$lot->save($PDOdb);
		}
	}

	//Spécifique Nomadic
	function retour_pret(&$PDOdb,$fk_entrepot){

		global $langs;

		//On remet en stock l'équipement
		$this->addStockMouvement($PDOdb, 1, $langs->trans('AssetLoanReturn').$this->serial_number,false,0,$fk_entrepot);
		$this->fk_societe_localisation = 8767;

		//Réinitialisation des dates de pret et du statut
		$this->etat = 0;
		$this->set_date('date_deb_pret', '');
		$this->set_date('date_fin_pret', '');

		$this->save($PDOdb);
	}

	function getTExpeditionId(&$PDOdb)
	{
		$sql = 'SELECT DISTINCT(ed.fk_expedition) FROM '.MAIN_DB_PREFIX.'expeditiondet ed';
		$sql.= ' INNER JOIN '.MAIN_DB_PREFIX.'expeditiondet_asset eda ON (ed.rowid = eda.fk_expeditiondet)';
		$sql.= ' WHERE eda.fk_asset = '.$this->getId();

		$PDOdb->Execute($sql);
		$TExpeditionId = $PDOdb->Get_All();

		if (!empty($TExpeditionId)) return $TExpeditionId;
		else return array();
	}

}

class TAssetLink extends TObjetStd{
/*
 * Liaison entre les équipements et les documents
 */
	function __construct() {
		$this->set_table(MAIN_DB_PREFIX.'assetatm_link');
    	$this->TChamps = array();
		$this->add_champs('fk_asset,fk_document','type=entier;');

		$this->_init_vars('type_document');

	    $this->start();

		$this->asset = new TAsset;
	}
	function load(&$db, $id, $annexe=false) {
		parent::load($db, $id);

		if($annexe){
			$this->asset->load($db, $this->fk_asset, false);
		}
	}
}


class TAssetCommandedet extends TObjetStdDolibarr{
/*
 * Liaison entre les lignes de commande et les lots
 */
	function __construct() {
		parent::set_table(MAIN_DB_PREFIX.'commandedet');
		parent::add_champs('asset_lot','type=chaine;');

		parent::_init_vars();

	    parent::start();
	}
}

class TAssetPropaldet extends TObjetStdDolibarr{
/*
 * Liaison entre les lignes de commande et les lots
 */
	function __construct() {
		parent::set_table(MAIN_DB_PREFIX.'propaldet');
		parent::add_champs('asset_lot','type=chaine;');

		parent::_init_vars();

	    parent::start();
	}
}

class TAssetFacturedet extends TObjetStdDolibarr{
/*
 * Liaison entre les lignes de facture et les lots
 */
	function __construct() {
		parent::set_table(MAIN_DB_PREFIX.'facturedet');
		parent::add_champs('asset_lot','type=chaine;');

		parent::_init_vars();

	    parent::start();
	}
}

class TAssetPropal extends TObjetStdDolibarr{
/*
 * Liaison entre les lignes de commande et les lots
 */
	function __construct() {
		parent::set_table(MAIN_DB_PREFIX.'propal');
		parent::add_champs('fk_asset','type=chaine;');

		parent::_init_vars();

	    parent::start();
	}
}

class TAssetCommande extends TObjetStdDolibarr{
/*
 * Liaison entre les lignes de commande et les lots
 */
	function __construct() {
		parent::set_table(MAIN_DB_PREFIX.'commande');
		parent::add_champs('fk_asset','type=chaine;');

		parent::_init_vars();

	    parent::start();
	}
}

class TAssetFacture extends TObjetStdDolibarr{
/*
 * Liaison entre les lignes de commande et les lots
 */
	function __construct() {
		parent::set_table(MAIN_DB_PREFIX.'facture');
		parent::add_champs('fk_asset','type=chaine;');

		parent::_init_vars();

	    parent::start();
	}
}

class TAssetStock extends TObjetStd{
/*
 * Gestion des mouvements de stock pour les équipements
 */
	function __construct() {
		parent::set_table(MAIN_DB_PREFIX.'assetatm_stock');
		parent::add_champs('fk_asset,fk_stock_mouvement','type=entier;index;');
		parent::add_champs('qty','type=float;');
		parent::add_champs('date_mvt','type=date;');
		parent::add_champs('type,lot','type=chaine;');
		parent::add_champs('source,user,weight_units','type=entier;');

		parent::_init_vars();

	    parent::start();
	}

	//Création d'une nouvelle entrée en stock
	function mouvement_stock(&$PDOdb,$user,$fk_asset,$qty,$type,$id_source, $fk_dol_moov=0)
	{
	    global $db;
	    
		$asset = new TAsset;
		$asset->load($PDOdb, $fk_asset);

		$this->fk_asset = $fk_asset;
		$this->qty = $qty;
		$this->date_mvt = time();
		$this->type = $type;
		$this->source = $id_source;
		$this->lot = $asset->lot_number;
		$this->user = $user->id;
		$this->weight_units = $asset->contenancereel_units;
		$this->fk_stock_mouvement = $fk_dol_moov;

		if (!empty($fk_dol_moov) && $fk_dol_moov > 0)
		{
			if(!empty($asset->valeur)) {
				$sql = "UPDATE ".MAIN_DB_PREFIX."stock_mouvement SET price='".$asset->valeur."' WHERE rowid=".$fk_dol_moov;
				$PDOdb->Execute($sql);
			}
		    $sql = "UPDATE ".MAIN_DB_PREFIX."assetatm as a SET a.fk_dol_moov = ".$fk_dol_moov." WHERE a.rowid = ".$fk_asset;
			$PDOdb->Execute($sql);
		}

		$this->save($PDOdb);
	}

	//Récupère la quantité de la dernière entrée en stock
	function get_last_mouvement(&$PDOdb,$fk_asset){
		$sql = "SELECT qty FROM ".MAIN_DB_PREFIX."assetatm_stock WHERE fk_asset = ".$fk_asset." ORDER BY rowid DESC LIMIT 1";
		$PDOdb->Execute($sql);
		if($PDOdb->Get_line())
			return $PDOdb->Get_field("qty");
		else
			return "error";
	}
}

class TAsset_type extends TObjetStd {

	function __construct() { /* declaration */
		global $langs;

		parent::set_table(MAIN_DB_PREFIX.'assetatm_type');
		parent::add_champs('libelle,code,reutilisable,masque,gestion_stock,measuring_units','type=chaine;');
		parent::add_champs('entity','type=entier;index;');
		parent::add_champs('contenance_value, contenancereel_value, point_chute', 'type=float;');
		parent::add_champs('contenance_units, contenancereel_units,cumulate,perishable', 'type=entier;');
		parent::add_champs('supprimable','type=entier;');
		parent::add_champs('default_dluo',array('length'=>20));

		parent::_init_vars();
		parent::start();
		$this->TField=array();
		$this->TType=array(
				'chaine'=>$langs->trans('AssetTypeFieldType_chaine')
				,'chaine100'=>$langs->trans('AssetTypeFieldType_chaine100')
				,'chaine255'=>$langs->trans('AssetTypeFieldType_chaine255')
				,'text'=>$langs->trans('AssetTypeFieldType_text')
				,'entier'=>$langs->trans('AssetTypeFieldType_entier')
				,'float'=>$langs->trans('AssetTypeFieldType_float')
				,'liste'=>$langs->trans('AssetTypeFieldType_liste')
				,'date'=>$langs->trans('AssetTypeFieldType_date')
				,'checkbox'=>$langs->trans('AssetTypeFieldType_checkbox')
				,'sellist'=>$langs->trans('AssetTypeFieldType_sellist')
		);

		$this->TDefaultDluoUnit=array(
		    'day'=>$langs->trans('Days')
		    ,'month'=>$langs->trans('Months')
		    ,'year'=>$langs->trans('Years')

		);


		$this->TGestionStock = array(
			'UNIT'=>$langs->trans('AssetStockManagementUNIT')
			,'QUANTITY'=>$langs->trans('AssetStockManagementQUANTITY')
		);

		$this->default_dluo_nb = 3;
		$this->default_dluo_unit = 'month' ;
		$this->default_dluo = $this->default_dluo_nb.' '.$this->default_dluo_unit;

		$this->measuring_units = 'unit';

		$this->gestion_stock = 'UNIT';
	}

	function load_by_code(&$PDOdb, $code){
		$sqlReq="SELECT rowid FROM ".MAIN_DB_PREFIX."assetatm_type WHERE code='".$code."'";
		$PDOdb->Execute($sqlReq);

		if ($PDOdb->Get_line()) {
			$this->load($PDOdb, $PDOdb->Get_field('rowid'));
			return true;
		}
		return false;
	}

    function load_by_fk_product(&$PDOdb, $fk_product) {

        $sql = 'SELECT type_asset FROM '.MAIN_DB_PREFIX.'product_extrafields WHERE fk_object = '.(int) $fk_product;
        $PDOdb->Execute($sql);
        if($PDOdb->Get_line()) {
            $fk_asset_type = $PDOdb->Get_field('type_asset');
            return $this->load($PDOdb, $fk_asset_type);
        }
        else {
            return false;
        }

    }

	public static function getIsCumulate(&$PDOdb, $fk_product)
	{
		$assetType = new TAsset_type;
		if($assetType->load_by_fk_product($PDOdb, $fk_product)) {
		  return (int) $assetType->cumulate;
		}

        return false;
	}

	function getDefaultContenance($fk_product=0) {
        /* récupère la contenance par défaut dans le produit ou la config du type */
        global $db, $conf;

        $unite = $this->measuring_units;

        if($unite=='unit') {
            if(isset($conf->global->ASSET_CONTENANCE_REEL_VALUE)){
                return $conf->global->ASSET_CONTENANCE_REEL_VALUE;
            } else {
                return 1;
            }
        }
        elseif($fk_product>0) {

            dol_include_once('/product/class/product.class.php');

            $product = new Product($db);
            $product->fetch($fk_product);

            if($unite=='size') $contenance = $product->length;
            elseif(isset($product->{$unite})) $contenance = $product->{$unite}; // TODO prendre en compte l'unité car j'ai la flemme
            else $contenance = 0;
        }

        if(empty($contenance)) $contenance = $this->contenancereel_value;

        return $contenance;
    }
    public static function getIsUnit(&$PDOdb, $fk_product)
    {
    	$assetType = new TAsset_type;
    	if($assetType->load_by_fk_product($PDOdb, $fk_product)) {

    		return ($assetType->gestion_stock == 'UNIT');
    	}

    	return false;
    }

	public static function getIsPerishable(&$PDOdb, $fk_product)
	{
		$assetType = new TAsset_type;
        if($assetType->load_by_fk_product($PDOdb, $fk_product)) {

		  return (int) $assetType->perishable;
        }

        return false;
	}

 	/**
	 * Attribut les champs directement, pour créer les types par défauts par exemple.
	 */
	function chargement(&$db, $libelle, $code, $supprimable){
		$this->load_by_code($db, $code);
		$this->libelle = $libelle;
		$this->code = $code;
		$this->supprimable = $supprimable;
		$this->save($db);
	}

	function load(&$PDOdb, $id,$annexe = true) {
		$res= parent::load($PDOdb, $id);

		if($annexe)$this->load_field($PDOdb);

		list($this->default_dluo_nb, $this->default_dluo_unit) = explode(' ',$this->default_dluo);

        return $res;
	}

	/**
	 * Renvoie true si ce type est utilisé par une des ressources.
	 */
	function isUsedByAsset(&$PDOdb){
		$Tab = TRequeteCore::get_id_from_what_you_want($PDOdb, MAIN_DB_PREFIX.'assetatm', array('fk_asset_type'=>$this->getId()));
		if (count($Tab)>0) return true;
		return false;

	}

	function load_field(&$PDOdb) {
		global $conf;
		$sqlReq="SELECT rowid FROM ".MAIN_DB_PREFIX."assetatm_field WHERE fk_asset_type=".$this->getId()." ORDER BY ordre ASC;";
		$PDOdb->Execute($sqlReq);

		$Tab = array();
		while($PDOdb->Get_line()) {
			$Tab[]= $PDOdb->Get_field('rowid');
		}

		$this->TField=array();
		foreach($Tab as $k=>$id) {
			$this->TField[$k]=new TAsset_field;
			$this->TField[$k]->load($PDOdb, $id);
		}
	}

	function addField(&$PDOdb, $TNField) {
		$k=count($this->TField);
		$this->TField[$k]=new TAsset_field;
		$this->TField[$k]->set_values($TNField);

		$type = $this->TField[$k]->type;

		$p=new TAsset;

		$param=array('type'=>'string','length'=>30);
		if($type == 'chaine100')$param=array('type'=>'string','length'=>100);
		else if($type == 'chaine255')$param=array('type'=>'string','length'=>255);
		else if($type == 'text')$param=array('type'=>'text');
		else if($type == 'float')$param=array('type'=>'float');
		else if($type == 'date')$param=array('type'=>'date');
		else if($type == 'entier')$param=array('type'=>'integer');
		else if($type == 'checkbox' || $type == 'liste')$param=array('type'=>'string', 'length'=>30, 'index'=>true);
		else if($type == 'sellist') $param=array('type'=>'integer');

		$p->add_champs($TNField['code'] ,$param );
		$p->init_db_by_vars($PDOdb);

		return $k;
	}

	function delField(&$PDOdb, $id){
		$toDel = new TAsset_field;
		$toDel->load($PDOdb,$id);
		return $toDel->delete($PDOdb);
	}

	function delete(&$PDOdb) {
		global $conf;
		if ($this->supprimable){
			//on supprime les champs associés à ce type
			$sqlReq="SELECT rowid FROM ".MAIN_DB_PREFIX."assetatm_field WHERE fk_asset_type=".$this->getId();
			$PDOdb->Execute($sqlReq);
			$Tab = array();
			while($PDOdb->Get_line()) {
				$Tab[]= $PDOdb->Get_field('rowid');
			}
			$temp = new TAsset_field;
			foreach ($Tab as $k => $id) {
				$temp->load($PDOdb, $id);
				$temp->delete($PDOdb);
			}
			//puis on supprime le type
			parent::delete($PDOdb);
			return true;
		}
		else {return false;}

	}
	function save(&$db) {
		global $conf;

		$this->entity = $conf->entity;
		$this->code = TAsset_type::code_format(empty($this->code) ? $this->libelle : $this->code);

		if ($this->gestion_stock == 'UNIT') $this->measuring_units = 'unit';

		$res = parent::save($db);

		foreach($this->TField as $field) {
			$field->fk_asset_type = $this->getId();
			$field->save($db);
		}

        return $res;

	}

	static function code_format($s){
		$r=""; $s = strtolower($s);
		$nb=strlen($s);
		for($i = 0; $i < $nb; $i++){
			if(ctype_alnum($s[$i]) || $s[$i] == "_"){
				$r.=$s[$i];
			}
		} // for

		//echo $r; exit;
		return $r;
	}

	//Function standard dolibarr pour afficher la structuration des masques
	function info()
    {
    	global $conf,$langs,$db;

		$langs->load("admin");

		$tooltip=$langs->trans("GenericMaskCodes",$langs->transnoentities("Proposal"),$langs->transnoentities("Proposal"));
		$tooltip.=$langs->trans("GenericMaskCodes2");
		$tooltip.=$langs->trans("GenericMaskCodes3");
		$tooltip.=$langs->trans("GenericMaskCodes5");

		return $tooltip;
    }

}

class TAsset_field extends TObjetStd {
	function __construct() { /* declaration */
		parent::set_table(MAIN_DB_PREFIX.'assetatm_field');
		parent::add_champs('code,libelle','type=chaine;');
		parent::add_champs('type','type=chaine;');
		parent::add_champs('obligatoire','type=entier;');
		parent::add_champs('ordre','type=entier;index;');
		parent::add_champs('options','type=text;');
		parent::add_champs('supprimable','type=entier;');
		parent::add_champs('inliste,inlibelle','type=chaine;'); //varchar booléen : oui/non si le champs sera dans la liste de Ressource.
		parent::add_champs('fk_asset_type,entity','type=entier;index;');

		$this->TListe = array();
		parent::_init_vars();
		parent::start();

	}

	public static function getAllByCode($only_inliste = true, $fk_asset_type = 0)
	{
		global $db,$conf;

		$TField = array();

		$sql = 'SELECT code, libelle, `type`, options FROM '.MAIN_DB_PREFIX.'assetatm_field WHERE entity = '.$conf->entity;
		if ($only_inliste) $sql.= ' AND inliste = \'oui\'';
		if (! empty($fk_asset_type)) $sql .= ' AND fk_asset_type = ' . intval($fk_asset_type);

		$resql = $db->query($sql);
		if ($resql)
		{
			while ($obj = $db->fetch_object($resql))
			{
				$TField[$obj->code] = array('label' => $obj->libelle, 'type' => $obj->type, 'options' => explode(';', $obj->options));
			}
		}
		else
		{
			dol_print_error($db);
		}

		return $TField;
	}

	function load_by_code(&$db, $code){
		$sqlReq="SELECT rowid FROM ".MAIN_DB_PREFIX."assetatm_field WHERE code='".$code."'";
		$db->Execute($sqlReq);

		if ($db->Get_line()) {
			$this->load($db, $db->Get_field('rowid'));
			return true;
		}
		return false;
	}


	function chargement(&$db, $libelle, $code, $type, $obligatoire, $ordre, $options, $supprimable, $fk_asset_type, $inliste = "non", $inlibelle = "non"){
		$this->load_by_code($db, $code);
		$this->libelle = $libelle;
		$this->code = $code;
		$this->type = $type;
		$this->obligatoire = $obligatoire;
		$this->ordre = $ordre;
		$this->options = $options;
		$this->supprimable = $supprimable;
		$this->inliste = $inliste;
		$this->inlibelle = $inlibelle;
		$this->fk_asset_type = $fk_asset_type;


		$this->save($db);
	}

	function load(&$PDOdb, $id, $loadChild = true)
	{
		$res = parent::load($PDOdb, $id);

		$this->TListe = array();

		// /!\ le champ obligatoire est inversé : 0 => obligatoire, 1 => facultatif...
		if(! empty($this->obligatoire))
		{
			$this->TListe[] = '';
		}
		if ($this->type == 'liste' && !empty($this->options))
		{
			foreach (explode(";",$this->options) as $key => $value)
			{
				$this->TListe[$key + 1] = $value; // On décale de 1 pour éviter un index à 0 qui fausse le test empty() de remplissage des champs obligatoires
			}
		}

		if($this->type == 'sellist' && !empty($this->options))
		{
			$this->TListe = array( 0 => '' );
			$params = explode(':', $this->options);
			$sql = "SELECT ".$params[2].", ".$params[1]." FROM ".MAIN_DB_PREFIX.$params[0];
			if (!empty($params[4])) $sql.= "WHERE ".$params[4];

			$res = $PDOdb->execute($sql);
			while ($PDOdb->Get_line())
			{
				$this->TListe[$PDOdb->Get_field($params[2])] = $PDOdb->Get_field($params[1]);
			}
		}

		return $res;
	}

	function save(&$db) {
		global $conf;

		$this->code = TAsset_type::code_format(empty($this->code) ? $this->libelle : $this->code);

		$this->entity = $conf->entity;
		if (empty($this->supprimable)){$this->supprimable = 0;}
		return parent::save($db);
	}

	function delete(&$PDOdb) {
		global $conf;

		//on supprime le champs que si il est par défault.
		if (! $this->supprimable){
			parent::delete($PDOdb);
			return true;
		}
		else {return false;}
	}

}

class TAssetLot extends TObjetStd{
/*
 * Gestion des lot d'équipements
 * */

	function __construct() {
		$this->set_table(MAIN_DB_PREFIX.'assetatmlot');
		$this->add_champs('entity','type=entier;');
		$this->add_champs('lot_number', 'type=chaine;');

	    $this->start();

		$this->TTraceabilityObjectLinked = array();
		$this->TTraceability = array();
		$this->TLotRecursive = array();
	}

	/*
	 *  Traçabilité
	 *  Récupération en cascade de tous les documents liés au lot
	 */
	function getTraceabilityObjectLinked(&$PDOdb,$assetid=0){
		global $conf;
		$this->_getTraceabilityExpedition($PDOdb,$assetid);

		$this->_getTraceabilityCommandeFournisseur($PDOdb,$assetid);
		if(!empty($this->TTraceabilityObjectLinked['expedition']))
			$this->_getTraceabilityCommande($PDOdb,$assetid);

		if($conf->of->enabled) $this->_getTraceabilityOF($PDOdb,$assetid);
		if($conf->contrat->enabled) $this->getTraceabilityObject($PDOdb,$assetid);
		if($conf->ficheinter->enabled) $this->getTraceabilityObject($PDOdb,$assetid,'Fichinter');
		if($conf->ticketsup->enabled) $this->getTraceabilityObject($PDOdb,$assetid,'Ticketsup');
		if($conf->commande->enabled) $this->getTraceabilityObject($PDOdb,$assetid,'Commande');
	}

	function _getTraceabilityExpedition(&$PDOdb,$assetid=0){
		global $db,$langs;
		dol_include_once('/expedition/class/expedition.class.php');
		dol_include_once('/societe/class/societe.class.php');

		//Liste des expéditions liés à l'équipement
		$sql = "SELECT DISTINCT(e.rowid)
				FROM ".MAIN_DB_PREFIX."expedition as e
					LEFT JOIN ".MAIN_DB_PREFIX."expeditiondet as ed ON (ed.fk_expedition = e.rowid)
					LEFT JOIN ".MAIN_DB_PREFIX."expeditiondet_asset as eda ON (eda.fk_expeditiondet = ed.rowid)
					LEFT JOIN ".MAIN_DB_PREFIX."assetatm as a ON (a.rowid = eda.fk_asset)
					LEFT JOIN ".MAIN_DB_PREFIX."assetatmlot as al ON (al.lot_number = a.lot_number)";
		if($assetid) $sql .= " WHERE a.rowid = ".$assetid;
		else $sql .= " WHERE al.lot_number = '".$this->lot_number."'";

		$PDOdb->Execute($sql);
		$societe = new Societe($db);
		$expedition = new Expedition($db);

		while($PDOdb->Get_line()){

			$expedition->fetch($PDOdb->Get_field('rowid'));
			$societe->fetch($expedition->socid);

			$this->TTraceabilityObjectLinked['expedition'][$expedition->id]['ref'] = $expedition->getNomUrl(1);
			$this->TTraceabilityObjectLinked['expedition'][$expedition->id]['societe'] = $societe->getNomUrl(1);
			$this->TTraceabilityObjectLinked['expedition'][$expedition->id]['date_livraison'] = date('d/m/Y',$expedition->date_delivery);
			$this->TTraceabilityObjectLinked['expedition'][$expedition->id]['status'] = $expedition->LibStatut($expedition->fk_statut,5);
		}
	}

	function _getTraceabilityCommandeFournisseur(&$PDOdb,$assetid=0){
		global $db,$langs;
		dol_include_once('/fourn/class/fournisseur.commande.class.php');
		dol_include_once('/societe/class/societe.class.php');

		//Liste des commandes fournisseurs liés à l'équipement
		$sql = "SELECT DISTINCT(cf.rowid)
				FROM ".MAIN_DB_PREFIX."commande_fournisseur as cf
					LEFT JOIN ".MAIN_DB_PREFIX."commande_fournisseurdet as cfd ON (cfd.fk_commande = cf.rowid)
					LEFT JOIN ".MAIN_DB_PREFIX."commande_fournisseurdet_asset as cfda ON (cfda.fk_commandedet = cfd.rowid)
					LEFT JOIN ".MAIN_DB_PREFIX."assetatm as a ON (a.serial_number = cfda.serial_number)
					LEFT JOIN ".MAIN_DB_PREFIX."assetatmlot as al ON (al.lot_number = a.lot_number)";
		if($assetid) $sql .= " WHERE a.rowid = ".$assetid;
		else $sql .= " WHERE al.lot_number = '".$this->lot_number."'";
		//echo $sql;
		$PDOdb->Execute($sql);

		$commandeFournisseur = new CommandeFournisseur($db);
		$societe = new Societe($db);

		while($PDOdb->Get_line()){

			$commandeFournisseur->fetch($PDOdb->Get_field('rowid'));
			$societe->fetch($commandeFournisseur->socid);

			//pre($commandeFournisseur,true);
			$this->TTraceabilityObjectLinked['commande_fournisseur'][$commandeFournisseur->id]['ref'] = $commandeFournisseur->getNomUrl(1);
			$this->TTraceabilityObjectLinked['commande_fournisseur'][$commandeFournisseur->id]['ref_fourn'] = $commandeFournisseur->ref_supplier;
			$this->TTraceabilityObjectLinked['commande_fournisseur'][$commandeFournisseur->id]['societe'] = $societe->getNomUrl(1);
			$this->TTraceabilityObjectLinked['commande_fournisseur'][$commandeFournisseur->id]['date_commande'] = date('d/m/Y',$commandeFournisseur->date);
			$this->TTraceabilityObjectLinked['commande_fournisseur'][$commandeFournisseur->id]['total_ttc'] = price($commandeFournisseur->total_ttc);
			$this->TTraceabilityObjectLinked['commande_fournisseur'][$commandeFournisseur->id]['date_livraison'] = date('d/m/Y',$commandeFournisseur->date_livraison);
			$this->TTraceabilityObjectLinked['commande_fournisseur'][$commandeFournisseur->id]['status'] = $commandeFournisseur->getLibStatut(3);
		}
	}

	function _getTraceabilityCommande(&$PDOdb,$assetid=0){
		global $db,$langs;
		dol_include_once('/commande/class/commande.class.php');
		dol_include_once('/societe/class/societe.class.php');
		//Liste des commandes clients liés à l'équipement
		$sql = "SELECT DISTINCT(c.rowid)
				FROM ".MAIN_DB_PREFIX."commande as c
					LEFT JOIN ".MAIN_DB_PREFIX."element_element as ee ON (ee.fk_source = c.rowid AND ee.sourcetype = 'commande' AND ee.targettype = 'shipping')
				WHERE  ee.fk_target IN (".implode(',', array_keys($this->TTraceabilityObjectLinked['expedition'])).")";

		$PDOdb->Execute($sql);

		$societe = new Societe($db);
		$commande = new Commande($db);

		while($PDOdb->Get_line()){

			$commande->fetch($PDOdb->Get_field('rowid'));
			$societe->fetch($commande->socid);

			$this->TTraceabilityObjectLinked['commande'][$commande->id]['ref'] = $commande->getNomUrl(1);
			$this->TTraceabilityObjectLinked['commande'][$commande->id]['ref_client'] = $commande->ref_client;
			$this->TTraceabilityObjectLinked['commande'][$commande->id]['societe'] = $societe->getNomUrl(1);
			$this->TTraceabilityObjectLinked['commande'][$commande->id]['date_commande'] = date('d/m/Y',$commande->date_commande);
			$this->TTraceabilityObjectLinked['commande'][$commande->id]['date_livraison'] = date('d/m/Y',$commande->date_livraison);
			$this->TTraceabilityObjectLinked['commande'][$commande->id]['total_ht'] = price($commande->total_ht).' €';
			$this->TTraceabilityObjectLinked['commande'][$commande->id]['status'] = $commande->getLibStatut(3);
		}
	}

	/**
	 * Generic function to get contract, fichinter or ticket tracability
	 */
	function getTraceabilityObject(&$PDOdb, $assetid=0, $type_object='Contrat') {

		global $db,$langs;
		dol_include_once('/contrat/class/contrat.class.php');
		dol_include_once('/fichinter/class/fichinter.class.php');
		dol_include_once('/ticketsup/class/ticketsup.class.php');
		dol_include_once('/societe/class/societe.class.php');

		// Liste des contrats liés à l'équipement
		$sql = 'SELECT DISTINCT(d.fk_object)
				FROM '.MAIN_DB_PREFIX.'dispatch_assetatm da
				LEFT JOIN '.MAIN_DB_PREFIX.'dispatch d ON (d.rowid = da.fk_dispatch)
				WHERE d.type_object = "'.(($type_object === 'Fichinter') ? 'intervention' : strtolower($type_object)).'"
				AND fk_asset = '.$assetid;

		$PDOdb->Execute($sql);

		$societe = new Societe($db);
		$o = new $type_object($db);

		$field_socid = ($type_object === 'Contrat') ? 'fk_soc' : 'socid';
		$field_status = ($type_object === 'Fichinter') ? 'statut' : 'fk_statut';

		switch($type_object) {
			case 'Ticketsup':
				$field_date = 'datec';
				break;

			case 'Commande':
				$field_date = 'date';
				break;

			default:
				$field_date = 'date_contrat';
		}

		while($PDOdb->Get_line()){

			$o->fetch($PDOdb->Get_field('fk_object'));

			$societe->fetch($o->{$field_socid});

			$this->TTraceabilityObjectLinked[$type_object][$o->id]['ref'] = $o->getNomUrl(1);
			$this->TTraceabilityObjectLinked[$type_object][$o->id]['societe'] = $societe->id ? $societe->getNomUrl(1) : 'Aucune';
			$this->TTraceabilityObjectLinked[$type_object][$o->id]['status'] = ($type_object == 'Commande' ? $o->LibStatut($o->{$field_status}, $o->billed, 5) : $o->LibStatut($o->{$field_status},5));

			if($type_object !== 'Fichinter') $this->TTraceabilityObjectLinked[$type_object][$o->id]['date'] = date('d/m/Y', $o->$field_date);
			if($type_object === 'Ticketsup') $this->TTraceabilityObjectLinked[$type_object][$o->id]['date_last_modif'] = date('d/m/Y', $o->tms);

		}

	}

	function _getTraceabilityOF(&$PDOdb,$assetid=0){
		global $db,$langs;
		dol_include_once('/societe/class/societe.class.php');
		dol_include_once('/product/class/product.class.php');
		dol_include_once('/of/class/ordre_fabrication_asset.class.php');

		//Liste des OF liés à l'équipement
		$sql = "SELECT DISTINCT(of.rowid)
				FROM ".MAIN_DB_PREFIX."assetOf as of
					LEFT JOIN ".MAIN_DB_PREFIX."assetOf_line as ofl ON (ofl.fk_assetOf = of.rowid)
					LEFT JOIN ".MAIN_DB_PREFIX."element_element as ee ON (ee.fk_source = ofl.rowid AND sourcetype = 'TassetOFLine' AND targettype = 'TAsset')
					LEFT JOIN ".MAIN_DB_PREFIX."assetatm as a ON (a.rowid = ee.fk_target)
					LEFT JOIN ".MAIN_DB_PREFIX."assetatmlot as al ON (al.lot_number = a.lot_number)";
		if($assetid) $sql .= " WHERE a.rowid = ".$assetid;
		else $sql .= " WHERE al.lot_number = '".$this->lot_number."'";

		//echo $sql;
		$PDOdb->Execute($sql);
		$Tres = $PDOdb->Get_All();

		$societe = new Societe($db);
		$assetof = new TAssetOF;
		$product = new Product($db);

		foreach($Tres as $res){

			$assetof->load($PDOdb,$res->rowid);
			$societe->fetch($assetof->fk_soc);
			foreach($assetof->TAssetOFLine as $key=>$TAssetOFLine){
				$product->fetch($TAssetOFLine->fk_product);
				if($TAssetOFLine->type == 'TO_MAKE'){
					$produits_tomake .= $product->getNomUrl(1)."<br>";
				}
				else{
					$produits_needed .= $product->getNomUrl(1);
					/*if($cpt % 3 )$produits_needed .= '<br>';
					$cpt++;*/
				}
			}

			$this->TTraceabilityObjectLinked['of'][$assetof->getId()]['ref'] = '<a href="'.dol_buildpath('/of/fiche_of.php',1).'?id='.$assetof->getId().'">'.img_picto('','object_list.png','',0).$assetof->numero.'</a>';
			$this->TTraceabilityObjectLinked['of'][$assetof->getId()]['societe'] = $societe->getNomUrl(1);
			//$this->TTraceabilityObjectLinked['of'][$assetof->getId()]['produit_needed'] = $produits_needed;
			$this->TTraceabilityObjectLinked['of'][$assetof->getId()]['produit_tomake'] = $produits_tomake;
			$this->TTraceabilityObjectLinked['of'][$assetof->getId()]['priorite'] = TAssetOF::$TOrdre[$assetof->ordre];
			$this->TTraceabilityObjectLinked['of'][$assetof->getId()]['date_lancement'] = $assetof->get_date('date_lancement');
			$this->TTraceabilityObjectLinked['of'][$assetof->getId()]['date_besoin'] = $assetof->get_date('date_besoin');
			$this->TTraceabilityObjectLinked['of'][$assetof->getId()]['status'] = TAssetOF::$TStatus[$assetof->status];

		}

		//pre($this->TTraceabilityObjectLinked['of'],true);exit;
	}

}
