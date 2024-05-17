<?php
/* Copyright (C) 2019 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    class/actions_linkcomercial.class.php
 * \ingroup linkcomercial
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

/**
 * Class Actionslinkcomercial
 */
class Actionslinkcomercial
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;
	/**
	 * @var string Error
	 */
	public $error = '';
	/**
	 * @var array Errors
	 */
	public $errors = array();


	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;


	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param array() $parameters Hook metadatas (context, etc...)
	 * @param CommonObject $object The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string $action Current action (if set). Generally create or edit or null
	 * @param HookManager $hookmanager Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{


		global $langs, $user, $conf, $db;
		require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
		require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
		$jsscript = '';

		$actions_exclude = array('create', 'modif');

		if ($action == 'changetiers' && GETPOST('socid')) {

			if ($object->element == 'invoice_supplier' || $object->element=='order_supplier') {
				$object->fk_soc = GETPOST('socid');
			} else {
				$object->socid = GETPOST('socid');
			}
			if (method_exists($object, 'update')) {
				if (DOL_VERSION >= 7) {
					$object->update($user);
					if ($object->element == 'order_supplier') {
						$object->update_price();
					}
				} else {
					$object->update();
				}
			} else {
				if ($object->id){
					$sql = "UPDATE " . MAIN_DB_PREFIX . $object->table_element . " SET fk_soc = " . GETPOST('socid') . " WHERE rowid = " . $object->id;
					$resql = $this->db->query($sql);
					if ($resql) {
						//TODO Alberto buscar cada linea y update_price
						//$object->update_price('','auto');
						if ($object->element == 'order_supplier') {
							$num = count($object->lines);
							if ($num) {
								foreach ($object->lines as $line) {
									$object->deleteline($line->id);
									$prod = new Product($this->db);
									$prod->get_buyprice(0, $line->qty, $line->fk_product, 'none', GETPOST('socid'));

									$result = $object->addline($line->desc,
										$line->pu_ht,
										$line->qty,
										$line->tva_tx,
										$line->localtax1_tx,
										$line->localtax2_tx,
										$line->fk_product,
										$line->remise_percent,
										$prod->ref_supplier,
										$line->remise_percent,
										'HT',
										0,
										0,
										0
									);
									if ($result == -1) {
										setEventMessage('Se ha eliminado el producto ' . $line->product_ref . ' - ' . $line->libelle . ' del pedido porque no tiene la cantidad minima para este proveedor', 'errors');
									}

								}
							}
						} elseif ($object->element = 'ordercard') {
							global $mysoc;
							$num = count($object->lines);
							if ($num) {
								foreach ($object->lines as $line) {
									//$object->deleteline($line->id);

									//$object->element = $object->table_element;
									if (!empty($line->product_ref)) {
										$prod = new Product($db);
										$prod->fetch($line->fk_product);
										$tva_tx = get_default_tva($mysoc, $object->thirdparty, $prod->id);
										$tva_npr = get_default_npr($mysoc, $object->thirdparty, $prod->id);
										if (empty($tva_tx)) $tva_npr = 0;

										$pu_ht = $prod->price;
										$pu_ttc = $prod->price_ttc;
										$price_min = $prod->price_min;
										$price_base_type = $prod->price_base_type;
									}
									$object->updateline($line->id, $line->desc, $pu_ht, $line->qty, $line->remise_percent, $tva_tx, $line->localtax1_tx, $line->localtax2_tx, '', 'HT', 0, 0, 0, 0, 0, $line->pa_ht, '', 0, '', '', '', '', $pu_ht);
									/*$result = $object->addline(
										$line->libelle,
										$pu_ht,
										$line->qty,
										$tva_tx,
										$line->localtax1_tx,
										$line->localtax2_tx,
										$line->fk_product,
										$line->remise_percent,
										0,
										$line->remise_percent,
										'HT',
										$pu_ttc,
										0,
										0
									);*/
									if ($result == -1) {
										setEventMessage('Se ha eliminado el producto ' . $line->product_ref . ' - ' . $line->libelle . ' del pedido porque no tiene la cantidad minima para este proveedor', 'errors');
									}

								}
							}
						}
						//  dolibarr_set_const($this->db, 'MAIN_PROPAGATE_CONTACTS_FROM_ORIGIN', 0,'chaine',1,'',0);
						$this->db->commit();
					} else {
						$this->db->rollback();
						dolibarr_set_const($this->db, 'MAIN_PROPAGATE_CONTACTS_FROM_ORIGIN', 1, 'chaine', 1, '', 0);
						dol_print_error($this->db);
						return 0;
					}
				}

			}
			//dolibarr_set_const($this->db, 'MAIN_PROPAGATE_CONTACTS_FROM_ORIGIN', 1,'chaine',1,'',0);

			//$object->delete_linked_contact('external');

			//$object->generateDocument('', $langs);
		}

		return 0;
	}


	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param array() $parameters Hook metadatas (context, etc...)
	 * @param CommonObject $object The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string $action Current action (if set). Generally create or edit or null
	 * @param HookManager $hookmanager Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/*
		print_r($parameters);
		print_r($object);
		echo "action: " . $action;
		*/

		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {  // do something only for the context 'somecontext1' or 'somecontext2'

			foreach ($parameters['toselect'] as $objectid) {
				// Do action on each object id

			}
		}

		if (!$error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0;                                    // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the addMoreMassActions function : replacing the parent's function with the one below
	 *
	 * @param array() $parameters Hook metadatas (context, etc...)
	 * @param CommonObject $object The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string $action Current action (if set). Generally create or edit or null
	 * @param HookManager $hookmanager Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2')))  // do something only for the context 'somecontext'
		{
			$this->resprints = '<option value="0"' . ($disabled ? ' disabled="disabled"' : '') . '>' . $langs->trans("linkcomercialMassAction") . '</option>';
		}

		if (!$error) {
			return 0;                                    // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	function addMoreActionsButtons($parameters, $object, $action, $hookmanager)
	{

		global $db, $conf, $langs, $user;
		$langs->load("linkcomercial@linkcomercial");
		$TContext = explode(':', $parameters['context']);
		if (in_array('supplier_proposalcard', $TContext)) {
			if ($object->statut == 3) {
				print '<div class="inline-block divButAction"><a class="butAction" style="background: #da8f27;margin-right: 3px;margin-left: 3px;" href="' . DOL_URL_ROOT . '/custom/linkcomercial/linkcomercialindex.php?action=presupcliente&origin=supplier_proposal&originid=' . $object->id . '&socid=' . $object->thirdparty->id . '">' . $langs->trans('toPresupuestoCliente') . '</a></div>';
			}
		}
		//TODO Razmi verficar aqui Stock de cada lines crear function para esto
		if (in_array('ordercard', $TContext)) {
			$stockenought = $this->VerifyStock($object, $db);

			//$colorbtn = $stockenought ? "#27C106" : "#FF0733";  //Razmi
			$colorbtn = $stockenought ? "#8579ba" : "#27C106";	//ALAN
			$title=$stockenought?"":"title=\"There are Products Out of Stock\"";
			if ($object->statut == 1) {
				print '<div class="inline-block divButAction" '.$title.'><a class="butAction" style="background: ' . $colorbtn . ';margin-right: 3px;margin-left: 3px;" href="' . DOL_URL_ROOT . '/custom/linkcomercial/linkcomercialindex.php?action=pedidocliente&origin=commande&originid=' . $object->id . '&socid=' . $object->thirdparty->id . '">' . $langs->trans('toPedidoProveedor') . '</a></div>';
			}
		}

	}

	private function VerifyStock($object, $db)
	{
		$generic_product = new Product($db);
       $sumproducts=0;
		foreach ($object->lines as $line) {
			//TODO Stock menos
			$generic_product->id = $line->fk_product;
			$generic_product->load_stock('nobatch');
			if ($line->qty > $generic_product->stock_reel) {
				return false;
			}
		}
		return true;
	}

	function printCommonFooter($parameters, &$object, &$action, $hookmanager)
	{

		global $langs, $user, $conf;
		require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
		require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
		require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
		require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
		require_once DOL_DOCUMENT_ROOT . '/supplier_proposal/class/supplier_proposal.class.php';
		require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.facture.class.php';
		require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.commande.class.php';
		require_once DOL_DOCUMENT_ROOT . '/expedition/class/expedition.class.php';

		$idparam = 'id';
		$idextparam = 'socid';
		$paramcompany = 'client>0';

		switch ($parameters['currentcontext']) {
			case 'ordercard' :
				$object = new Commande($this->db);
				$object->fetch(GETPOST('id'));
				break;
			case 'propalcard' :
				$object = new Propal($this->db);
				$object->fetch(GETPOST('id'));
				break;
			case 'invoicecard' :
				$object = new Facture($this->db);
				$object->fetch(GETPOST('facid'));
				$idparam = 'facid';
				break;
			case 'supplier_proposalcard' :
				$object = new SupplierProposal($this->db);
				$object->fetch(GETPOST('id'));
				$paramcompany = 'fournisseur=1';
				break;
			case 'ordersuppliercard' :
				$object = new CommandeFournisseur($this->db);
				$object->fetch(GETPOST('id'));
				$paramcompany = 'fournisseur=1';
				break;
			case 'invoicesuppliercard' :
				$object = new FactureFournisseur($this->db);
				$object->fetch(GETPOST('facid'));
				$idparam = 'facid';
				$paramcompany = 'fournisseur=1';
				break;
			case 'expeditioncard' :
				$object = new Expedition($this->db);
				$object->fetch(GETPOST('id'));
				$idparam = 'id';
				break;
		}

		$jsscript = '';

		$actions_exclude = array('create', 'modif');

		$element_authorized = array('propal', 'commande', 'supplier_proposal', 'order_supplier');


		if (1 || ($action == '' || !in_array($action, $actions_exclude)) && (in_array($object->element, $element_authorized))) {

			$jsscript .= '<script>';

			$form = new Form($this->db);

			// Select changement tiers
			$formtiers = '<form method="post" id="formchangetiers" action="' . $_SERVER['PHP_SELF'] . '?' . $idparam . '=' . GETPOST($idparam) . '">' . PHP_EOL;
			$formtiers .= '<input type="hidden" name="action" value="changetiers">' . PHP_EOL;
			$formtiers .= '<input type="hidden" name="originid" value="'.GETPOST('originid').'">' . PHP_EOL;
			$formtiers .= '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">' . PHP_EOL;
			$formtiers .= $form->select_company($object->{$idextparam}, 'socid', $paramcompany, '', 0, 0, 0) . PHP_EOL;
			$formtiers .= '<input type="submit" class="button valignmiddle" value="' . $langs->trans("Modify") . '">' . PHP_EOL;
			$formtiers .= '<input type="submit" name="cancel" class="button valignmiddle" id="changetierscancelbtn" value="' . $langs->trans("Cancel") . '">' . PHP_EOL;
			$formtiers .= '</form>' . PHP_EOL;

			$matches = null;
			$returnValue = preg_match_all('#<script(.*?)>(.*?)</script>#is', $formtiers, $matches);

			$jsscript .= 'var urlConf = "' . $_SERVER['PHP_SELF'] . '";' . PHP_EOL;

			$jsscript .= 'var changeTiers = true;' . PHP_EOL;
			$jsscript .= 'var pictoChangeTiers = "' . DOL_URL_ROOT . '/theme/eldy/img/edit.png' . '";' . PHP_EOL;
			$jsscript .= "var formTiers = `" . PHP_EOL
				. preg_replace('#<script(.*?)>(.*?)</script>#is', '', $formtiers) . PHP_EOL
				. " ` ;" . PHP_EOL;


			if (isset($matches[2]) && isset($matches[2][0])) {
				$jsscript .= "var scriptTiers = `" . PHP_EOL
					. $matches[2][0] . PHP_EOL
					. " ` ;" . PHP_EOL;
			}

			$jsscript .= '</script>';

		}

		echo $jsscript;
		return 0;
	}

	function formAddObjectLine($parameters, $object, $action)
	{

		print "<script>";

		print '$("#prod_entry_mode_free").hide();';
		print '$("#prod_entry_mode_predef").hide();';
		print '$(\'label[for="prod_entry_mode_predef"]\').hide();';
		print '$("#idprod").hide();';
		// pour les fournisseurs
		print '$("#idprodfournprice").hide();';
		print '$(\'input[name=prod_entry_mode][value="free"]\').attr("checked", "checked");';


		print "</script>";

		return 0;
	}


}
