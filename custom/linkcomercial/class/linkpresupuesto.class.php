<?php
dol_include_once('./linkcomercial/class/pedidoproveedor.class.php');
require_once  DOL_DOCUMENT_ROOT.'/custom/linkcomercial/class/PropagateContacts.class.php';
/**
 * Created by PhpStorm.
 * User: alberto
 * Date: 03/11/2019
 * Time: 03:17 PM
 */
class linkpresupuesto
{
	public static $fk_socproduct = 0;
    /**
     * linkpresupuesto presupuestos.
     */
    public function convierte_presupuesto_cliente($object, $origin, $thirdid, $tipodoc, $iddoc)
    {
        global $db, $user;
        $presucliente = new Propal($db);
        $presucliente->socid = $thirdid;
        $presucliente->date = $object->date_creation;
        $presucliente->origin = $origin;
        $presucliente->origin_id = $iddoc;
        $presucliente->user_author_id = $object->user_author_id;
        $presucliente->date_livraison = $object->date_livraison;
        $presucliente->note_private = $object->note_private;
        $presucliente->note_public = $object->note_public;
        $presucliente->cond_reglement_id = $object->cond_reglement_id;
        $presucliente->cond_reglement_code = $object->cond_reglement_code;
        $presucliente->mode_reglement_code = $object->mode_reglement_code;
        $presucliente->mode_reglement_id = $object->mode_reglement_id;
        $presucliente->fk_project=$object->fk_project;

        if (count($object->line) > 0) {

        }
        //$i=0;
        foreach ($object->lines as $line) {
            $presupuestlolinea = new PropaleLigne($db);
            $presupuestlolinea->qty = $line->qty;
            $presupuestlolinea->product_ref = $line->product_ref;
            $presupuestlolinea->product_desc = $line->product_desc;
            $presupuestlolinea->product_label = $line->product_label;
            $presupuestlolinea->price = $line->price;
            $presupuestlolinea->ref = $line->ref;
            $presupuestlolinea->fk_product = $line->fk_product;
            $presupuestlolinea->fk_multicurrency = $line->fk_multicurrency;
            $presupuestlolinea->id = $line->id;
            $presupuestlolinea->libelle = $line->libelle;
            $presupuestlolinea->localtax1_tx = $line->localtax1_tx;
            $presupuestlolinea->marque_tx = $line->marge_tx;
            $presupuestlolinea->multicurrency_code = $line->multicurrency_code;
            $presupuestlolinea->multicurrency_subprice = $line->multicurrency_subprice;
            $presupuestlolinea->multicurrency_total_ht = $line->multicurrency_total_ht;
            $presupuestlolinea->multicurrency_total_ttc = $line->multicurrency_total_ttc;
            $presupuestlolinea->multicurrency_total_tva = $line->multicurrency_total_tva;
            $presupuestlolinea->rang = $line->rang;
            $presupuestlolinea->subprice = $line->subprice;
            $presupuestlolinea->total_ht = $line->total_ht;
            $presupuestlolinea->total_localtax1 = $line->total_localtax1;
            $presupuestlolinea->total_localtax2 = $line->total_localtax2;
            $presupuestlolinea->total_ttc = $line->total_ttc;
            $presupuestlolinea->total_tva = $line->total_tva;
            $presupuestlolinea->tva_tx = $line->tva_tx;
            $presucliente->lines[] = $presupuestlolinea;
            //$i++;
        }
        $preid=$presucliente->create($user);
        if ($preid) PropagateContacts::propagate($object,$presucliente);
        return $preid;


    }

    public function convierte_pedido_proveedor($object, $origin, $thirdid, $tipodoc, $iddoc)
    {

        global $db, $user, $langs, $conf;
		$dicss = $conf->global->VALID_STOCK_DROPSHIPPING1;
        $presucliente = new pedidoproveedor($db);
        //TODO Alberto buscar un socid o tercero que tenga esos productos en proveedores
        $presucliente->socid = $object->socid;
        //$presucliente->socid = 2;
        $presucliente->date = $object->date_creation;
        $presucliente->linked_objects=array("commande"=>$iddoc);
        $presucliente->origin = $origin;
        $presucliente->origin_id = $iddoc;
        $presucliente->user_author_id = $object->user_author_id;
        $presucliente->source = $object->source;
		if($conf->global->VALID_STOCK_DROPSHIPPING1 ==  '1') {
			$presucliente->remise_percent = $object->remise_percent;
		}

		$presucliente->date_livraison = $object->date_livraison;
        $presucliente->note_private = $object->note_private;
        $presucliente->note_public = $object->note_public;
        $presucliente->cond_reglement_id = $object->cond_reglement_id;
        $presucliente->cond_reglement_code = $object->cond_reglement_code;
        $presucliente->mode_reglement_code = $object->mode_reglement_code;
        $presucliente->mode_reglement_id = $object->mode_reglement_id;
        $presucliente->source=$object->id;

        //$i=0;
        foreach ($object->lines as $line) {
			$productsupplier = new ProductFournisseur($db);
			$res = $productsupplier->fetch($line->fk_product);
			$prod = new Product($db);
			$prod->get_buyprice(0, $line->qty, $line->fk_product, 'none', 0);

            $presupuestlolinea = new CommandeFournisseurLigne($db);
            $presupuestlolinea->qty = $line->qty;
            $presupuestlolinea->product_ref = $line->product_ref;
            $presupuestlolinea->product_desc = $line->product_desc;
			if($conf->global->VALID_STOCK_DROPSHIPPING1 == '1') {
				$presupuestlolinea->remise_percent = $line->remise_percent;
			}

            $presupuestlolinea->product_label = $line->product_label;
            $presupuestlolinea->price = $productsupplier->cost_price;
            $presupuestlolinea->ref = $line->ref;
            $presupuestlolinea->fk_product = $line->fk_product;
            $presupuestlolinea->fk_multicurrency = $line->fk_multicurrency;
            $presupuestlolinea->id = $line->id;
            $presupuestlolinea->desc=$line->desc;
            $presupuestlolinea->libelle = $line->libelle;
            $presupuestlolinea->localtax1_tx = $line->localtax1_tx;
            $presupuestlolinea->marque_tx = $line->marge_tx;
            $presupuestlolinea->multicurrency_code = $line->multicurrency_code;
            $presupuestlolinea->multicurrency_subprice =$productsupplier->cost_price;
            $presupuestlolinea->multicurrency_total_ht = $line->multicurrency_total_ht;
            $presupuestlolinea->multicurrency_total_ttc = $line->multicurrency_total_ttc;
            $presupuestlolinea->multicurrency_total_tva = $line->multicurrency_total_tva;
            $presupuestlolinea->rang = $line->rang;
            $presupuestlolinea->subprice = $productsupplier->cost_price;;
            $presupuestlolinea->total_ht = $line->total_ht;
            $presupuestlolinea->total_localtax1 = $line->total_localtax1;
            $presupuestlolinea->total_localtax2 = $line->total_localtax2;
            $presupuestlolinea->total_ttc = $line->total_ttc;
            $presupuestlolinea->total_tva = $line->total_tva;
            $presupuestlolinea->tva_tx = $line->tva_tx;
            $presupuestlolinea->ref_supplier=$prod->ref_supplier;
            $presucliente->lines[] = $presupuestlolinea;
            //$i++;
        }
       //return $presucliente->updateFromCommandeClient($user,$iddoc,$thirdid);
		if (! $error)
		{
			$object->array_options["options_dropship"] = 1;
			$object->insertExtraFields();
			$result=$presucliente->insertExtraFields();
			if ($result < 0) $error++;
		}
        $mivar= $presucliente->create($user);
        $presucliente->update_price();
		if ($mivar) PropagateContacts::propagate($object,$presucliente);
        return $mivar;

    }

	public static function elimina_dropship_pedido($id)
	{
		global $db;
		$db->query("UPDATE ".MAIN_DB_PREFIX."commande_extrafields set dropship=0 WHERE fk_object=".$id);
		//TODO Razmi delete from element
		$target=$_GET["id"];
		$db->query("UPDATE ".MAIN_DB_PREFIX."commande_extrafields set dropship=0 WHERE fk_object=(SELECT fk_source FROM 
		          ".MAIN_DB_PREFIX."element_element where targettype='order_supplier' and sourcetype='commande' and fk_target=$target)");
        if($_POST["toselect"]){
			$target=$_POST["toselect"][0];
			$db->query("UPDATE ".MAIN_DB_PREFIX."commande_extrafields set dropship=0 WHERE fk_object=(SELECT fk_source FROM 
		          ".MAIN_DB_PREFIX."element_element where targettype='order_supplier' and sourcetype='commande' and fk_target=$target)");
		}

	}
	public static function select_producto_fourn_price($productid, $htmlname = 'productfournpriceid', $selected_supplier = '')
	{
		// phpcs:enable
		global $langs, $conf,$db;

		$langs->load('stocks');

		$sql = "SELECT p.rowid, p.ref, p.label, p.price, p.duration, pfp.fk_soc,";
		$sql .= " pfp.ref_fourn, pfp.rowid as idprodfournprice, pfp.price as fprice, pfp.remise_percent, pfp.quantity, pfp.unitprice,";
		$sql .= " pfp.fk_supplier_price_expression, pfp.fk_product, pfp.tva_tx, s.nom as name";
		$sql .= " FROM ".MAIN_DB_PREFIX."product as p";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_fournisseur_price as pfp ON p.rowid = pfp.fk_product";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON pfp.fk_soc = s.rowid";
		$sql .= " WHERE pfp.entity IN (".getEntity('productsupplierprice').")";
		$sql .= " AND p.tobuy = 1";
		$sql .= " AND s.fournisseur = 1";
		$sql .= " AND p.rowid = ".$productid;
		$sql .= " ORDER BY s.nom, pfp.ref_fourn DESC";

		//dol_syslog(get_class($this)."::select_product_fourn_price", LOG_DEBUG);
		$result = $db->query($sql);

		if ($result)
		{
			$num = $db->num_rows($result);

			$form = '<select class="flat" id="select_'.$htmlname.'" name="'.$htmlname.'">';

			if (!$num)
			{
				$form .= '<option value="0">-- '.$langs->trans("NoSupplierPriceDefinedForThisProduct").' --</option>';
			} else {
				require_once DOL_DOCUMENT_ROOT.'/product/dynamic_price/class/price_parser.class.php';
//				$form .= '<option value="0">&nbsp;</option>';

				$i = 0;
				while ($i < $num)
				{
					$objp = $db->fetch_object($result);

					$opt = '<option value="'.$objp->fk_soc.'"';
					//if there is only one supplier, preselect it
					if ($num == 1 || ($selected_supplier > 0 && $objp->fk_soc == $selected_supplier)) {
						$opt .= ' selected';
					}
					$opt .= '>'.$objp->name.' - '.$objp->ref_fourn.' - ';

					if (!empty($conf->dynamicprices->enabled) && !empty($objp->fk_supplier_price_expression)) {
						$prod_supplier = new ProductFournisseur($db);
						$prod_supplier->product_fourn_price_id = $objp->idprodfournprice;
						$prod_supplier->id = $productid;
						$prod_supplier->fourn_qty = $objp->quantity;
						$prod_supplier->fourn_tva_tx = $objp->tva_tx;
						$prod_supplier->fk_supplier_price_expression = $objp->fk_supplier_price_expression;
						$priceparser = new PriceParser($db);
						$price_result = $priceparser->parseProductSupplier($prod_supplier);
						if ($price_result >= 0) {
							$objp->fprice = $price_result;
							if ($objp->quantity >= 1)
							{
								$objp->unitprice = $objp->fprice / $objp->quantity;
							}
						}
					}
					if ($objp->quantity == 1)
					{
						$opt .= price($objp->fprice * (!empty($conf->global->DISPLAY_DISCOUNTED_SUPPLIER_PRICE) ? (1 - $objp->remise_percent / 100) : 1), 1, $langs, 0, 0, -1, $conf->currency)."/";
					}

					$opt .= $objp->quantity.' ';

					if ($objp->quantity == 1)
					{
						$opt .= $langs->trans("Unit");
					} else {
						$opt .= $langs->trans("Units");
					}
					if ($objp->quantity > 1)
					{
						$opt .= " - ";
						$opt .= price($objp->unitprice * (!empty($conf->global->DISPLAY_DISCOUNTED_SUPPLIER_PRICE) ? (1 - $objp->remise_percent / 100) : 1), 1, $langs, 0, 0, -1, $conf->currency)."/".$langs->trans("Unit");
					}
					if ($objp->duration) $opt .= " - ".$objp->duration;
					$opt .= "</option>\n";

					$form .= $opt;
					$i++;
				}
			}
			self::$fk_socproduct=$objp->fk_soc;
			$form .= '</select>';
			$db->free($result);

			return $form;
		} else {
			dol_print_error($db);
		}
	}



}
