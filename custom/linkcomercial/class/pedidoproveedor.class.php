<?php
require_once  DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.commande.class.php';
require_once  DOL_DOCUMENT_ROOT.'/custom/linkcomercial/class/PropagateContacts.class.php';
/**
 * Created by PhpStorm.
 * User: razmin
 * Date: 06/11/2019
 * Time: 09:50 AM
 */
class pedidoproveedor extends CommandeFournisseur
{
    /**
     *  Create order with draft status
     *
     *  @param      User	$user       User making creation
     *	@param		int		$notrigger	Disable all triggers
     *  @return     int         		<0 if KO, Id of supplier order if OK
     */
    public function create($user, $notrigger=0)
    {
        global $langs,$conf,$hookmanager;

        $this->db->begin();

        $error=0;
        $now=dol_now();

        // Clean parameters
        if (empty($this->source)) $this->source = 0;

        // Multicurrency (test on $this->multicurrency_tx because we should take the default rate only if not using origin rate)
        if (!empty($this->multicurrency_code) && empty($this->multicurrency_tx)) list($this->fk_multicurrency,$this->multicurrency_tx) = MultiCurrency::getIdAndTxFromCode($this->db, $this->multicurrency_code);
        else $this->fk_multicurrency = MultiCurrency::getIdFromCode($this->db, $this->multicurrency_code);
        if (empty($this->fk_multicurrency))
        {
            $this->multicurrency_code = $conf->currency;
            $this->fk_multicurrency = 0;
            $this->multicurrency_tx = 1;
        }

        // We set order into draft status
        $this->brouillon = 1;

        $sql = "INSERT INTO ".MAIN_DB_PREFIX."commande_fournisseur (";
        $sql.= "ref";
        $sql.= ", ref_supplier";
        $sql.= ", note_private";
        $sql.= ", note_public";
        $sql.= ", entity";
        $sql.= ", fk_soc";
        $sql.= ", fk_projet";
        $sql.= ", date_creation";
        $sql.= ", date_livraison";
        $sql.= ", fk_user_author";
        $sql.= ", fk_statut";
        $sql.= ", source";
        $sql.= ", model_pdf";
        $sql.= ", fk_mode_reglement";
        $sql.= ", fk_cond_reglement";
        $sql.= ", fk_account";
        $sql.= ", fk_incoterms, location_incoterms";
        $sql.= ", fk_multicurrency";
        $sql.= ", multicurrency_code";
        $sql.= ", multicurrency_tx";
        $sql.= ") ";
        $sql.= " VALUES (";
        $sql.= "''";
        $sql.= ", '".$this->db->escape($this->ref_supplier)."'";
        $sql.= ", '".$this->db->escape($this->note_private)."'";
        $sql.= ", '".$this->db->escape($this->note_public)."'";
        $sql.= ", ".$conf->entity;
        $sql.= ", ".$this->socid;
        $sql.= ", ".($this->fk_project > 0 ? $this->fk_project : "null");
        $sql.= ", '".$this->db->idate($now)."'";
        $sql.= ", ".($this->date_livraison?"'".$this->db->idate($this->date_livraison)."'":"null");
        $sql.= ", ".$user->id;
        $sql.= ", 0";
        $sql.= ", ".$this->db->escape($this->source);
        $sql.= ", '".$conf->global->COMMANDE_SUPPLIER_ADDON_PDF."'";
        $sql.= ", ".($this->mode_reglement_id > 0 ? $this->mode_reglement_id : 'null');
        $sql.= ", ".($this->cond_reglement_id > 0 ? $this->cond_reglement_id : 'null');
        $sql.= ", ".($this->fk_account>0?$this->fk_account:'NULL');
        $sql.= ", ".(int) $this->fk_incoterms;
        $sql.= ", '".$this->db->escape($this->location_incoterms)."'";
        $sql.= ", ".(int) $this->fk_multicurrency;
        $sql.= ", '".$this->db->escape($this->multicurrency_code)."'";
        $sql.= ", ".(double) $this->multicurrency_tx;
        $sql.= ")";

        dol_syslog(get_class($this)."::create", LOG_DEBUG);
        if ($this->db->query($sql))
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."commande_fournisseur");

            if ($this->id) {
                $num=count($this->lines);

                // insert products details into database
                for ($i=0;$i<$num;$i++)
                {
                    $result = $this->addline(              // This include test on qty if option SUPPLIER_ORDER_WITH_NOPRICEDEFINED is not set
                        $this->lines[$i]->desc,
                        $this->lines[$i]->subprice,
                        $this->lines[$i]->qty,
                        $this->lines[$i]->tva_tx,
                        $this->lines[$i]->localtax1_tx,
                        $this->lines[$i]->localtax2_tx,
                        $this->lines[$i]->fk_product,
                        0, //TODO Alejandro Revisar que es FP-7
                        $this->lines[$i]->product_ref,   // $this->lines[$i]->ref_fourn comes from field ref into table of lines. Value may ba a ref that does not exists anymore, so we first try with value of product
                        //$this->lines[$i]->ref_fourn,   // $this->lines[$i]->ref_fourn comes from field ref into table of lines. Value may ba a ref that does not exists anymore, so we first try with value of product
                        $this->lines[$i]->remise_percent,
                        'HT',
                        0,
                        $this->lines[$i]->product_type,
                        $this->lines[$i]->info_bits,
                        false,
                        $this->lines[$i]->date_start,
                        $this->lines[$i]->date_end,
                        0,
                        $this->lines[$i]->fk_unit
                    );
                    if ($result < 0)
                    {
                        dol_syslog(get_class($this)."::create ".$this->error, LOG_WARNING);	// do not use dol_print_error here as it may be a functionnal error
                        $this->db->rollback();
                        return -1;
                    }
                }

                $sql = "UPDATE ".MAIN_DB_PREFIX."commande_fournisseur";
                $sql.= " SET ref='(PROV".$this->id.")'";
                $sql.= " WHERE rowid=".$this->id;
                dol_syslog(get_class($this)."::create", LOG_DEBUG);
                if ($this->db->query($sql))
                {
                    // Add link with price request and supplier order
                    if ($this->id)
                    {
                        $this->ref="(PROV".$this->id.")";

                        if (! empty($this->linkedObjectsIds) && empty($this->linked_objects))	// To use new linkedObjectsIds instead of old linked_objects
                        {
                            $this->linked_objects = $this->linkedObjectsIds;	// TODO Replace linked_objects with linkedObjectsIds
                        }

                        // Add object linked
                        if (! $error && $this->id && is_array($this->linked_objects) && ! empty($this->linked_objects))
                        {
                            foreach($this->linked_objects as $origin => $tmp_origin_id)
                            {
                                if (is_array($tmp_origin_id))       // New behaviour, if linked_object can have several links per type, so is something like array('contract'=>array(id1, id2, ...))
                                {
                                    foreach($tmp_origin_id as $origin_id)
                                    {
                                        $ret = $this->add_object_linked($origin, $origin_id);
                                        if (! $ret)
                                        {
                                            dol_print_error($this->db);
                                            $error++;
                                        }
                                    }
                                }
                                else                                // Old behaviour, if linked_object has only one link per type, so is something like array('contract'=>id1))
                                {
                                    $origin_id = $tmp_origin_id;
                                    $ret = $this->add_object_linked($origin, $origin_id);
                                    if (! $ret)
                                    {
                                        dol_print_error($this->db);
                                        $error++;
                                    }
                                }
                            }
                        }
                    }

                    if (! $error)
                    {
                        $result=$this->insertExtraFields();
                        if ($result < 0) $error++;
                    }

                    if (! $error && ! $notrigger)
                    {
                        // Call trigger
                        $result=$this->call_trigger('ORDER_SUPPLIER_CREATE',$user);
                        if ($result < 0)
                        {
                            $this->db->rollback();
                            return -1;
                        }
                        // End call triggers
                    }

                    $this->db->commit();
                    return $this->id;
                }
                else
                {
                    $this->error=$this->db->lasterror();
                    $this->db->rollback();
                    return -2;
                }
            }
        }
        else
        {
            $this->error=$this->db->lasterror();
            $this->db->rollback();
            return -1;
        }
       //PropagateContacts::propagate($this,$this->id);
    }
    /**
     *	Add order line
     *
     *	@param      string	$desc            		Description
     *	@param      float	$pu_ht              	Unit price
     *	@param      float	$qty             		Quantity
     *	@param      float	$txtva           		Taux tva
     *	@param      float	$txlocaltax1        	Localtax1 tax
     *  @param      float	$txlocaltax2        	Localtax2 tax
     *	@param      int		$fk_product      		Id product
     *  @param      int		$fk_prod_fourn_price	Id supplier price
     *  @param      string	$fourn_ref				Supplier reference price
     *	@param      float	$remise_percent  		Remise
     *	@param      string	$price_base_type		HT or TTC
     *	@param		float	$pu_ttc					Unit price TTC
     *	@param		int		$type					Type of line (0=product, 1=service)
     *	@param		int		$info_bits				More information
     *  @param		bool	$notrigger				Disable triggers
     *  @param		int		$date_start				Date start of service
     *  @param		int		$date_end				Date end of service
     *  @param		array	$array_options			extrafields array
     *  @param 		string	$fk_unit 				Code of the unit to use. Null to use the default one
     *  @param 		string	$pu_ht_devise			Amount in currency
     *  @param		string	$origin					'order', ...
     *  @param		int		$origin_id				Id of origin object
     *	@return     int             				<=0 if KO, >0 if OK
     */
    public function addline($desc, $pu_ht, $qty, $txtva, $txlocaltax1=0.0, $txlocaltax2=0.0, $fk_product=0, $fk_prod_fourn_price=0, $fourn_ref='', $remise_percent=0.0, $price_base_type='HT', $pu_ttc=0.0, $type=0, $info_bits=0, $notrigger=false, $date_start=null, $date_end=null, $array_options=0, $fk_unit=null, $pu_ht_devise=0, $origin='', $origin_id=0)
    {
        global $langs,$mysoc,$conf;

        $error = 0;

        dol_syslog(get_class($this)."::addline $desc, $pu_ht, $qty, $txtva, $txlocaltax1, $txlocaltax2, $fk_product, $fk_prod_fourn_price, $fourn_ref, $remise_percent, $price_base_type, $pu_ttc, $type, $fk_unit");
        include_once DOL_DOCUMENT_ROOT.'/core/lib/price.lib.php';

        // Clean parameters
        if (! $qty) $qty=1;
        if (! $info_bits) $info_bits=0;
        if (empty($txtva)) $txtva=0;
        if (empty($txlocaltax1)) $txlocaltax1=0;
        if (empty($txlocaltax2)) $txlocaltax2=0;
        if (empty($remise_percent)) $remise_percent=0;

        $remise_percent=price2num($remise_percent);
        $qty=price2num($qty);
        $pu_ht=price2num($pu_ht);
        $pu_ttc=price2num($pu_ttc);
        $txtva = price2num($txtva);
        $txlocaltax1 = price2num($txlocaltax1);
        $txlocaltax2 = price2num($txlocaltax2);
        if ($price_base_type=='HT')
        {
            $pu=$pu_ht;
        }
        else
        {
            $pu=$pu_ttc;
        }
        $desc=trim($desc);
        $ref_supplier=''; // Ref of supplier price when we add line

        // Check parameters
        if ($qty < 1 && ! $fk_product)
        {
            $this->error=$langs->trans("ErrorFieldRequired",$langs->trans("Product"));
            return -1;
        }
        if ($type < 0) return -1;

        if ($this->statut == 0)
        {
            $this->db->begin();

            if ($fk_product > 0)
            {
                if (empty($conf->global->SUPPLIER_ORDER_WITH_NOPRICEDEFINED))
                {
                    // Check quantity is enough
                    dol_syslog(get_class($this)."::addline we check supplier prices fk_product=".$fk_product." fk_prod_fourn_price=".$fk_prod_fourn_price." qty=".$qty." fourn_ref=".$fourn_ref);
                    $prod = new Product($this->db, $fk_product);
                    if ($prod->fetch($fk_product) > 0)
                    {
                        $product_type = $prod->type;
                        $label = $prod->label;

                        // We use 'none' instead of $fourn_ref, because fourn_ref may not exists anymore. So we will take the first supplier price ok.
                        // If we want a dedicated supplier price, we must provide $fk_prod_fourn_price.
						$prod->get_buyprice(0, $qty,$fk_product, 'none', 0);
                        //$result=$prod->get_buyprice($fk_prod_fourn_price, $qty, $fk_product, 'none', ($this->fk_soc?$this->fk_soc:$this->socid));   // Search on couple $fk_prod_fourn_price/$qty first, then on triplet $qty/$fk_product/$fourn_ref/$this->fk_soc
                       // $result=1;
                       // if ($result > 0)
                       // {
                            $pu           = $prod->fourn_pu;                             // Unit price supplier price set by get_buyprice
                           if(!$pu) $pu=$pu_ht;
                            $ref_supplier = $prod->ref_supplier;   // Ref supplier price set by get_buyprice
                           //TODO Alejandro comentado esto en esta version if(!$ref_supplier) $ref_supplier="FP-".$this->socid;
                          // if(!$ref_supplier) $ref_supplier="FP-".$this->socid;
						    $this->thirdparty=$mysoc;
                            // is remise percent not keyed but present for the product we add it
                            if ($remise_percent == 0 && $prod->remise_percent !=0)
                                $remise_percent =$prod->remise_percent;


                      //  }
                        //TODO Alberto comentado para que grabe desde cualquier tercero
                       /* if ($result == 0)                   // If result == 0, we failed to found the supplier reference price
                        {
                            $langs->load("errors");
                            $this->error = "Ref " . $prod->ref . " " . $langs->trans("ErrorQtyTooLowForThisSupplier");
                            $this->db->rollback();
                            dol_syslog(get_class($this)."::addline we did not found supplier price, so we can't guess unit price");
                            //$pu    = $prod->fourn_pu;     // We do not overwrite unit price
                            //$ref   = $prod->ref_fourn;    // We do not overwrite ref supplier price
                            return -1;
                        }
                        if ($result == -1)
                        {
                            $langs->load("errors");
                            $this->error = "Ref " . $prod->ref . " " . $langs->trans("ErrorQtyTooLowForThisSupplier");
                            $this->db->rollback();
                            dol_syslog(get_class($this)."::addline result=".$result." - ".$this->error, LOG_DEBUG);
                            return -1;
                        }
                        if ($result < -1)
                        {
                            $this->error=$prod->error;
                            $this->db->rollback();
                            dol_syslog(get_class($this)."::addline result=".$result." - ".$this->error, LOG_ERR);
                            return -1;
                        }*/
                    }
                    else
                    {
                        $this->error=$prod->error;
                        return -1;
                    }
                }
            }
            else
            {
                $product_type = $type;
            }

            // Calcul du total TTC et de la TVA pour la ligne a partir de
            // qty, pu, remise_percent et txtva
            // TRES IMPORTANT: C'est au moment de l'insertion ligne qu'on doit stocker
            // la part ht, tva et ttc, et ce au niveau de la ligne qui a son propre taux tva.

            $localtaxes_type=getLocalTaxesFromRate($txtva,0,$mysoc,$this->thirdparty);

            // Clean vat code
            $vat_src_code='';
            if (preg_match('/\((.*)\)/', $txtva, $reg))
            {
                $vat_src_code = $reg[1];
                $txtva = preg_replace('/\s*\(.*\)/', '', $txtva);    // Remove code into vatrate.
            }

            $tabprice = calcul_price_total($qty, $pu, $remise_percent, $txtva, $txlocaltax1, $txlocaltax2, 0, $price_base_type, $info_bits, $product_type, $this->thirdparty, $localtaxes_type, 100, $this->multicurrency_tx,$pu_ht_devise);
            $total_ht  = $tabprice[0];
            $total_tva = $tabprice[1];
            $total_ttc = $tabprice[2];
            $total_localtax1 = $tabprice[9];
            $total_localtax2 = $tabprice[10];
            $pu_ht = $tabprice[3];

            // MultiCurrency
            $multicurrency_total_ht  = $tabprice[16];
            $multicurrency_total_tva = $tabprice[17];
            $multicurrency_total_ttc = $tabprice[18];
            $pu_ht_devise = $tabprice[19];

            $localtax1_type=$localtaxes_type[0];
            $localtax2_type=$localtaxes_type[2];

            $subprice = price2num($pu,'MU');

            $rangmax = $this->line_max();
            $rang = $rangmax + 1;

            // Insert line
            $this->line=new CommandeFournisseurLigne($this->db);

            $this->line->context = $this->context;

            $this->line->fk_commande=$this->id;
            $this->line->label=$label;
            $this->line->ref_fourn = $ref_supplier;
            $this->line->ref_supplier = $ref_supplier;
            $this->line->desc=$desc;
            $this->line->qty=$qty;
            $this->line->tva_tx=$txtva;
            $this->line->localtax1_tx=$txlocaltax1;
            $this->line->localtax2_tx=$txlocaltax2;
            $this->line->localtax1_type = $localtaxes_type[0];
            $this->line->localtax2_type = $localtaxes_type[2];
            $this->line->fk_product=$fk_product;
            $this->line->product_type=$product_type;
            $this->line->remise_percent=$remise_percent;
            $this->line->subprice=$pu_ht;
            $this->line->rang=$this->rang;
            $this->line->info_bits=$info_bits;

            $this->line->vat_src_code=$vat_src_code;
            $this->line->total_ht=$total_ht;
            $this->line->total_tva=$total_tva;
            $this->line->total_localtax1=$total_localtax1;
            $this->line->total_localtax2=$total_localtax2;
            $this->line->total_ttc=$total_ttc;
            $this->line->product_type=$type;
            $this->line->special_code=$this->special_code;
            $this->line->origin=$origin;
            $this->line->origin_id=$origin_id;
            $this->line->fk_unit=$fk_unit;

            $this->line->date_start=$date_start;
            $this->line->date_end=$date_end;

            // Multicurrency
            $this->line->fk_multicurrency			= $this->fk_multicurrency;
            $this->line->multicurrency_code			= $this->multicurrency_code;
            $this->line->multicurrency_subprice		= $pu_ht_devise;
            $this->line->multicurrency_total_ht 	= $multicurrency_total_ht;
            $this->line->multicurrency_total_tva 	= $multicurrency_total_tva;
            $this->line->multicurrency_total_ttc 	= $multicurrency_total_ttc;

            $this->line->subprice=$pu_ht;
            $this->line->price=$this->line->subprice;

            $this->line->remise_percent=$remise_percent;

            if (is_array($array_options) && count($array_options)>0) {
                $this->line->array_options=$array_options;
            }

            $result=$this->line->insert($notrigger);
            if ($result > 0)
            {
                // Reorder if child line
                if (! empty($fk_parent_line)) $this->line_order(true,'DESC');

                // Mise a jour informations denormalisees au niveau de la commande meme
                $result=$this->update_price(1,'auto',0,$this->thirdparty);	// This method is designed to add line from user input so total calculation must be done using 'auto' mode.
                if ($result > 0)
                {
                    $this->db->commit();
                    return $this->line->id;
                }
                else
                {
                    $this->db->rollback();
                    return -1;
                }
            }
            else
            {
                $this->error=$this->line->error;
                $this->errors=$this->line->errors;
                dol_syslog(get_class($this)."::addline error=".$this->error, LOG_ERR);
                $this->db->rollback();
                return -1;
            }
        }
    }


}
