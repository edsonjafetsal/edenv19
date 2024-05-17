<?php

class ActionsDispatch
{

	/**
	 * @param $parameters
	 * @param $object
	 * @param $action
	 * @param $hookmanager
	 */
	function doAction($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $db, $langs;

		$context = explode(":", $parameters['currentcontext']);
		if (in_array('receptionstockcard', $context)) {

			require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
			require_once DOL_DOCUMENT_ROOT . '/expedition/class/expedition.class.php';

			/**  fetchObjectLinked à voir avec john  */
			//$com = new Commande($db);
			//$com->fetchObjectLinked();
            //var_dump($com);
			$sql = "SELECT DISTINCT c.rowid FROM " . MAIN_DB_PREFIX . "commande AS c ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "element_element AS ee ON c.rowid = ee.fk_target";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "commandedet AS cd ON c.rowid = cd.fk_commande ";
			$sql .= " AND ee.fk_source = " . $object->id . "";
			$sql .= " AND ee.targettype = 'commande'";
			$sql .= " AND ee.sourcetype = 'commandefourn'";
			$sql .= " ORDER BY cd.rowid ";

			$resultSetSupplierOrder = $db->query($sql);

			if ($resultSetSupplierOrder) {
				$resql = $db->fetch_object($resultSetSupplierOrder);
				if ($resql) {
					$orderFromSupplierOrder = new Commande($db);
					$orderFromSupplierOrder->fetch($resql->rowid);

					if($orderFromSupplierOrder) {

						$shipmentsSql = "SELECT ex.customer_treated_shipment, ex.rowid, e.fk_statut , e.entity , e.rowid";
                        $shipmentsSql .= " FROM " . MAIN_DB_PREFIX . "commande AS c ";
						$shipmentsSql .= " INNER JOIN " . MAIN_DB_PREFIX . "element_element AS ee ON c.rowid = ee.fk_source ";
						$shipmentsSql .= " INNER JOIN " . MAIN_DB_PREFIX . "expedition AS e ON e.rowid = ee.fk_target ";
                        $shipmentsSql .= " INNER JOIN " . MAIN_DB_PREFIX . "expedition_extrafields  AS ex ON e.rowid = ex.fk_object";
						$shipmentsSql .= " AND c.rowid = '" . $orderFromSupplierOrder->id . "' ";
						$shipmentsSql .= " AND ee.sourcetype = 'commande' ";
						$shipmentsSql .= " AND ee.targettype = 'shipping' ";

						$resultSetShipments = $db->query($shipmentsSql);
						$TShipments = array();

						if ($resultSetShipments) {
							$num = $db->num_rows($resultSetShipments);
							$i = 0;
							while ($i < $num) {
								$obj = $db->fetch_object($resultSetShipments);

                                /**
                                 *on ne remonte que les expedition au statut cloturée
                                 * ou bien les expeditions traitées
                                 */
                               if ($obj) {
                                    if($obj->fk_statut == Expedition::STATUS_CLOSED){
                                        $TShipments[] = $obj;
                                    }
								} else {
									dol_syslog(__METHOD__.' $obj='.var_export($obj,true), LOG_ERR);
								}
								$i++;
							}

						} else {
							setEventMessage($langs->trans('ErrorAtResultSet', 'resultSetShipments'), 'errors');
							dol_syslog(__METHOD__.' $resql='.var_export($resql,true), LOG_ERR);
						}
						$object->orderFromSupplierOrder = $orderFromSupplierOrder;
						$object->shipmentsFromSupplier = $TShipments;
					} else {
						dol_syslog(__METHOD__.' $orderFromSupplierOrder='.var_export($orderFromSupplierOrder,true), LOG_ERR);
					}
				} else {
					dol_syslog(__METHOD__.' $resql='.var_export($resql,true), LOG_ERR);
				}
			} else {
				setEventMessage($langs->trans('ErrorAtResultSet', 'resultSetSupplierOrder'), 'errors');
				dol_syslog(__METHOD__.' $resultSetSupplierOrder='.var_export($resultSetSupplierOrder,true), LOG_ERR);
			}
		}
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param array()         $parameters     Hook metadatas (context, etc...)
	 * @param CommonObject    &$object      The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string          &$action      Current action (if set). Generally create or edit or null
	 * @param HookManager      $hookmanager Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf;

		$TContexts = explode(':', $parameters['context']);

		if (in_array('bonderetourcard', $TContexts)) {
			//			var_dump($conf->global->STOCK_CALCULATE_ON_BONDERETOUR_VALIDATE, $conf->global->STOCK_CALCULATE_ON_BONDERETOUR_CLOSE);
			//if (! empty($conf->global->STOCK_CALCULATE_ON_BONDERETOUR_VALIDATE)) $conf->global->STOCK_CALCULATE_ON_BONDERETOUR_VALIDATE = 0;
			//if (! empty($conf->global->STOCK_CALCULATE_ON_BONDERETOUR_CLOSE)) $conf->global->STOCK_CALCULATE_ON_BONDERETOUR_CLOSE = 0;
			//			var_dump($conf->global->STOCK_CALCULATE_ON_BONDERETOUR_VALIDATE, $conf->global->STOCK_CALCULATE_ON_BONDERETOUR_CLOSE);
			//			exit('la');
		}

		return 0;
	}

	/** Overloading the addMoreActionsButtons function : replacing the parent's function with the one below
	 * @param parameters  meta datas of the hook (context, etc...)
	 * @param object             the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param action             current action (if set). Generally create or edit or null
	 * @return       void
	 */
	function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
	{
		global $conf;

		$TContexts = explode(':', $parameters['context']);

		// Add more cols on card lines table
		$this->addMoreColsOnCardTable($parameters, $object, $action, $hookmanager);


		if (in_array('ordersuppliercard', $TContexts)) {
			$id = GETPOST('id');
			$targetUrl = dol_buildpath('/dispatch/reception.php', 2) . '?id=' . $id;
			?>
			<script>
				$(document).ready(function () {
					$('a[href*="fourn/commande/dispatch.php"]').attr('href', '<?php print dol_escape_js($targetUrl, 1); ?>');
				});
			</script>
			<?php
		}

		if (in_array('expeditioncard', $TContexts) && $object->statut == Expedition::STATUS_VALIDATED && !empty($conf->global->DISPATCH_BLOCK_SHIPPING_CLOSING_IF_PRODUCTS_NOT_PREPARED)) {
			if (!defined('INC_FROM_DOLIBARR'))
				define('INC_FROM_DOLIBARR', true);
			dol_include_once('/dispatch/config.php');
			dol_include_once('/dispatch/lib/dispatch.lib.php');

			list($canBeClosed, $msg) = dispatch_shipment_can_be_closed($object);

			if (empty($canBeClosed)) {
				global $langs;

				$langs->load('dispatch@dispatch');

				$message = dol_escape_js($langs->transnoentities('ShipmentCannotBeClosed', $msg), 1);
				?>
				<script>
					$(document).ready(function () {
						$('a.butAction[href*=action\\=classifyclosed]').removeClass('butAction').addClass('butActionRefused').prop('href', '#').prop('title', '<?php echo $message; ?>');
					});
				</script>
				<?php
			}
		}

		return 0;
	}

	/**
	 * @param array $parameters
	 * @param CommonObject $object
	 * @param string $action
	 * @param HookManager $hookmanager
	 */
	function beforePDFCreation($parameters, &$object, &$action, $hookmanager)
	{

		// pour implementation dans Dolibarr 3.7
		if (in_array('pdfgeneration', explode(':', $parameters['context']))) {


			define('INC_FROM_DOLIBARR', true);
			dol_include_once('/dispatch/config.php');
			dol_include_once('/' . ATM_ASSET_NAME . '/class/asset.class.php');
			dol_include_once('/dispatch/class/dispatchdetail.class.php');
			dol_include_once('/dispatch/class/dispatchasset.class.php');
			dol_include_once('/core/lib/product.lib.php');

			global $conf;
			if (!empty($parameters['object']) && (get_class($object) == 'Expedition' || get_class($object) == 'Livraison')) {

				$PDOdb = new TPDOdb;

				$outputlangs = $parameters['outputlangs'];
				$outputlangs->load(ATM_ASSET_NAME . '@' . ATM_ASSET_NAME);
				$outputlangs->load('productbatch');
				$outputlangs->load('dispatch@dispatch');

				$expedition = $object;
				if (get_class($object) == 'Livraison') {
					$expedition = new Expedition($object->db);
					$expedition->fetch($object->origin_id);
				}
				foreach ($object->lines as &$line) {

					$details = new TDispatchDetail;

					$fkExpeditionLine = $line->id;

					if (get_class($object) == 'Livraison') {
						$fkExpeditionLine = 0;

						foreach ($expedition->lines as $lineExpe) {
							if ($lineExpe->fk_origin_line == $line->fk_origin_line) { // La ligne d'origine de la livraison est la ligne de commande et non la ligne d'expédition
								$fkExpeditionLine = $lineExpe->id;
								break;
							}
						}
					}

					if (!empty($parameters['object']) && get_class($object) == 'CommandeFournisseur') {

						$PDOdb = new TPDOdb;

						$outputlangs = $parameters['outputlangs'];
						$outputlangs->load(ATM_ASSET_NAME . '@' . ATM_ASSET_NAME);
						$outputlangs->load('productbatch');
						$outputlangs->load('dispatch@dispatch');

						foreach ($object->lines as &$line) {
							$details = new TRecepDetail;
							$TRecepDetail = $details->LoadAllBy($PDOdb, array('fk_commandedet' => $line->id));

							if (count($TRecepDetail) > 0) {
								$line->desc .= '<br>' . $outputlangs->trans('ProductsReceived') . ' :';

								foreach ($TRecepDetail as $detail) {

									$asset = new TAsset;
									$asset->loadBy($PDOdb, $detail->serial_number, 'serial_number');
									$asset->load_asset_type($PDOdb);
									$this->_addAssetToLineDesc($line, $detail, $asset, $outputlangs);
								}
							}

						}
					}
				}

				if (!empty($fkExpeditionLine)) {
					$TRecepDetail = $details->LoadAllBy($PDOdb, array('fk_expeditiondet' => $fkExpeditionLine));

					if (count($TRecepDetail) > 0) {
						if (!empty($line->description) && $line->description != $line->desc)
							$line->desc .= $line->description . '<br />'; // Sinon Dans certains cas desc écrase description
						$line->desc .= '<br>' . $outputlangs->trans('ProductsSent') . ' :';

						$TCompareDetails = array();

						foreach ($TRecepDetail as $detail) {
							// Grouping with conf
							if (!empty($conf->global->DISPATCH_GROUP_DETAILS_ON_PDF) && intval($detail->fk_asset) > 0) {

								$asset = new TAsset;
								$asset->load($PDOdb, $detail->fk_asset);
								$newComparaison = $this->getArrayForAssetToLineDescCompare($detail, $asset, $outputlangs);

								$isGrouped = false;
								//This condition exists for the first case : key = 0
								if (!empty($TCompareDetails)) {
									foreach ($TCompareDetails as $compKey => $compareDetail) {
										//Comparing lot numbers between them
										$resComp = array_diff_assoc($newComparaison, $compareDetail->TCompare);
										if (empty($resComp)) {
											$isGrouped = true;
											$TCompareDetails[$compKey]->total_weight_reel += doubleval($detail->weight_reel);
											break;
										}
									}
								}

								if (!$isGrouped) {
									//Creation of the first element
									$compareDetail = new stdClass();
									$compareDetail->total_weight_reel = doubleval($line->qty);
									$compareDetail->TCompare = $newComparaison;
									$TCompareDetails[] = $compareDetail;
								}
							}
							else {
								$asset = new TAsset;
								$asset->loadBy($PDOdb, $detail->lot_number, 'lot_number');
								$asset->load_asset_type($PDOdb);
								$this->_addAssetToLineDesc($line, $detail, $asset, $outputlangs);
							}
						}

						if (!empty($TCompareDetails)) {
							foreach ($TCompareDetails as $compareDetail) {
								$this->_addAssetGroupToLineDesc($line, $compareDetail, $outputlangs);
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @param $detail
	 * @param $asset
	 * @param $outputlangs
	 * @return array containing unite and lot number if there is a lot, and unite, lot number and serial number if not
	 */
	public function getArrayForAssetToLineDescCompare($detail, $asset, $outputlangs)
	{
		$unite = (($asset->assetType->measuring_units == 'unit') ? $outputlangs->trans('Assetunit_s') : measuring_units_string($detail->weight_reel_unit, $asset->assetType->measuring_units));

		if (!empty($asset->lot_number))
		{
			$forCompare = array(
				'unite' => $unite,
				'lot_number' => $asset->lot_number
			);
		}
		else // Case without lot
		{
			$forCompare = array(
				'unite' => $unite,
				'lot_number' => $asset->lot_number,
				'serial_number' => $asset->serial_number
			);
		}

		if (!empty($conf->global->ASSET_SHOW_DLUO) && empty($conf->global->DISPATCH_HIDE_DLUO_PDF) && !empty($asset->date_dluo)) {
			$forCompare['DateDluo'] = $asset->get_date('dluo');
		}

		return $forCompare;
	}

	/**
	 * @param $line
	 * @param $detail
	 * @param $asset
	 * @param Translate $outputlangs
	 */
	function _addAssetToLineDesc(&$line, $detail, $asset, Translate $outputlangs)
	{
		global $conf;

		$unite = (($asset->assetType->measuring_units == 'unit') ? $outputlangs->trans('Assetunit_s') : measuring_units_string($detail->weight_reel_unit, $asset->assetType->measuring_units));

		if (empty($asset->lot_number)) {
			$desc = '<br>- ' . $outputlangs->trans('SerialNumberShort') . ' : ' . $asset->serial_number;
		} else {
			$desc = "<br>- " . $asset->lot_number . " x " . $detail->weight_reel . " " . $unite;
		}

		if (!empty($conf->global->ASSET_SHOW_DLUO) && empty($conf->global->DISPATCH_HIDE_DLUO_PDF) && !empty($asset->date_dluo)) {
			$desc .= ' (' . $outputlangs->trans('EatByDate') . ' : ' . $asset->get_date('dluo') . ')';
		}

		$line->desc .= $desc;
	}

	/**
	 * @param $line
	 * @param $compareDetail
	 * @param $outputlangs
	 */
	function _addAssetGroupToLineDesc(&$line, $compareDetail, $outputlangs)
	{
		global $conf;
		$unite = $compareDetail->TCompare['unite'];
		$serial_number = $compareDetail->TCompare['serial_number'];
		$lot_number = $compareDetail->TCompare['lot_number'];

		if (empty($lot_number)) {
			$desc = '<br>- ' . $outputlangs->trans('SerialNumberShort') . ' : ' . $serial_number;
		} else {
			$desc = "<br>- " . $lot_number . " x " . $compareDetail->total_weight_reel . " " . $unite;
		}

		if (!empty($conf->global->ASSET_SHOW_DLUO) && empty($conf->global->DISPATCH_HIDE_DLUO_PDF) && !empty($compareDetail->TCompare['DateDluo'])) {
			$desc .= ' (' . $outputlangs->trans('EatByDate') . ' : ' . $compareDetail->TCompare['DateDluo'] . ')';
		}

		$line->desc .= $desc;
	}


	/*
	 * Overloading the addMoreActionsButtons function
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreColsOnCardTable(&$parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $langs, $db;

		if (intval(DOL_VERSION) < 11 && empty($conf->global->DISPATCH_SHOW_BATCH_COL_IN_CARD)) {
			return 0;
		}

		if (empty($conf->assetatm->enabled)) {
			return 0;
		}


		$contextArray = explode(':', $parameters['context']);

		if (in_array('expeditioncard', $contextArray)) {

			define('INC_FROM_DOLIBARR', true);
			dol_include_once('/dispatch/config.php');
			dol_include_once('/' . ATM_ASSET_NAME . '/class/asset.class.php');
			dol_include_once('/dispatch/class/dispatchdetail.class.php');
			dol_include_once('/dispatch/class/dispatchasset.class.php');

			$expedition = $object;

			$jsonObjectData = array();
			foreach ($object->lines as $i => $line) {
				$jsonObjectData[$line->id] = new stdClass();
				$jsonObjectData[$line->id]->id = $line->id;

				$product = new Product($db);
				if (!empty($line->fk_product)) {
					$product->fetch($line->fk_product);
				}

				$jsonObjectData[$line->id]->batch = '';

				// GET DISPATCH INFOS
				$PDOdb = new TPDOdb;
				$details = new TDispatchDetail();

				$fkExpeditionLine = $line->id;

				if (get_class($object) == 'Livraison') {
					$fkExpeditionLine = 0;

					foreach ($expedition->lines as $lineExpe) {
						if ($lineExpe->fk_origin_line == $line->fk_origin_line) { // La ligne d'origine de la livraison est la ligne de commande et non la ligne d'expédition
							$fkExpeditionLine = $lineExpe->id;
							break;
						}
					}
				}

				if (!empty($fkExpeditionLine)) {
					$TRecepDetail = $details->LoadAllBy($PDOdb, array('fk_expeditiondet' => $fkExpeditionLine));

					if (count($TRecepDetail) > 0) {
						if (!empty($line->description) && $line->description != $line->desc)
							$line->desc .= $line->description . '<br />'; // Sinon Dans certains cas desc écrase description
						$batch = '';

						foreach ($TRecepDetail as $detail) {
							$asset = new TAsset;
							$asset->load($PDOdb, $detail->fk_asset);
							$asset->load_asset_type($PDOdb);

							$unite = (($asset->assetType->measuring_units == 'unit') ? 'unité(s)' : measuring_units_string($detail->weight_reel_unit, $asset->assetType->measuring_units));

							if (empty($asset->lot_number)) {
								$batch = $asset->serial_number;
							} else {
								$batch = $asset->lot_number . " x " . $detail->weight_reel . " " . $unite;
							}

							if (!empty($conf->global->ASSET_SHOW_DLUO) && empty($conf->global->DISPATCH_HIDE_DLUO_PDF) && !empty($asset->date_dluo))
								$batch .= ' (DLUO : ' . $asset->get_date('dluo') . ')';
							$jsonObjectData[$line->id]->batch = $batch;
						}
					}
				}

			}

			$totalAddedCols = 1;
			?>
			<script>

				$(document).ready(function () {
					var jsonObjectData = <?php print json_encode($jsonObjectData); ?> ;

					// ADD NEW COLS
					$("#tablelines tr").each(function (index) {
						//console.log($( this ).data( "product_type" ));
						if ($(this).hasClass("liste_titre")) {
							// PARTIE TITRE
							$('<td align="center" class="linecoldispatch"><?php print $langs->transnoentities('colDispatch'); ?></td>').insertAfter($(this).find("td:first"));
						} else if ($(this).data("product_type") == "9") {
							// pas encore géré mais au cas où
							$(this).find("td[colspan]:first").attr('colspan', parseInt($(this).find("td[colspan]:first").attr('colspan')) + <?php print $totalAddedCols; ?>  );
						} else {
							// PARTIE LIGNE

							$('<td align="center" class="linecoldispatch"></td>').insertAfter($(this).find("td.linecoldescription"));

							if ($(this).attr("data-element") == "extrafield") {
								$(this).find("td[colspan]:first").attr('colspan', parseInt($(this).find("td[colspan]:first").attr('colspan')) + <?php print $totalAddedCols + 1; ?>  );
							}
						}
					});

					// Affichage des données
					$.each(jsonObjectData, function (i, item) {
						// Dimensions
						$("#row-" + jsonObjectData[i].id + " .linecoldispatch:first").html(jsonObjectData[i].batch);

					});

				});
			</script>
			<?php
		}

		return 0;
	}
}
