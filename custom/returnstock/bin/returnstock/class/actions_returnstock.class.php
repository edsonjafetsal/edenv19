<?php
/* Copyright (C) 2021 Alan Montoya UBE
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    returnstock/class/actions_returnstock.class.php
 * \ingroup returnstock
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

/**
 * Class ActionsReturnStock
 */
class ActionsReturnStock
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
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
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}


	/**
	 * Execute action
	 *
	 * @param	array			$parameters		Array of parameters
	 * @param	CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	string			$action      	'add', 'update', 'view'
	 * @return	int         					<0 if KO,
	 *                           				=0 if OK but we want to process standard actions too,
	 *                            				>0 if OK and we want to replace standard actions.
	 */
	public function getNomUrl($parameters, &$object, &$action)
	{
		global $db, $langs, $conf, $user;
		$this->resprints = '';
		return 0;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2')))	    // do something only for the context 'somecontext1' or 'somecontext2'
		{
			// Do what you want here...
			// You can for example call global vars like $fieldstosearchall to overwrite them, or update database depending on $action and $_POST values.
		}

		if (!$error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}


		/*
		 * AGREGAR  AQUI ACTIONS
		 *
		 * */

		setEventMessages($valorref.' : Updated Stock', '', 'mesgs');



	}


	/**
	 * Overloading the doMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{
			foreach ($parameters['toselect'] as $objectid)
			{
				// Do action on each object id
			}
		}

		if (!$error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the addMoreMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter
		$disabled = 1;

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{
			$this->resprints = '<option value="0"'.($disabled ? ' disabled="disabled"' : '').'>'.$langs->trans("ReturnStockMassAction").'</option>';
		}

		if (!$error) {
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}



	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$object		   	Object output on PDF
	 * @param   string	$action     	'add', 'update', 'view'
	 * @return  int 		        	<0 if KO,
	 *                          		=0 if OK but we want to process standard actions too,
	 *  	                            >0 if OK and we want to replace standard actions.
	 */
	public function beforePDFCreation($parameters, &$object, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0; $deltemp = array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{
		}

		return $ret;
	}

	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$pdfhandler     PDF builder handler
	 * @param   string	$action         'add', 'update', 'view'
	 * @return  int 		            <0 if KO,
	 *                                  =0 if OK but we want to process standard actions too,
	 *                                  >0 if OK and we want to replace standard actions.
	 */
	public function afterPDFCreation($parameters, &$pdfhandler, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0; $deltemp = array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {
			// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}



	/**
	 * Overloading the loadDataForCustomReports function : returns data to complete the customreport tool
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function loadDataForCustomReports($parameters, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$langs->load("returnstock@returnstock");

		$this->results = array();

		$head = array();
		$h = 0;

		if ($parameters['tabfamily'] == 'returnstock') {
			$head[$h][0] = dol_buildpath('/module/index.php', 1);
			$head[$h][1] = $langs->trans("Home");
			$head[$h][2] = 'home';
			$h++;

			$this->results['title'] = $langs->trans("ReturnStock");
			$this->results['picto'] = 'returnstock@returnstock';
		}

		$head[$h][0] = 'customreports.php?objecttype='.$parameters['objecttype'].(empty($parameters['tabfamily']) ? '' : '&tabfamily='.$parameters['tabfamily']);
		$head[$h][1] = $langs->trans("CustomReports");
		$head[$h][2] = 'customreports';

		$this->results['head'] = $head;

		return 1;
	}



	/**
	 * Overloading the restrictedArea function : check permission on an object
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int 		      			  	<0 if KO,
	 *                          				=0 if OK but we want to process standard actions too,
	 *  	                            		>0 if OK and we want to replace standard actions.
	 */
	public function restrictedArea($parameters, &$action, $hookmanager)
	{
		global $user;

		if ($parameters['features'] == 'myobject') {
			if ($user->rights->returnstock->myobject->read) {
				$this->results['result'] = 1;
				return 1;
			} else {
				$this->results['result'] = 0;
				return 1;
			}
		}

		return 0;
	}

	public function addMoreMassActions1($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter
		$disabled = 1;

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{
			$this->resprints = '<option value="0"'.($disabled ? ' disabled="disabled"' : '').'>'.$langs->trans("ReturnStockMassAction").'</option>';
		}

		if (!$error) {
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	function addMoreActionsButtons($parameters, $object, $action, $hookmanager)
	{

		global $db, $conf, $langs, $user;
		$langs->load("returnstock@returnstock");
		$TContext = explode(':', $parameters['context']);



		if (in_array('invoicecard', $TContext))
		{

			if ($action == 'Modifystock')
			{
				//print_r($object->lines);
				$prods=$object->lines;
				foreach($prods as $prod):
					$sql_prod_line = "Select  lps.reel from ".MAIN_DB_PREFIX."product_stock AS lps WHERE fk_product= ".$prod->fk_product;
					$resql12 = $db->query($sql_prod_line);
					if ($resql12) {
						$i = 0;
						$num = $db->num_rows($resql12);

						while ($i < $num) {
							$objp = $db->fetch_object($resql12);

							$prod_id = $prod->fk_product;
							$stock1 = $prod->qty;
							$nuevostock = $objp->reel + $stock1;

							$sql_update= " update ".MAIN_DB_PREFIX."product_stock set reel= ".$nuevostock." where fk_product =".$prod->fk_product;
							$resql11 = $db->query($sql_update);
							$sql_ref = "SELECT  lp.ref FROM ".MAIN_DB_PREFIX."product lp  where rowid = ".$prod_id;
							$redprod = $db->query($sql_ref);
							$num = $db->num_rows($redprod);
							$refObjet = $db->fetch_object($redprod);
							foreach($refObjet as $valorref):

							endforeach;
							if ($resql11)
							{

								setEventMessages('Ref: '.$valorref.' : Updated Stock', '', 'mesgs');
								/*actualizamos return_cash para desabilitar la devolucion doble*/
								$sqlverifca = "SELECT  lfe.return_cash FROM ".MAIN_DB_PREFIX."facture_extrafields lfe  where fk_object =".$object->id;
								$verificaquery = $db->query($sqlverifca);
								$consul=$db->fetch_object($resql122);

								if($consul)
								{
								$sql_update= "UPDATE ".MAIN_DB_PREFIX."facture_extrafields  set return_cash= 1 where fk_object =".$object->id;
								$resql11 = $db->query($sql_update);
								}
								else
								{
									$sql_update= "INSERT INTO ".MAIN_DB_PREFIX."facture_extrafields  ( fk_object, jobaddress ,return_cash) VALUES ( ".$object->id.", '', 1)";
									$resql11 = $db->query($sql_update);
								}
						
								$sql_element = "select fk_element, elementtype from ".MAIN_DB_PREFIX."actioncomm where label like '%".$object->ref."%'";
								$Elements = $db->query($sql_element);
								if ($Elements) {
																		
									foreach ($Elements as $e)
									{


									}
									
								}	
								$a1=$e['fk_element'];

								$a2 =$e['elementtype'];
								


								$sql_entrepot = " SELECT fk_entrepot from ".MAIN_DB_PREFIX."product_stock  where fk_product = ".$prod->fk_product;
								//" SELECT fk_entrepot llx_product_stock  where fk_product = 4646"
								$res_entrepot = $db->query($sql_entrepot);
								if ($res_entrepot) {
																		
									foreach ($res_entrepot as $almacenes)
									{


									}
									
								}	
								$almacen = $almacenes['fk_entrepot'];
								$fact = $prod['fk_facture'];
								
								$sql_stock_moment = "INSERT INTO ".MAIN_DB_PREFIX."stock_mouvement (rowid, tms, datem, fk_product, fk_entrepot, price, type_mouvement, fk_user_author, label, fk_origin, origintype, fk_projet)"; 
								$sql_stock_moment .= "VALUES (null,  curdate(), curdate(), ".$prod_id.", ".$almacen.",  0.00, 0,  ".$object->fk_user_author.",  'Invoice ".$object->ref." changed to stock',  ".$fact.", 'facture', 0);";
								$res_stock_moment = $db->query($sql_stock_moment);

								//action code 50 = AC_OTH -> Other (manually inserted events)
								$sql_insert_event = "INSERT INTO ".MAIN_DB_PREFIX."actioncomm (id, ref_ext, entity, datep, datep2, fk_action, code, datec, tms, fk_user_mod, fk_soc, ";
								$sql_insert_event .= "fk_parent, fk_user_action, percent, label, fk_element, elementtype) VALUES (null, null, 1, curdate(), curdate(), ";
								$sql_insert_event .= "50, 'PRODUCT_RETURN_STOCK ', curdate(), curdate(), ".$object->fk_user_author.", ".$object->socid.", 0, ".$object->fk_user_author.", -1, ";
								$sql_insert_event .= "'".$a2." ".$object->ref." changed to stock Ref: ".$valorref." : ".$stock1."', ".$a1.", '".$a2."')";
								$res_insert_event = $db->query($sql_insert_event);
								
								// "INSERT INTO llx_stock_mouvement (rowid, tms, datem, fk_product, fk_entrepot, price, type_mouvement, fk_user_author, label, fk_origin, origintype, fk_projet)VALUES (null,  curdate(), curdate(), 4646, ,  0.00, 0,  29,  'Invoice AV-102721-00111 changed to stock',  2535, 'facture', 0);"
								// "INSERT INTO llx_stock_mouvement (rowid, tms, datem, fk_product, fk_entrepot, price, type_mouvement, fk_user_author, label, fk_origin, origintype, fk_projet)VALUES (null,  curdate(), curdate(), 4646, ,  0.00, 0,  29,  'Invoice AV-102721-00111 changed to stock',  2535, 'facture', 0);"
								$sql_insert_event = "INSERT INTO ".MAIN_DB_PREFIX."actioncomm (id, ref_ext, entity, datep, datep2, fk_action, code, datec, tms, fk_user_mod, fk_soc, ";
								$sql_insert_event .= "fk_parent, fk_user_action, percent, label, fk_element, elementtype) VALUES (null, null, 1, curdate(), curdate(), ";
								$sql_insert_event .= "50, 'PRODUCT_RETURN_STOCK ', curdate(), curdate(), ".$object->fk_user_author.", ".$object->socid.", 0, ".$object->fk_user_author.", -1, ";
								$sql_insert_event .= "'".$a2." ".$object->ref." changed to stock Ref: ".$valorref." : ".$stock1."', ".$prod->fk_product.", 'product')";
								$res = $db->query($sql_insert_event);
							/*
							"INSERT INTO llx_actioncomm (id, ref_ext, entity, datep, datep2, fk_action, code, datec, tms, fk_user_mod, fk_soc, fk_parent, fk_user_action, percent, label, fk_element, elementtype) 
							VALUES (null, null, 1, curdate(), curdate(), 50, 'PRODUCT_RETURN_STOCK ', curdate(), curdate(), 29, 176, 0, 29, -1, 'invoice AV-102721-00111 changed to stock Ref: 000001  ', 2539, 'invoice')"
							
							*/


							}

							$i++;
						}
					}


				endforeach;


			}

			$stockenought = $this->VerifyStock($object, $db);
			$colorbtn = $stockenought ? "#27C106" : "#FF0733";
			$title=$stockenought?"":"title=\"There are Products Out of Stock\"";
			//if ($object->statut == 1) {
			//if ($object->type == Facture::TYPE_CREDIT_NOTE && $object->statut == Facture::STATUS_VALIDATED && $object->paye == 0 && $usercanissuepayment)
			//print '<div class="inline-block divButAction" '.$title.'><a class="butAction"' . DOL_URL_ROOT . '/custom/returnstock/returnstockindex.php?action=RETURNSTOCK&origin=invoice&originid=' . $object->id . '&socid=' . $object->thirdparty->id . '">' . $langs->trans('ReturnStockMoule') . '</a></div>';
			$active = $conf->global->RETURN_STOCK_IN_CREDIT_NOTE;






			if($active == 1 )
			{   //if ($object->statut == Facture::STATUS_CLOSED || ($object->statut == Facture::STATUS_ABANDONED && ($object->close_code != 'replaced' || $object->getIdReplacingInvoice() == 0)) || ($object->statut == Facture::STATUS_VALIDATED && $object->paye == 1)) {    // ($object->statut == 1 && $object->paye == 1) should not happened but can be found when data are corrupted

				if ($object->type == Facture::TYPE_CREDIT_NOTE && $object->statut == Facture::STATUS_VALIDATED && $object->paye == 0 )
				{

					$sql_prod_line = "Select  lpe.return_cash from ".MAIN_DB_PREFIX."facture_extrafields AS lpe WHERE fk_object= ".$object->id;
					$resql122 = $db->query($sql_prod_line);
					// $return1 = $db->fetch_object($resql122);

					foreach($resql122 as $retu)
					{
						foreach ($retu as $a)
						{

						}


					}

					if ($a == null)
					{
						print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?facid='.$object->id.'&action=Modifystock&mode=init#formmailbeforetitle">'.$langs->trans('Return Stock').'</a>';
						//print '<a class="butAction'.($conf->use_javascript_ajax ? ' reposition' : '').'" href="'.$_SERVER["PHP_SELF"].'?facid='.$object->id.'&amp;action=draftstock#draftstock">'.$langs->trans(' Draft stock').'</a>';
						/*
						 * boton para almacenes
						 * */


					} else {
						// print '<a class="butAction" href="'.DOL_URL_ROOT.'/compta/paiement.php?facid='.$object->id.'&amp;action=create&amp;accountid='.$object->fk_account.'">'.$langs->trans('DoPaymentBack').'</a>';
						print '<a class="butActionRefused classfortooltip" href="'.$_SERVER['PHP_SELF'].'?facid='.$object->id.'&action=Modifystock&mode=init#formmailbeforetitle">'.$langs->trans('Return Stock').'</a>';
						$action = 'Modifystock';
					}
					/**
					 * CONFIGURACION DE BOTONES  invoice
					 *
					 */


					 /**
					 * CONFIGURACION DE BOTONES  invoice
					 *
					 */




					$b= $object->statut ;
				}




				/*
				 * botones
				 * */

				$usercanread = $user->rights->facture->lire;
				$usercancreate = $user->rights->facture->creer;
				$usercanissuepayment = $user->rights->facture->paiement;
				$usercandelete = $user->rights->facture->supprimer;
				$usercanvalidate = ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && $usercancreate) || (!empty($conf->global->MAIN_USE_ADVANCED_PERMS) && !empty($user->rights->facture->invoice_advance->validate)));
				$usercansend = (empty($conf->global->MAIN_USE_ADVANCED_PERMS) || $user->rights->facture->invoice_advance->send);
				$usercanreopen = (empty($conf->global->MAIN_USE_ADVANCED_PERMS) || $user->rights->facture->invoice_advance->reopen);
				$usercanunvalidate = ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && !empty($usercancreate)) || (!empty($conf->global->MAIN_USE_ADVANCED_PERMS) && !empty($user->rights->facture->invoice_advance->unvalidate)));

				$usercanproductignorepricemin = ((!empty($conf->global->MAIN_USE_ADVANCED_PERMS) && empty($user->rights->produit->ignore_price_min_advance)) || empty($conf->global->MAIN_USE_ADVANCED_PERMS));
				$usercancreatemargin = $user->rights->margins->creer;
				$usercanreadallmargin = $user->rights->margins->liretous;
				$usercancreatewithdrarequest = $user->rights->prelevement->bons->creer;

					// Editer une facture deja validee, sans paiement effectue et pas exporte en compta
					if ($object->statut == Facture::STATUS_VALIDATED)
					{
						// We check if lines of invoice are not already transfered into accountancy
						$ventilExportCompta = $object->getVentilExportCompta();

						if ($ventilExportCompta == 0)
						{	$resteapayer = $object->total_ttc - $totalpaye;
							if (!empty($conf->global->INVOICE_CAN_ALWAYS_BE_EDITED) || ($resteapayer == price2num($object->total_ttc, 'MT', 1) && empty($object->paye)))
							{
								if (!$objectidnext && $object->is_last_in_cycle())
								{
									if ($usercanunvalidate)
									{
										print '<a class="butAction'.($conf->use_javascript_ajax ? ' reposition' : '').'" href="'.$_SERVER['PHP_SELF'].'?facid='.$object->id.'&amp;action=modif">'.$langs->trans('Modify').'</a>';
									} else {
										print '<span class="butActionRefused classfortooltip" title="'.$langs->trans("NotEnoughPermissions").'">'.$langs->trans('Modify').'</span>';
									}
								} elseif (!$object->is_last_in_cycle()) {
									print '<span class="butActionRefused classfortooltip" title="'.$langs->trans("NotLastInCycle").'">'.$langs->trans('Modify').'</span>';
								} else {
									print '<span class="butActionRefused classfortooltip" title="'.$langs->trans("DisabledBecauseReplacedInvoice").'">'.$langs->trans('Modify').'</span>';
								}
							}
						} else {
							print '<span class="butActionRefused classfortooltip" title="'.$langs->trans("DisabledBecauseDispatchedInBookkeeping").'">'.$langs->trans('Modify').'</span>';
						}
					}

					$discount = new DiscountAbsolute($db);
					$result = $discount->fetch(0, $object->id);

					// Reopen a standard paid invoice
					if ((($object->type == Facture::TYPE_STANDARD || $object->type == Facture::TYPE_REPLACEMENT)
							|| ($object->type == Facture::TYPE_CREDIT_NOTE && empty($discount->id))
							|| ($object->type == Facture::TYPE_DEPOSIT && empty($discount->id)))
						&& ($object->statut == Facture::STATUS_CLOSED || $object->statut == Facture::STATUS_ABANDONED || ($object->statut == 1 && $object->paye == 1))   // Condition ($object->statut == 1 && $object->paye == 1) should not happened but can be found due to corrupted data
						&& ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && $usercancreate) || $usercanreopen))				// A paid invoice (partially or completely)
					{
						if ($object->close_code != 'replaced' || (!$objectidnext)) 				// Not replaced by another invoice or replaced but the replacement invoice has been deleted
						{
							print '<a class="butAction'.($conf->use_javascript_ajax ? ' reposition' : '').'" href="'.$_SERVER['PHP_SELF'].'?facid='.$object->id.'&amp;action=reopen">'.$langs->trans('ReOpen').'</a>';
						} else {
							print '<span class="butActionRefused classfortooltip" title="'.$langs->trans("DisabledBecauseReplacedInvoice").'">'.$langs->trans('ReOpen').'</span>';
						}
					}

					// Validate
					if ($object->statut == Facture::STATUS_DRAFT && count($object->lines) > 0 && ((($object->type == Facture::TYPE_STANDARD || $object->type == Facture::TYPE_REPLACEMENT || $object->type == Facture::TYPE_DEPOSIT || $object->type == Facture::TYPE_PROFORMA || $object->type == Facture::TYPE_SITUATION) && (!empty($conf->global->FACTURE_ENABLE_NEGATIVE) || $object->total_ttc >= 0)) || ($object->type == Facture::TYPE_CREDIT_NOTE && $object->total_ttc <= 0))) {
						if ($usercanvalidate)
						{
							print '<a class="butAction'.($conf->use_javascript_ajax ? ' reposition' : '').'" href="'.$_SERVER["PHP_SELF"].'?facid='.$object->id.'&amp;action=valid">'.$langs->trans('Validate').'</a>';
						}
					}

					// Send by mail
					if (empty($user->socid)) {
						if (($object->statut == Facture::STATUS_VALIDATED || $object->statut == Facture::STATUS_CLOSED) || !empty($conf->global->FACTURE_SENDBYEMAIL_FOR_ALL_STATUS)) {
							if ($objectidnext) {
								print '<span class="butActionRefused classfortooltip" title="'.$langs->trans("DisabledBecauseReplacedInvoice").'">'.$langs->trans('SendMail').'</span>';
							} else {
								if ($usercansend) {
									print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?facid='.$object->id.'&action=presend&mode=init#formmailbeforetitle">'.$langs->trans('SendMail').'</a>';
								} else print '<a class="butActionRefused classfortooltip" href="#">'.$langs->trans('SendMail').'</a>';
							}
						}
					}

					// Request a direct debit order
					if ($object->statut > Facture::STATUS_DRAFT && $object->paye == 0 && $num == 0)
					{
						if ($resteapayer > 0)
						{
							if ($usercancreatewithdrarequest)
							{
								if (!$objectidnext && $object->close_code != 'replaced') 				// Not replaced by another invoice
								{
									print '<a class="butAction" href="'.DOL_URL_ROOT.'/compta/facture/prelevement.php?facid='.$object->id.'" title="'.dol_escape_htmltag($langs->trans("MakeWithdrawRequest")).'">'.$langs->trans("MakeWithdrawRequest").'</a>';
								} else {
									print '<span class="butActionRefused classfortooltip" title="'.$langs->trans("DisabledBecauseReplacedInvoice").'">'.$langs->trans('MakeWithdrawRequest').'</span>';
								}
							} else {
								//print '<a class="butActionRefused classfortooltip" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans("MakeWithdrawRequest").'</a>';
							}
						} else {
							//print '<a class="butActionRefused classfortooltip" href="#" title="'.dol_escape_htmltag($langs->trans("AmountMustBePositive")).'">'.$langs->trans("MakeWithdrawRequest").'</a>';
						}
					}

					// POS Ticket
					if (!empty($conf->takepos->enabled) && $object->module_source == 'takepos')
					{
						$langs->load("cashdesk");
						$receipt_url = DOL_URL_ROOT."/takepos/receipt.php";
						print '<a target="_blank" class="butAction" href="'.$receipt_url.'?facid='.$object->id.'">'.$langs->trans('POSTicket').'</a>';
					}

					// Create payment
					if ($object->type != Facture::TYPE_CREDIT_NOTE && $object->statut == 1 && $object->paye == 0 && $usercanissuepayment) {
						if ($objectidnext) {
							print '<span class="butActionRefused classfortooltip" title="'.$langs->trans("DisabledBecauseReplacedInvoice").'">'.$langs->trans('DoPayment').'</span>';
						} else {
							//if ($resteapayer == 0) {		// Sometimes we can receive more, so we accept to enter more and will offer a button to convert into discount (but it is not a credit note, just a prepayment done)
							//	print '<div class="inline-block divButAction"><span class="butActionRefused classfortooltip" title="' . $langs->trans("DisabledBecauseRemainderToPayIsZero") . '">' . $langs->trans('DoPayment') . '</span></div>';
							//} else {
							print '<a class="butAction" href="'.DOL_URL_ROOT.'/compta/paiement.php?facid='.$object->id.'&amp;action=create&amp;accountid='.$object->fk_account.'">'.$langs->trans('DoPayment').'</a>';
							//}
						}
					}

					// Reverse back money or convert to reduction
					if ($object->type == Facture::TYPE_CREDIT_NOTE || $object->type == Facture::TYPE_DEPOSIT || $object->type == Facture::TYPE_STANDARD || $object->type == Facture::TYPE_SITUATION) {
						// For credit note only
						if ($object->type == Facture::TYPE_CREDIT_NOTE && $object->statut == Facture::STATUS_VALIDATED && $object->paye == 0 && $usercanissuepayment)
						{	if ($a == 1)
							{
								if ($resteapayer == 0)
								{
									print '<span class="butActionRefused classfortooltip" title="'.$langs->trans("DisabledBecauseRemainderToPayIsZero").'">'.$langs->trans('DoPaymentBack').'</span>';
								} else {
									print '<a class="butAction" href="'.DOL_URL_ROOT.'/compta/paiement.php?facid='.$object->id.'&amp;action=create&amp;accountid='.$object->fk_account.'">'.$langs->trans('DoPaymentBack').'</a>';
								}

							}

						}

						// For standard invoice with excess received
						if (($object->type == Facture::TYPE_STANDARD || $object->type == Facture::TYPE_SITUATION) && $object->statut == Facture::STATUS_VALIDATED && empty($object->paye) && $resteapayer < 0 && $usercancreate && empty($discount->id))
						{
							print '<a class="butAction'.($conf->use_javascript_ajax ? ' reposition' : '').'" href="'.$_SERVER["PHP_SELF"].'?facid='.$object->id.'&amp;action=converttoreduc">'.$langs->trans('ConvertExcessReceivedToReduc').'</a>';
						}
						// For credit note
						if ($object->type == Facture::TYPE_CREDIT_NOTE && $object->statut == Facture::STATUS_VALIDATED && $object->paye == 0 && $usercancreate
							&& (!empty($conf->global->INVOICE_ALLOW_REUSE_OF_CREDIT_WHEN_PARTIALLY_REFUNDED) || $object->getSommePaiement() == 0)
						) {
							print '<a class="butAction'.($conf->use_javascript_ajax ? ' reposition' : '').'" href="'.$_SERVER["PHP_SELF"].'?facid='.$object->id.'&amp;action=converttoreduc" title="'.dol_escape_htmltag($langs->trans("ConfirmConvertToReduc2")).'">'.$langs->trans('ConvertToReduc').'</a>';
						}
						// For deposit invoice
						if ($object->type == Facture::TYPE_DEPOSIT && $usercancreate && $object->statut > 0 && empty($discount->id))
						{
							print '<a class="butAction'.($conf->use_javascript_ajax ? ' reposition' : '').'" href="'.$_SERVER["PHP_SELF"].'?facid='.$object->id.'&amp;action=converttoreduc">'.$langs->trans('ConvertToReduc').'</a>';
						}
					}

					// Classify paid
					if (($object->statut == Facture::STATUS_VALIDATED && $object->paye == 0 && $usercanissuepayment && (($object->type != Facture::TYPE_CREDIT_NOTE && $object->type != Facture::TYPE_DEPOSIT && $resteapayer <= 0) || ($object->type == Facture::TYPE_CREDIT_NOTE && $resteapayer >= 0)))
						|| ($object->type == Facture::TYPE_DEPOSIT && $object->paye == 0 && $object->total_ttc > 0 && $resteapayer == 0 && $usercanissuepayment && empty($discount->id))
					)
					{
						print '<a class="butAction'.($conf->use_javascript_ajax ? ' reposition' : '').'" href="'.$_SERVER['PHP_SELF'].'?facid='.$object->id.'&amp;action=paid">'.$langs->trans('ClassifyPaid').'</a>';
					}

					// Classify 'closed not completely paid' (possible si validee et pas encore classee payee)

					if ($object->statut == Facture::STATUS_VALIDATED && $object->paye == 0 && $resteapayer > 0 && $usercanissuepayment)
					{
						if ($totalpaye > 0 || $totalcreditnotes > 0)
						{
							// If one payment or one credit note was linked to this invoice
							print '<a class="butAction'.($conf->use_javascript_ajax ? ' reposition' : '').'" href="'.$_SERVER['PHP_SELF'].'?facid='.$object->id.'&amp;action=paid">'.$langs->trans('ClassifyPaidPartially').'</a>';
						} else {
							if (empty($conf->global->INVOICE_CAN_NEVER_BE_CANCELED))
							{
								if ($objectidnext)
								{
									print '<span class="butActionRefused classfortooltip" title="'.$langs->trans("DisabledBecauseReplacedInvoice").'">'.$langs->trans('ClassifyCanceled').'</span>';
								} else {
									print '<a class="butAction'.($conf->use_javascript_ajax ? ' reposition' : '').'" href="'.$_SERVER['PHP_SELF'].'?facid='.$object->id.'&amp;action=canceled">'.$langs->trans('ClassifyCanceled').'</a>';
								}
							}
						}
					}

					// Create a credit note
					if (($object->type == Facture::TYPE_STANDARD || $object->type == Facture::TYPE_DEPOSIT || $object->type == Facture::TYPE_PROFORMA) && $object->statut > 0 && $usercancreate)
					{
						if (!$objectidnext)
						{
							print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?socid='.$object->socid.'&amp;fac_avoir='.$object->id.'&amp;action=create&amp;type=2'.($object->fk_project > 0 ? '&amp;projectid='.$object->fk_project : '').($object->entity > 0 ? '&amp;originentity='.$object->entity : '').'">'.$langs->trans("CreateCreditNote").'</a>';
						}
					}

					// For situation invoice with excess received
					if ($object->statut > Facture::STATUS_DRAFT
						&& $object->type == Facture::TYPE_SITUATION
						&& ($object->total_ttc - $totalpaye - $totalcreditnotes - $totaldeposits) > 0
						&& $usercancreate
						&& !$objectidnext
						&& $object->is_last_in_cycle()
						&& $conf->global->INVOICE_USE_SITUATION_CREDIT_NOTE
					)
					{
						if ($usercanunvalidate)
						{
							print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?socid='.$object->socid.'&amp;fac_avoir='.$object->id.'&amp;invoiceAvoirWithLines=1&amp;action=create&amp;type=2'.($object->fk_project > 0 ? '&amp;projectid='.$object->fk_project : '').'">'.$langs->trans("CreateCreditNote").'</a>';
						} else {
							print '<span class="butActionRefused classfortooltip" title="'.$langs->trans("NotEnoughPermissions").'">'.$langs->trans("CreateCreditNote").'</span>';
						}
					}

					// Clone
					if (($object->type == Facture::TYPE_STANDARD || $object->type == Facture::TYPE_DEPOSIT || $object->type == Facture::TYPE_PROFORMA) && $usercancreate)
					{
						print '<a class="butAction'.($conf->use_javascript_ajax ? ' reposition' : '').'" href="'.$_SERVER['PHP_SELF'].'?facid='.$object->id.'&amp;action=clone&amp;object=invoice">'.$langs->trans("ToClone").'</a>';
					}

					// Clone as predefined / Create template
					if (($object->type == Facture::TYPE_STANDARD || $object->type == Facture::TYPE_DEPOSIT || $object->type == Facture::TYPE_PROFORMA) && $object->statut == 0 && $usercancreate)
					{
						if (!$objectidnext && count($object->lines) > 0)
						{
							print '<a class="butAction" href="'.DOL_URL_ROOT.'/compta/facture/card-rec.php?facid='.$object->id.'&amp;action=create">'.$langs->trans("ChangeIntoRepeatableInvoice").'</a>';
						}
					}

					// Remove situation from cycle
					if (in_array($object->statut, array(Facture::STATUS_CLOSED, Facture::STATUS_VALIDATED))
						&& $object->type == Facture::TYPE_SITUATION
						&& $usercancreate
						&& !$objectidnext
						&& $object->situation_counter > 1
						&& $object->is_last_in_cycle()
						&& $usercanunvalidate
					)
					{
						if (($object->total_ttc - $totalcreditnotes) == 0)
						{
							print '<a id="butSituationOut" class="butAction" href="'.$_SERVER['PHP_SELF'].'?facid='.$object->id.'&amp;action=situationout">'.$langs->trans("RemoveSituationFromCycle").'</a>';
						} else {
							print '<a id="butSituationOutRefused" class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("DisabledBecauseNotEnouthCreditNote").'" >'.$langs->trans("RemoveSituationFromCycle").'</a>';
						}
					}

					// Create next situation invoice
					if ($usercancreate && ($object->type == 5) && ($object->statut == 1 || $object->statut == 2)) {
						if ($object->is_last_in_cycle() && $object->situation_final != 1) {
							print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=create&amp;type=5&amp;origin=facture&amp;originid='.$object->id.'&amp;socid='.$object->socid.'" >'.$langs->trans('CreateNextSituationInvoice').'</a>';
						} elseif (!$object->is_last_in_cycle()) {
							print '<a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("DisabledBecauseNotLastInCycle").'">'.$langs->trans('CreateNextSituationInvoice').'</a>';
						} else {
							print '<a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("DisabledBecauseFinal").'">'.$langs->trans('CreateNextSituationInvoice').'</a>';
						}
					}

					// Delete
					$isErasable = $object->is_erasable();
					if ($usercandelete || ($usercancreate && $isErasable == 1))	// isErasable = 1 means draft with temporary ref (draft can always be deleted with no need of permissions)
					{
						//var_dump($isErasable);
						if ($isErasable == -4) {
							print '<a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("DisabledBecausePayments").'">'.$langs->trans('Delete').'</a>';
						} elseif ($isErasable == -3) {
							print '<a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("DisabledBecauseNotLastSituationInvoice").'">'.$langs->trans('Delete').'</a>';
						} elseif ($isErasable == -2) {
							print '<a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("DisabledBecauseNotLastInvoice").'">'.$langs->trans('Delete').'</a>';
						} elseif ($isErasable == -1) {
							print '<a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("DisabledBecauseDispatchedInBookkeeping").'">'.$langs->trans('Delete').'</a>';
						} elseif ($isErasable <= 0)	// Any other cases
						{
							print '<a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("DisabledBecauseNotErasable").'">'.$langs->trans('Delete').'</a>';
						} elseif ($objectidnext)
						{
							print '<a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("DisabledBecauseReplacedInvoice").'">'.$langs->trans('Delete').'</a>';
						} else {
							print '<a class="butActionDelete'.($conf->use_javascript_ajax ? ' reposition' : '').'" href="'.$_SERVER["PHP_SELF"].'?facid='.$object->id.'&amp;action=delete&amp;token='.newToken().'">'.$langs->trans('Delete').'</a>';
						}
					} else {
						print '<a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("NotAllowed").'">'.$langs->trans('Delete').'</a>';
					}





				/*
				 * botones
				 * */
				return true;
			}



		}


		if (in_array('invoicesuppliercard', $TContext))
		{

			if ($action == 'Modifystock')
			{
				//print_r($object->lines);
				$prods=$object->lines;
				$societe = new Fournisseur($db);
				// Advanced permissions
				$usercanvalidate = ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && !empty($usercancreate)) || (!empty($conf->global->MAIN_USE_ADVANCED_PERMS) && !empty($user->rights->fournisseur->supplier_invoice_advance->validate)));
				$usercansend = (empty($conf->global->MAIN_USE_ADVANCED_PERMS) || $user->rights->fournisseur->supplier_invoice_advance->send);
				
				
				// Common permissions
				$usercanread = $user->rights->fournisseur->facture->lire;
				$usercancreate		= $user->rights->fournisseur->facture->creer;
				$usercandelete		= $user->rights->fournisseur->facture->supprimer;
				
				
				$form = new Form($db);
				$formfile = new FormFile($db);
				$result = $societe->fetch($object->socid);



				foreach($prods as $prod):
					$sql_prod_line = "Select  lps.reel from ".MAIN_DB_PREFIX."product_stock AS lps WHERE fk_product= ".$prod->fk_product;
					$resql12 = $db->query($sql_prod_line);
					if ($resql12) {
						$i = 0;
						$num = $db->num_rows($resql12);
						while ($i < $num) {
							$objp = $db->fetch_object($resql12);
							$prod_id= $prod->fk_product;
							$stock1 = $prod->qty;
							$nuevostock = $objp->reel - $stock1;
							$sql_update= " update ".MAIN_DB_PREFIX."product_stock set reel= ".$nuevostock." where fk_product =".$prod->fk_product;
							$resql11 = $db->query($sql_update);
							$sql_ref = "SELECT  lp.ref FROM ".MAIN_DB_PREFIX."product lp  where rowid = ".$prod_id;
							$redprod = $db->query($sql_ref);
							$num1 = $db->num_rows($redprod);
							$refObjet = $db->fetch_object($redprod);
							foreach($refObjet as $valorref):

							endforeach;
							if ($resql11)
							{
								setEventMessages('Ref: '.$valorref.' : Updated Stock', '', 'mesgs');
								//actualizamos return_cash para desabilitar la devolucion doble
								$sql_update= "UPDATE ".MAIN_DB_PREFIX."facture_fourn_extrafields  set return_stock = 1 where fk_object =".$object->id;
								$resql11 = $db->query($sql_update);
								if ($resql11) {
									$sql_prod_line = "select fk_object from ".MAIN_DB_PREFIX."facture_fourn_extrafields lffe  where fk_object = ".$object->id;
									$resql122 = $db->query($sql_prod_line);
									// $return1 = $db->fetch_object($resql122);
									foreach($resql122 as $retu)
									{
										foreach ($retu as $b)
										{

										}
									}
									if($b <> $object->id)
									{
										$sql_update= "INSERT INTO ".MAIN_DB_PREFIX."facture_fourn_extrafields  ( fk_object, import_key, return_stock) VALUES ( ".$object->id.", null, 1)";
										$resql11 = $db->query($sql_update);
										$action = 'Modifystock';
										foreach($resql11 as $retua)
										{

										}

									}

									$sql_element = "select fk_element, elementtype from ".MAIN_DB_PREFIX."actioncomm where label like '%".$object->ref."%'";
									$Elements = $db->query($sql_element);
									if ($Elements) {
																			
										foreach ($Elements as $e)
										{


										}
										
									}	
								$a1=$e['fk_element'];

								$a2 =$e['elementtype'];


								
								$sql_entrepot = " SELECT fk_entrepot from ".MAIN_DB_PREFIX."product_stock  where fk_product = ".$prod->fk_product;
								//" SELECT fk_entrepot llx_product_stock  where fk_product = 4646"
								$res_entrepot = $db->query($sql_entrepot);
								if ($res_entrepot) {
																		
									foreach ($res_entrepot as $almacenes)
									{


									}
									
								}	


								$almacen = $almacenes['fk_entrepot'];
								$fact = $prod->fk_facture_fourn;

								
								$sql_stock_moment = "INSERT INTO ".MAIN_DB_PREFIX."stock_mouvement (rowid, tms, datem, fk_product, fk_entrepot, price, type_mouvement, fk_user_author, label, fk_origin, origintype, fk_projet)"; 
								$sql_stock_moment .= "VALUES (null,  curdate(), curdate(), ".$prod_id.", ".$almacen.",  0.00, 0,  ".$object->fk_user_author.",  'invoice_supplier ".$object->ref." changed to stock',  ".$fact.", 'invoice_supplier', 0);";
								$res_stock_moment = $db->query($sql_stock_moment);
//"INSERT INTO llx_stock_mouvement (rowid, tms, datem, fk_product, fk_entrepot, price, type_mouvement, fk_user_author, label, fk_origin, origintype, fk_projet)
//VALUES (null,  curdate(), curdate(), 94, 1,  0.00, 0,  29,  'Invoice SA2110-0037 changed to stock',  1054, 'facture', 0);"


								//action code 50 = AC_OTH -> Other (manually inserted events)
								$sql_insert_event = "INSERT INTO ".MAIN_DB_PREFIX."actioncomm (id, ref_ext, entity, datep, datep2, fk_action, code, datec, tms, fk_user_mod, fk_soc, ";
								$sql_insert_event .= "fk_parent, fk_user_action, percent, label, fk_element, elementtype) VALUES (null, null, 1, curdate(), curdate(), ";
								$sql_insert_event .= "50, 'PRODUCT_RETURN_STOCK ', curdate(), curdate(), ".$object->fk_user_author.", ".$object->socid.", 0, ".$object->fk_user_author.", -1, ";
								$sql_insert_event .= "'".$a2." ".$object->ref." changed to stock Ref: ".$valorref." : ".$stock1."', ".$object->id.", 'invoice_supplier')";
								$res = $db->query($sql_insert_event);


								

								}
							}
							$i++;
						}$sql_insert_event = "INSERT INTO ".MAIN_DB_PREFIX."actioncomm (id, ref_ext, entity, datep, datep2, fk_action, code, datec, tms, fk_user_mod, fk_soc, ";
								$sql_insert_event .= "fk_parent, fk_user_action, percent, label, fk_element, elementtype) VALUES (null, null, 1, curdate(), curdate(), ";
								$sql_insert_event .= "50, 'PRODUCT_RETURN_STOCK ', curdate(), curdate(), ".$object->fk_user_author.", ".$object->socid.", 0, ".$object->fk_user_author.", -1, ";
								$sql_insert_event .= "'invoice_supplier ".$object->ref." changed to stock product  Ref: ".$valorref." : ".$stock1."', ".$prod->fk_product.", 'product')";
								$res = $db->query($sql_insert_event);
					}


				endforeach;
				$action = 'Modifystock';

			}
			$action;

			$active = $conf->global->RETURN_STOCK_IN_CREDIT_NOTE_VENDOR;
			if($active == 1 )
			{   //if ($object->statut == Facture::STATUS_CLOSED || ($object->statut == Facture::STATUS_ABANDONED && ($object->close_code != 'replaced' || $object->getIdReplacingInvoice() == 0)) || ($object->statut == Facture::STATUS_VALIDATED && $object->paye == 1)) {    // ($object->statut == 1 && $object->paye == 1) should not happened but can be found when data are corrupted
				//if ($object->type == FactureFournisseur::TYPE_CREDIT_NOTE || $object->type == FactureFournisseur::TYPE_DEPOSIT || $object->type == FactureFournisseur::TYPE_STANDARD)
					// if ($object->type == FactureFournisseur::TYPE_CREDIT_NOTE && $object->statut == FactureFournisseur::STATUS_VALIDATED && $object->paye == 0 && $usercanissuepayment)
				if ($object->type == FactureFournisseur::TYPE_CREDIT_NOTE || $object->type == FactureFournisseur::TYPE_DEPOSIT || $object->type == FactureFournisseur::TYPE_STANDARD)
				{

					$sql_prod_line = "select return_stock from ".MAIN_DB_PREFIX."facture_fourn_extrafields lffe  where fk_object = ".$object->id;
					$resql122 = $db->query($sql_prod_line);
					// $return1 = $db->fetch_object($resql122);

					foreach($resql122 as $retu)
					{
						foreach ($retu as $a)
						{

						}


					}

					if ($object->type == FactureFournisseur::TYPE_CREDIT_NOTE && $object->statut == 1 && $object->paye == 0)
					{
						if ($a == null)
						{

								print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?facid='.$object->id.'&action=Modifystock&mode=init#formmailbeforetitle">'.$langs->trans('Return Stock').'</a>';
								//print '<a class="butAction'.($conf->use_javascript_ajax ? ' reposition' : '').'" href="'.$_SERVER["PHP_SELF"].'?facid='.$object->id.'&amp;action=draftstock#draftstock">'.$langs->trans(' Draft stock').'</a>';
								/*
								 * boton para almacenes
								 * */
						} else {
							$totalpaye = $object->getSommePaiement();
					$resteapayer = $object->total_ttc - $totalpaye;
							// print '<a class="butAction" href="'.DOL_URL_ROOT.'/compta/paiement.php?facid='.$object->id.'&amp;action=create&amp;accountid='.$object->fk_account.'">'.$langs->trans('DoPaymentBack').'</a>';
							print '<a class="butActionRefused classfortooltip" href="'.$_SERVER['PHP_SELF'].'?facid='.$object->id.'&action=Modifystock&mode=init#formmailbeforetitle">'.$langs->trans('Return Stock').'</a>';

							$action = 'Modifystock';
						}
						/*
						 * AGREGAR BOTON ENTER REFUND
						 * */


							// Modify a validated invoice with no payments
							if ($object->statut == FactureFournisseur::STATUS_VALIDATED && $action != 'confirm_edit' && $object->getSommePaiement() == 0 && $usercancreate)
							{
								// We check if lines of invoice are not already transfered into accountancy
								$ventilExportCompta = $object->getVentilExportCompta(); // Should be 0 since the sum of payments are zero. But we keep the protection.

								if ($ventilExportCompta == 0)
								{
									print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&amp;action=edit">'.$langs->trans('Modify').'</a></div>';
								} else {
									print '<div class="inline-block divButAction"><span class="butActionRefused classfortooltip" title="'.$langs->trans("DisabledBecauseDispatchedInBookkeeping").'">'.$langs->trans('Modify').'</span></div>';
								}
							}

							$discount = new DiscountAbsolute($db);
							$result = $discount->fetch(0, 0, $object->id);

							// Reopen a standard paid invoice
							if (($object->type == FactureFournisseur::TYPE_STANDARD || $object->type == FactureFournisseur::TYPE_REPLACEMENT
									|| ($object->type == FactureFournisseur::TYPE_CREDIT_NOTE && empty($discount->id)))
								&& ($object->statut == FactureFournisseur::STATUS_CLOSED || $object->statut == FactureFournisseur::STATUS_ABANDONED))				// A paid invoice (partially or completely)
							{
								if (!$facidnext && $object->close_code != 'replaced' && $usercancreate)	// Not replaced by another invoice
								{
									print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&amp;action=reopen">'.$langs->trans('ReOpen').'</a></div>';
								} else {
									if ($usercancreate) {
										print '<div class="inline-block divButAction"><span class="butActionRefused classfortooltip" title="'.$langs->trans("DisabledBecauseReplacedInvoice").'">'.$langs->trans('ReOpen').'</span></div>';
									} elseif (empty($conf->global->MAIN_BUTTON_HIDE_UNAUTHORIZED)) {
										print '<div class="inline-block divButAction"><span class="butActionRefused classfortooltip">'.$langs->trans('ReOpen').'</span></div>';
									}
								}
							}

							// Send by mail
							if (empty($user->socid)) {
								if (($object->statut == FactureFournisseur::STATUS_VALIDATED || $object->statut == FactureFournisseur::STATUS_CLOSED))
								{	$usercansend = (empty($conf->global->MAIN_USE_ADVANCED_PERMS) || $user->rights->facture->invoice_advance->send);
									if ($usercansend)
									{
										print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=presend&mode=init#formmailbeforetitle">'.$langs->trans('SendMail').'</a></div>';
									} else print '<div class="inline-block divButAction"><span class="butActionRefused classfortooltip">'.$langs->trans('SendMail').'</a></div>';
								}
							}

							// Make payments
							if ($object->type != FactureFournisseur::TYPE_CREDIT_NOTE && $action != 'confirm_edit' && $object->statut == FactureFournisseur::STATUS_VALIDATED && $object->paye == 0 && $user->socid == 0)
							{
								print '<div class="inline-block divButAction"><a class="butAction" href="paiement.php?facid='.$object->id.'&amp;action=create'.($object->fk_account > 0 ? '&amp;accountid='.$object->fk_account : '').'">'.$langs->trans('DoPayment').'</a></div>'; // must use facid because id is for payment id not invoice
							}

							// Classify paid
							if ($action != 'confirm_edit' && $object->statut == FactureFournisseur::STATUS_VALIDATED && $object->paye == 0 && $user->socid == 0)
							{
								print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=paid"';
								print '>'.$langs->trans('ClassifyPaid').'</a></div>';

								//print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=paid">'.$langs->trans('ClassifyPaid').'</a>';
							}

							// Reverse back money or convert to reduction
							if ($object->type == FactureFournisseur::TYPE_CREDIT_NOTE || $object->type == FactureFournisseur::TYPE_DEPOSIT || $object->type == FactureFournisseur::TYPE_STANDARD)
							{
								// For credit note only
								if ($object->type == FactureFournisseur::TYPE_CREDIT_NOTE && $object->statut == 1 && $object->paye == 0)
								{
									if ($resteapayer == 0)
									{
										print '<div class="inline-block divButAction"><span class="butActionRefused classfortooltip" title="'.$langs->trans("DisabledBecauseRemainderToPayIsZero").'">'.$langs->trans('DoPaymentBack').'</span></div>';
									} else {
										print '<div class="inline-block divButAction"><a class="butAction" href="paiement.php?facid='.$object->id.'&amp;action=create&amp;accountid='.$object->fk_account.'">'.$langs->trans('DoPaymentBack').'</a></div>';
									}
								}

								// For standard invoice with excess paid
								if ($object->type == FactureFournisseur::TYPE_STANDARD && empty($object->paye) && ($object->total_ttc - $totalpaye - $totalcreditnotes - $totaldeposits) < 0 && $usercancreate && empty($discount->id))
								{
									print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?facid='.$object->id.'&amp;action=converttoreduc">'.$langs->trans('ConvertExcessPaidToReduc').'</a></div>';
								}
								// For credit note
								if ($object->type == FactureFournisseur::TYPE_CREDIT_NOTE && $object->statut == 1 && $object->paye == 0 && $usercancreate
									&& (!empty($conf->global->SUPPLIER_INVOICE_ALLOW_REUSE_OF_CREDIT_WHEN_PARTIALLY_REFUNDED) || $object->getSommePaiement() == 0)
								) {
									print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?facid='.$object->id.'&amp;action=converttoreduc" title="'.dol_escape_htmltag($langs->trans("ConfirmConvertToReducSupplier2")).'">'.$langs->trans('ConvertToReduc').'</a></div>';
								}
								// For deposit invoice
								if ($object->type == FactureFournisseur::TYPE_DEPOSIT && $object->paye == 1 && $resteapayer == 0 && $usercancreate && empty($discount->id))
								{
									print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?facid='.$object->id.'&amp;action=converttoreduc">'.$langs->trans('ConvertToReduc').'</a></div>';
								}
							}

							// Validate
							if ($action != 'confirm_edit' && $object->statut == FactureFournisseur::STATUS_DRAFT)
							{
								if (count($object->lines))
								{
									if ($usercanvalidate)
									{
										print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=valid"';
										print '>'.$langs->trans('Validate').'</a></div>';
									} else {
										print '<div class="inline-block divButAction"><a class="butActionRefused classfortooltip" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'"';
										print '>'.$langs->trans('Validate').'</a></div>';
									}
								}
							}

							// Create event
							/*if ($conf->agenda->enabled && ! empty($conf->global->MAIN_ADD_EVENT_ON_ELEMENT_CARD)) 	// Add hidden condition because this is not a "workflow" action so should appears somewhere else on page.
							{
								print '<div class="inline-block divButAction"><a class="butAction" href="' . DOL_URL_ROOT . '/comm/action/card.php?action=create&amp;origin=' . $object->element . '&amp;originid=' . $object->id . '&amp;socid=' . $object->socid . '">' . $langs->trans("AddAction") . '</a></div>';
							}*/

							// Clone
							if ($action != 'edit' && $usercancreate)
							{
								print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=clone&amp;socid='.$object->socid.'">'.$langs->trans('ToClone').'</a></div>';
							}

							// Create a credit note
							if (($object->type == FactureFournisseur::TYPE_STANDARD || $object->type == FactureFournisseur::TYPE_DEPOSIT) && $object->statut > 0 && $usercancreate)
							{
								if (!$objectidnext)
								{
									print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER['PHP_SELF'].'?socid='.$object->socid.'&amp;fac_avoir='.$object->id.'&amp;action=create&amp;type=2'.($object->fk_project > 0 ? '&amp;projectid='.$object->fk_project : '').'">'.$langs->trans("CreateCreditNote").'</a></div>';
								}
							}

							// Delete
							$isErasable = $object->is_erasable();
							if ($action != 'confirm_edit' && ($user->rights->fournisseur->facture->supprimer || ($usercancreate && $isErasable == 1)))	// isErasable = 1 means draft with temporary ref (draft can always be deleted with no need of permissions)
							{
								//var_dump($isErasable);
								if ($isErasable == -4) {
									print '<div class="inline-block divButAction"><a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("DisabledBecausePayments").'">'.$langs->trans('Delete').'</a></div>';
								} elseif ($isErasable == -3) {	// Should never happen with supplier invoice
									print '<div class="inline-block divButAction"><a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("DisabledBecauseNotLastSituationInvoice").'">'.$langs->trans('Delete').'</a></div>';
								} elseif ($isErasable == -2) {	// Should never happen with supplier invoice
									print '<div class="inline-block divButAction"><a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("DisabledBecauseNotLastInvoice").'">'.$langs->trans('Delete').'</a></div>';
								} elseif ($isErasable == -1) {
									print '<div class="inline-block divButAction"><a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("DisabledBecauseDispatchedInBookkeeping").'">'.$langs->trans('Delete').'</a></div>';
								} elseif ($isErasable <= 0)	// Any other cases
								{
									print '<div class="inline-block divButAction"><a class="butActionRefused classfortooltip" href="#" title="'.$langs->trans("DisabledBecauseNotErasable").'">'.$langs->trans('Delete').'</a></div>';
								} else {
									print '<div class="inline-block divButAction"><a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=delete&amp;token='.newToken().'">'.$langs->trans('Delete').'</a></div>';
								}
							}
							print '</div>';

							if ($action != 'confirm_edit'  )//&& $action != 'Modifystock'
							{
								print '<div class="fichecenter"><div class="fichehalfleft">';

					/*
	                 * Documents generes
	                 */
					global $conf;
					$ref = dol_sanitizeFileName($object->ref);
					$subdir = get_exdir($object->id, 2, 0, 0, $object, 'invoice_supplier').$ref;
					$filedir = $conf->fournisseur->facture->dir_output.'/'.$subdir;
					$urlsource = $_SERVER['PHP_SELF'].'?id='.$object->id;
					$genallowed = $usercanread;
					$delallowed = $usercancreate;
					$modelpdf = (!empty($object->model_pdf) ? $object->model_pdf : (empty($conf->global->INVOICE_SUPPLIER_ADDON_PDF) ? '' : $conf->global->INVOICE_SUPPLIER_ADDON_PDF));

//					print $formfile->showdocuments('facture_fournisseur', $subdir, $filedir, $urlsource, $genallowed, $delallowed, $modelpdf, 1, 0, 0, 40, 0, '', '', '', $societe->default_lang);
//					$somethingshown = $formfile->numoffiles;

					// Show links to link elements
					
//					$linktoelem = $form->showLinkToObjectBlock($object, null, array('invoice_supplier'));
//					$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);

					print '</div><div class="fichehalfright"><div class="ficheaddleft">';
					//print '</td><td valign="top" width="50%">';
					//print '<br>';

					// List of actions on element
					include_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
					$formactions = new FormActions($db);
					$somethingshown = $formactions->showactions($object, 'invoice_supplier', $socid, 1, 'listaction'.($genallowed ? 'largetitle' : ''));

					print '</div></div></div>';
					//print '</td></tr></table>';
							}




						/*
						 * FIN AGREGAR BOTON ENTER REFUND
						 * */




						return true;

					}
					$b= $object->statut ;
				}
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





	/* Add here any other hooked methods... */
}
