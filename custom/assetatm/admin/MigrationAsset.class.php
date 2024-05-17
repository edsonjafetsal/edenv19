<?php


/**
 * Class MigrationAsset
 *
 *  Permet de delocaliser les equipements de assetatm vers product_lot/batch en natif depuis LA VERSION dolibarr 13.0
 *  LA CLASSE  VERIFIE  l'activation du module Lot natif pour effectuer les transferts.
 *  donne un message d'erreur sur la page d'admin si pas activé
 *
 *
 *  1 création des product_batch et des product_lot
 *
 * ------------------------------------------------------------------------------------------------------------------
 *  2 Création des extrafields de assetatm_fields dans llx_extrafields
 *
 * ------------------------------------------------------------------------------------------------------------------
 *  3 Update des serials dans stock_mouvment depuis assetatm_tranfert (mouvement d'entrepot à entrepot )
 *   {
 *    fk_dol_moov et fk_dol_target  sont des id stock_mouvement et servent à copier
 *    les source_serial et  target_serial dans les stock_mouvement correspondant
 *   }
 *
 *  ------------------------------------------------------------------------------------------------------------------
 *  4 update des  serials dans stock mouvment depuis les lignes assetatm_
 *  ------------------------------------------------------------------------------------------------------------------
 *
 *  passer la const debug à true pour les tests et modifications
 *
 */
class MigrationAsset
{

	const SIMULATION = 'SIMULATION';
	const EXECUTION = 'EXECUTION';
	const WAREHOUSE = 'WAREHOUSE_MIGRATION';
	const TABLE_BATCH = "product_batch";
	const TABLE_LOT = "product_lot";
	const PRODUIT_SERIALIZED = 1;
	public $db;
	public $entId;
	public $nbbatchAdded;
	public $nblotAdded;
	public $nbMvtUpdated;
	public $output;
	const DEBUG = false;
	public $errors;


	/**
	 * MigrationAsset constructor.
	 * @param $db
	 */
	public function __construct($db)
	{
		$this->db = $db;
		$this->nbbatchAdded = 0;
		$this->nblotAdded = 0;
		$this->nbMvtUpdated = 0;


	}

	/**
	 * return the id product_stock  for product and stock  in equipement
	 *
	 * @param $productId
	 * @param $EntrepotId
	 * @return int  0 not found , > 0 id product_stock
	 */
	public function getProductStockId($productId, $currentEqEntrepotId)
	{

		// recuperer le product_stock id
		$sqlProdStock = " SELECT rowid FROM " . MAIN_DB_PREFIX . "product_stock where fk_product=" . $productId . " AND fk_entrepot=" . $currentEqEntrepotId;

		$resProdStock = $this->db->query($sqlProdStock);
		$numProdStock = $this->db->num_rows($resProdStock);
		if ($numProdStock <= 0) {
			return 0;
		}
		$stockProd = $this->db->fetch_object($resProdStock);
		return intval($stockProd->rowid);
	}

	/**
	 * @param int $ProductStockID
	 * @param string $serial
	 * @param string $table
	 * @return int
	 */
	public function isEqExist($table, $ProductStockID = 0, $serial, $fk_product = 0 )
	{

		$sqlBatchAlreadyThere = "SELECT rowid FROM " . MAIN_DB_PREFIX . $table;
		if ($table == SELF::TABLE_BATCH) {
			$sqlBatchAlreadyThere .= " WHERE fk_product_stock =" . $ProductStockID;
		} else {
			$sqlBatchAlreadyThere .= " WHERE fk_product =" . $fk_product;
		}
		$sqlBatchAlreadyThere .= " AND batch =" . $serial;

		$resBatchAlreadyThere = $this->db->query($sqlBatchAlreadyThere);

		$numBatchAlreadyThere = $this->db->num_rows($resBatchAlreadyThere);

		if ($numBatchAlreadyThere > 0) {
			return  $this->db->fetch_object($resBatchAlreadyThere);
		}

		return 0;
	}

	/**
	 * @param string $mode
	 */
	public function action_equipment($mode)
	{
		global $user,$langs;


		$this->initExtrafield();

		$start_time = microtime(true);
		$this->output = "";
		$sql = " SELECT p.rowid, pe.type_asset, p.ref, p.description from " . MAIN_DB_PREFIX . "product p";
		$sql .= " INNER join " . MAIN_DB_PREFIX . "product_extrafields pe on p.rowid = pe.fk_object";
		$sql .= " AND pe.type_asset = ".SELF::PRODUIT_SERIALIZED;
		$sql .= " AND p.fk_product_type = 0";
		if ( SELF::DEBUG ){
			$sql .= " LIMIT 2";
		}


		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
				$i = 0;
				while ($i < $num) {
					$product = $this->db->fetch_object($resql);


					 $this->output .= "<span class='text-danger'><strong >".$langs->trans('Product') .  " : " . $product->rowid . " " . $product->ref . "</strong></span><br><hr>";
					//PRODUCT SERIALIZED
					$sqleqs = "SELECT * FROM " . MAIN_DB_PREFIX . "assetatm a where a.fk_product =" . $product->rowid;
					$sqleqs .= " AND a.fk_asset_type = 1";

					$res = $this->db->query($sqleqs);

					if ($res) {
						$num2 = $this->db->num_rows($res);
						$j = 0;
						$this->updateProductType($product->rowid,$user);
							// SERIALIZED
						while ($j < $num2) {
								$eq = $this->db->fetch_object($res);

								// ------------ EQUIPEMENTS ----------------
								$this->createProductBatchLot($product, $eq, $user,$mode);

								// ------------ MOUVEMENTS ----------------
								$this->moveSerialFromAssetAtm($eq,$mode);

								// ------------ MOUVEMENTS TRANSFERT ----------------
								$this->moveSerialFromAssetAtmTransfert($eq,$mode);

								$j++;
						}
					}
					$i++;
					$this->output .= "<br>";
				}

		}

		$end_time = microtime(true);
		$execution_time = number_format(($end_time - $start_time), 2, '.', ' ');




		$outPlusTime = "<span class='text-danger'>" . $mode . "</span> <br><hr>";
		$outPlusTime .= $langs->trans('Executiontimeofscript')." ". gmdate("H:i:s", $execution_time) . " sec <br><hr>";
		$outPlusTime .= $langs->trans('batchadded') ." ". $this->nbbatchAdded . "<br>";
		$outPlusTime .= $langs->trans('lotadded')   ." ". $this->nblotAdded . "<br>";
		$outPlusTime .= $langs->trans('moveupdated') ." ". $this->nbMvtUpdated . "<br><hr>";
		$outPlusTime .= $this->errors;

		$outPlusTime .= $this->output;


		return $outPlusTime;

	}

	/**
	 * @param $productId
	 * @param $user
	 */
	public function updateProductType($productId,&$user){


		$p = new Product($this->db);
		$p->fetch($productId);
		// le produit est du type serialisé nous mettons à jour la nouvelle variable de gestion des produits sérialiés
		$p->status_batch = 1;
		$resultUpdade  = $p->update($p->id,$user);


	}

	/**
	 * duplication des déclarations des champs de la table assetatm_field (extrafields  de assetatm)
	 * gestion historique d'extrafield ne correspondant pas au standard actuel des extrafields dans dolibarr.
	 *
	 * standard
	 * ----------------
	 *
	 * table
	 * ----------------
	 * llx_extrafield |
	 * ---------------
	 * contient les déclarations des champs
	 *
	 *
	 * * table
	 * ----------------
	 * llx__obj_extrafield |
	 * ---------------
	 * contient les valeurs
	 *
	 *
	 * ______________________________________________________________________________
	 *
	 * ______________________________________________________________________________
	 *
	 * assetAtm  ne gère pas cela de la même manière
	 *
	 * * -----------------
	 * llx_assetatm_field|
	 * -------------------
	 *
	 * fait office de llx_extrafield
	 *
	 * * ----------
	 * llx_assetatm|
	 * ------------
	 * les valeurs unitaire pour ces declarations de champs sont AJOUTÉES dans la table assetatm
	 * pour chaque ligne dans la table. fait office de llx_obj_extrafields
	 *
	 *
	 *
	 *
	 * vers les extrafields de product_lot
	 *
	 */


	public function initExtrafield()
	{

		dol_include_once('/core/class/extrafields.class.php');

		// listing des champs dans assetatm_field
		$sql = "Select * from " . MAIN_DB_PREFIX . "assetatm_field";

		$res = $this->db->query($sql);
		$num = $this->db->num_rows($res);
		if ($num) {
			$i = 0;
			while ($i < $num) {
				$objField = $this->db->fetch_object($res);
				$type = $objField->type;

				$extrafields = new ExtraFields($this->db);

				$number = 0;
				$typeField = '';
				switch ($type) {
					case "chaine30": // string 30

						$typeField = 'varchar';
						$number = 30;
						break;

					case "chaine100": // string 100

						$typeField = 'varchar';
						$number = 100;
						break;
					case "chaine255": // string 255

						$typeField = 'varchar';
						$number = 255;
						break;
					case "chaine":  // string

						$typeField = 'varchar';
						$number = 255;
						break;

					case "sellist": //liste issue d'une table

						$typeField = 'sellist';
						$number = 255;
						break;

					case "date": //date

						$typeField = 'date';
						$number = '';
						break;

					case "checkbox": // checkbox

						$typeField = 'varchar';
						$number = 255;
						break;

					case "entier": // int

						$typeField = 'link';
						$number = '';
						break;

					case "float": // float

						$typeField = 'price';
						$number = '';
						break;
					case "text": // string 30

						$typeField = 'html';
						$number = '';
						break;
					case "liste": // list

						break;
				}

				$param = array('options' => array($objField->options));
				//$objField->obligatoire
				//$attrname,     $label,             $type,     $pos,             $size,    $elementtype, $unique = 0, $required = 0, $default_value = '', $param = ''
				$re = $extrafields->addExtraField($objField->code, $objField->libelle, $typeField, $objField->ordre, $number, 'product_lot', 0, 0, '', $param);
				if ($re <= 0){
					$this->errors .= $extrafields->error ."</br>";
				}																															  //$objField->obligatoire
				$i++;
			}
		}

		$this->addExtrafieldsNotHandledByAssetatmField($extrafields);
		// CHAMPS AJOUTÉS DEPUIS LA TABLE ASSETATM (ceux qui n'ont pas d'existance dans assetatm_field



	}

	/**
	 * recuperation des champs IMPORTANTS de la table assetatm ne figurant pas dans la table assetatm_field
	 * @param $extrafields
	 */
	public function addExtrafieldsNotHandledByAssetatmField($extrafields){

		// fk_societe_localisation
		$param = array('options' => "");
		//$attrname, $label,              $type, $pos,$size,$elementtype, $unique = 0, $required = 0, $default_value = '', $param = ''
		$re = $extrafields->addExtraField("fk_societe_localisation", "fk_societe_localisation", "int", 0, 11, 'product_lot', 0, 0, '', $param);
		if ($re <= 0){
			$this->errors .= $extrafields->error ."</br>";
		}

		/*//dluo qui devrait être du type date sera , finnalement,  transformé en varchar 255 par la methode addExtrafield;
		$param = array('options' => "");
		//$attrname, $label,              $type, $pos,$size,$elementtype, $unique = 0, $required = 0, $default_value = '', $param = ''
		$re = $extrafields->addExtraField("dluo", "dluo", "varchar", 0, 255, 'product_lot', 0, 0, '', $param);*/

	}

	/**
	 * ajout des extrafields pour l'equipement en cours de traitement
	 * @param Productlot $lot
	 * @param stdClass $eq
	 * @param string $mode
	 */
	public function setExtrafieldsfromCurrentEq($lot, $eq,$mode)
	{

		dol_include_once('/core/class/extrafields.class.php');

		$extrafields = new ExtraFields($this->db);
		$extrafields->fetch_name_optionals_label($lot->table_element);
		$res = $lot->fetch_optionals();

		if ($res >= 0){
			foreach ($extrafields->attributes[SELF::TABLE_LOT]['type'] as $key => $val){


				// on interprete $key pour matcher avec le nom de la colonne dans assetatm
				// certain champs sont vides dans la base
				// même en forcant le champ à non obligatoire dans sa definition  une erreur si vide


				if (empty($eq->{$key}) || is_null($eq->{$key}) ){

					if ($val == "varchar"){
						$lot->array_options["options_" . $key] = "non défini";
					}else{
						$lot->array_options["options_" . $key] = 0;
					}

				}else{
					$lot->array_options["options_" . $key] = $eq->{$key};
				}
				$this->db->begin();
				$result = $lot->insertExtraFields();
				if ($result){
					if ($mode == SELF::SIMULATION){
						$this->db->rollback();
					}else{
						$this->db->commit();
					}

				}else{
					//var_dump("result  : ".$result . " option : " . $lot->array_options);
				}
			}
		}

	}

	/**
	 * depuis la table assetatm_transfert on recupere les id des product_stocks
	 * pour ensuite recuperer les stock_mouvements correspondant pour finalement
	 * ajouter dans chaque stock_mouvement le serial_number de l'equipement dans le champs batch
	 *
	 *
	 * @param stdClass $eq
	 * @param string $mode
	 */
	public function moveSerialFromAssetAtmTransfert($eq,$mode){
		global $langs;
		// fk_dol_moov / fk_dol_target  = id stock_mouvement
		$errors =  0;

		$sqlTransfert = "SELECT fk_dol_moov,fk_dol_moov_target FROM ".MAIN_DB_PREFIX."assetatm_tranfert WHERE source_serial =".$eq->serial_number;
		$re = $this->db->query($sqlTransfert);
		if ($re) {
			$num = $this->db->num_rows($re);
				$l = 0;
				while ($l < $num) {
					$this->db->begin();
					$mv = $this->db->fetch_object($re);
					$sql  = " UPDATE ".MAIN_DB_PREFIX."stock_mouvement SET batch =".$eq->serial_number;
					$sql .= " WHERE rowid = ". $mv->fk_dol_moov;
					$result = $this->db->query($sql);

					if ($result < 0){
						$this->errors.= "Update stock_mouvement failed for : ".$eq->serial_number."<br>";
					}else {
						$this->nbMvtUpdated++;
					}

					$sql  = " UPDATE ".MAIN_DB_PREFIX."stock_mouvement SET batch =".$eq->serial_number;
					$sql .= " WHERE rowid = ". $mv->fk_dol_moov_target;
					$result = $this->db->query($sql);

					if ($result){

						if ($mode == SELF::SIMULATION) {
							$modeMsg = $langs->trans('willbeAddedTo');
						}else{
							$modeMsg = $langs->trans('addedTo');
						}

						$this->out .= "<span class='text-warning'> le serial  : " . $eq->serial_number . $modeMsg . $mv->rowid . " </span><br>";
						$this->nbMvtUpdated++;

					}else{
							$this->errors .= "Update stock_mouvement failed for : ".$eq->serial_number."</br>";
						}

					$l++;
					if ($mode == SELF::SIMULATION) {
						$this->db->rollback();
					}else{
						$this->db->commit();
					}
				}



		}
	}


	/**
	 * copie le serial de l'equipement selectionné depuis assetAtm vers la table stock_mouvement
	 *
	 * @param stdClass $eq
	 * @param string $mode
	 * @param string $out
	 */
	public function moveSerialFromAssetAtm($eq,$mode ){
		global $langs;

		$sqlMvt = " SELECT * FROM " . MAIN_DB_PREFIX . "assetatm_stock ";
		$sqlMvt .= " WHERE fk_asset = " . $eq->rowid;

		$re = $this->db->query($sqlMvt);
		if ($re) {
			$num = $this->db->num_rows($re);
				$k = 0;
				while ($k < $num) {
					$mv = $this->db->fetch_object($re);

					$this->db->begin();

					// 2  update du mvt standard en ajoutant  le serial dans le champs bash
					// et lot

					// pas de function update dans la classe stockmouvement
					// UPDATE table_name SET field1 = new-value1, field2 = new-value2
					//[WHERE Clause]
					$sql  = " UPDATE ".MAIN_DB_PREFIX."stock_mouvement SET batch =".$eq->serial_number;
					$sql .= " WHERE rowid = ". $mv->fk_stock_mouvement;
					//update MOUve
					$updateMouv = $this->db->query($sql);
					if ($updateMouv){
						if ($mode == SELF::SIMULATION) {
							$modeMsg = $langs->trans("willbeAddedTo");
						}else{
							$modeMsg = $langs->trans("addedTo");
						}
						$this->output .= "<span class='text-warning'> le serial  : " . $eq->serial_number . $modeMsg . $mv->rowid . " </span><br>";
						$this->nbMvtUpdated++;
					}else{
						$this->errors .= "update stock_mouvement faild for ".$eq->serial_number."</br";
					}
					$k++;

					if ($mode == SELF::SIMULATION) {
						$this->db->rollback();
					}else{
						$this->db->commit();}
					}

		}
	}

	/**
	 *
	 * creation de produit_batch et et produit lot
	 * @param stdClass $product
	 * @param stdClass $eq
	 * @param User $user
	 * @param string $out
	 */
	public function createProductBatchLot($product, $eq, $user,$mode){
		global $langs;
		$ProductStockID = $this->getProductStockId($product->rowid, $eq->fk_entrepot);


		// we found productid and entrepotId  in llx_product_entrepot
		//if ($ProductStockID > 0) {

		// 1 création eq  dans PRODUCT_LOT
		if (!$this->isEqExist(SELF::TABLE_LOT,0, $eq->serial_number, $product->rowid )) {
			$this->db->begin();
			$lot = new Productlot($this->db);
			$user->entity;
			$lot->entity = $eq->entity; // pas sûre que ce soit ça
			$lot->fk_product = $product->rowid;
			$lot->batch = $eq->serial_number;
			$lot->datec = dol_now();
			$lot->tms = dol_now();
			// product_lot import_key est un int  alors que import_key dans assetatm est un varchar au format imp01012021
			//var_dump($eq->import_key);

			if (empty($eq->import_key)) {
				$lot->import_key = 0;
			} else {
				$lot->import_key = substr($eq->import_key, 3);
			}

			$lot->fk_user_creat = $user->id;
			$result = $lot->create($user);

			//var_dump($result, $this->db->lasterror());
			if ($result > 0) {

				if ($mode == SELF::SIMULATION) {
					$modeMsg =  $langs->trans('willbeCreatedInLot');
				} else {
					$modeMsg = $langs->trans('createdInLot');
				}
				$this->nblotAdded++;
				$this->output .= "<span class='text-success'> l'ID équipement : " . $eq->rowid . " Pour le produit REF : " . $product->ref . " avec le serial  : " . $eq->serial_number . $modeMsg . "</span><br>";


				// EXTRAFIELDS
				$lot->fetch($result);
				// création ligne extrafield pour cet eq product_lot_extrafield
				// recuperation des assetatm_field
				//  on boucle sur les filed_name
				// on se sert du field_name comme nom de colonne depuis assetatm->filedname
				// pour l'affecter dans llx_lot_extrafield
				if ($mode == SELF::SIMULATION) { $this->db->rollback(); }else{ $this->db->commit();}
				$this->setExtrafieldsfromCurrentEq($lot, $eq, $mode);


			} else {
				//var_dump("no result for lot ... on serial  ".$eq->serial_number , $eq->import_key ,$this->db->lasterror);
			}

		}

		// 1.2 Création eq  dans PRODUCT_BATCH
		if (!$this->isEqExist(self::TABLE_BATCH,$ProductStockID, $eq->serial_number, 0 )) {

			$this->db->begin();
			$batch = new Productbatch($this->db);
			$batch->tms = $eq->tms;
			$batch->batch = $eq->serial_number;
			/**  clé de la table de jointure produit _ entrepot */
			$batch->fk_product_stock = ($ProductStockID == 0) ? null :$ProductStockID  ;
			$batch->import_key = $eq->import_key;
			$batch->date_creation = $eq->date_cre;
			$batch->date_modification = $eq->date_maj;
			$batch->qty = 1;
			$result = $batch->create($user);


			if ($result) {
						if ($mode == SELF::SIMULATION) {
							$modeMsg = $langs->trans('willbeCreatedInBatch');
						} else {
							$modeMsg = $langs->trans('createdInBatch');
						}

						$this->nbbatchAdded++;
						$this->output .= "<span class='text-success'>" . $langs->trans('idEquipement') . $eq->rowid . $langs->trans('forRef') . $product->ref . $langs->trans('withSerial') . $eq->serial_number . $modeMsg . "</span><br>";
					}
				if ($mode == SELF::SIMULATION) {
						$this->db->rollback();
				} else {
					$this->db->commit();
				}
			}
	}




	/**
	 * TEST ONLY
	 * dump de la base sur les extrafield lot et batch product
	 */
	public function dropTest(){

		// DANS LES TEST RÉALISÉS j'ai un lot de reference que je garde qui a l'id 1
		// à modifier si necessaire.
		$sql  = "delete from ".MAIN_DB_PREFIX."product_lot where rowid > 1";
		$re = $this->db->query($sql);
		// DANS LES TEST RÉALISÉS j'ai un batch de reference que je garde qui a l'id 1
		// à modifier si necessaire.
		$sql  = "delete from ".MAIN_DB_PREFIX."product_batch where rowid > 1";
		$re = $this->db->query($sql);
		// suppression des exrtafields
		$sql  = "delete from ".MAIN_DB_PREFIX."product_lot_extrafields ";
		$re = $this->db->query($sql);
		// udpate des mouvements en resetant la colonne batch
		// ATTENTION cette requete ne peut être utiliser QUE sur une base en locale
		$sql  = "UPDATE".MAIN_DB_PREFIX."stock_mouvement SET batch='' WHERE batch != '' ";
		$re = $this->db->query($sql);


		$sql = "delete FROM ".MAIN_DB_PREFIX."extrafields WHERE elementtype ='product_lot'";
		$re = $this->db->query($sql);


		// supprimer manuellement les declarations de colonnes dans product_lot_extrafields
	}
}
