<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

Class Formimport extends Form {
    
    /**
     *  Output html form to select a third party
     *
     *	@param	string	$selected       		Preselected type
     *	@param  string	$htmlname       		Name of field in form
     *  @param  string	$filter         		optional filters criteras (example: 's.rowid <> x', 's.client IN (1,3)')
     *	@param	string	$showempty				Add an empty field (Can be '1' or text key to use on empty line like 'SelectThirdParty')
     * 	@param	int		$showtype				Show third party type in combolist (customer, prospect or supplier)
     * 	@param	int		$forcecombo				Force to use combo box
     *  @param	array	$events					Ajax event options to run on change. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
     *	@param	int		$limit					Maximum number of elements
     *  @param	string	$morecss				Add more css styles to the SELECT component
     *	@param  string	$moreparam      		Add more parameters onto the select tag. For example 'style="width: 95%"' to avoid select2 component to go over parent container
	 *	@param	string	$selected_input_value	Value of preselected input text (for use with ajax)
     *  @param	int		$hidelabel				Hide label (0=no, 1=yes, 2=show search icon (before) and placeholder, 3 search icon after)
     *  @param	array	$ajaxoptions			Options for ajax_autocompleter
     * 	@return	string							HTML string with select box for thirdparty.
     */
    function select_propal($selected='', $htmlname='propalid', $filter='', $showempty='', $showtype=0, $forcecombo=0, $events=array(), $limit=0, $morecss='minwidth100', $moreparam='', $selected_input_value='', $hidelabel=1, $ajaxoptions=array())
    {
    	global $conf,$user,$langs;

    	$out='';

    	if (0 && ! empty($conf->use_javascript_ajax) && ! empty($conf->global->COMPANY_USE_SEARCH_TO_SELECT) && ! $forcecombo)
    	{
    	    // No immediate load of all database
    		$placeholder='';
    		if ($selected && empty($selected_input_value))
    		{
    			require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
    			$societetmp = new Propal($this->db);
    			$societetmp->fetch($selected);
    			$selected_input_value=$societetmp->name;
    			unset($societetmp);
    		}
    		// mode 1
    		$urloption='htmlname='.$htmlname.'&outjson=1&filter='.$filter.($showtype?'&showtype='.$showtype:'');
    		$out.=  ajax_autocompleter($selected, $htmlname, dol_buildpath('/importline/js/ajaxsearch.php',1), $urloption, $conf->global->COMPANY_USE_SEARCH_TO_SELECT, 0, $ajaxoptions);
			$out.='<style type="text/css">
					.ui-autocomplete {
						z-index: 250;
					}
				</style>';
    		if (empty($hidelabel)) print $langs->trans("RefOrLabel").' : ';
    		else if ($hidelabel > 1) {
    			if (! empty($conf->global->MAIN_HTML5_PLACEHOLDER)) $placeholder=' placeholder="'.$langs->trans("RefOrLabel").'"';
    			else $placeholder=' title="'.$langs->trans("RefOrLabel").'"';
    			if ($hidelabel == 2) {
    				$out.=  img_picto($langs->trans("Search"), 'search');
    			}
    		}
            $out.=  '<input type="text" class="'.$morecss.'" name="search_'.$htmlname.'" id="search_'.$htmlname.'" value="'.$selected_input_value.'"'.$placeholder.' '.(!empty($conf->global->THIRDPARTY_SEARCH_AUTOFOCUS) ? 'autofocus' : '').' />';
    		if ($hidelabel == 3) {
    			$out.=  img_picto($langs->trans("Search"), 'search');
    		}
    	}
    	else
    	{
    	    // Immediate load of all database
    		$out.=$this->select_propal_list($selected, $htmlname, $filter, $showempty, $showtype, $forcecombo, $events, '', 0, $limit, $morecss, $moreparam);
    	}

    	return $out;
    }
    
    /**
     *  Output html form to select a third party.
     *  Note, you must use the select_company to get the component to select a third party. This function must only be called by select_company.
     *
     *	@param	string	$selected       Preselected type
     *	@param  string	$htmlname       Name of field in form
     *  @param  string	$filter         optional filters criteras (example: 's.rowid <> x', 's.client in (1,3)')
     *	@param	string	$showempty		Add an empty field (Can be '1' or text to use on empty line like 'SelectThirdParty')
     * 	@param	int		$showtype		Show third party type in combolist (customer, prospect or supplier)
     * 	@param	int		$forcecombo		Force to use combo box
     *  @param	array	$events			Event options. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
     *  @param	string	$filterkey		Filter on key value
     *  @param	int		$outputmode		0=HTML select string, 1=Array
     *  @param	int		$limit			Limit number of answers
     *  @param	string	$morecss		Add more css styles to the SELECT component
     *	@param  string	$moreparam      Add more parameters onto the select tag. For example 'style="width: 95%"' to avoid select2 component to go over parent container
     * 	@return	string					HTML string with
     */
    function select_propal_list($selected='',$htmlname='propalid',$filter='',$showempty='', $showtype=0, $forcecombo=0, $events=array(), $filterkey='', $outputmode=0, $limit=0, $morecss='minwidth100', $moreparam='')
    {
        global $conf,$user,$langs;

        $out='';
        $num=0;
        $outarray=array();

        // On recherche les societes
        $sql = "SELECT p.ref,p.rowid,s.nom as name, s.name_alias";
        $sql.= " FROM ".MAIN_DB_PREFIX ."propal as p";
        $sql.= " INNER JOIN ".MAIN_DB_PREFIX ."societe as s ON s.rowid=p.fk_soc";

        $sql.= " WHERE s.entity IN (".getEntity('societe').")";
        $sql .=" AND p.fk_statut != 0";
        if ($filter) $sql.= " AND (".$filter.")";
        // Add criteria

        $sql.=$this->db->order("ref","ASC");
		if ($limit > 0) $sql.=$this->db->plimit($limit);

		// Build output string
        dol_syslog(get_class($this)."::select_thirdparty_list", LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
           	if ($conf->use_javascript_ajax && ! $forcecombo)
            {
				include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
            	$comboenhancement =ajax_combobox($htmlname, $events, $conf->global->COMPANY_USE_SEARCH_TO_SELECT);
            	$out.= $comboenhancement;
            }

            // Construct $out and $outarray
            $out.= '<select id="'.$htmlname.'" class="flat'.($morecss?' '.$morecss:'').'"'.($moreparam?' '.$moreparam:'').' name="'.$htmlname.'">'."\n";

            $textifempty='';
            // Do not use textifempty = ' ' or '&nbsp;' here, or search on key will search on ' key'.
            //if (! empty($conf->use_javascript_ajax) || $forcecombo) $textifempty='';
            if (! empty($conf->global->COMPANY_USE_SEARCH_TO_SELECT))
            {
                if ($showempty && ! is_numeric($showempty)) $textifempty=$langs->trans($showempty);
                else $textifempty.=$langs->trans("All");
            }
            if ($showempty) $out.= '<option value="-1">'.$textifempty.'</option>'."\n";

			$num = $this->db->num_rows($resql);
            $i = 0;
            if ($num)
            {
                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($resql);
                    $label='';
                    if ($conf->global->SOCIETE_ADD_REF_IN_LIST) {
                    	$label.=$obj->ref;
                    }
                    else
                    {
                    	$label=$obj->ref." ".$obj->name;
                    }

					if(!empty($obj->name_alias)) {
						$label.=' ('.$obj->name_alias.')';
					}

                    if ($showtype)
                    {
                        if ($obj->client || $obj->fournisseur) $label.=' (';
                        if ($obj->client == 1 || $obj->client == 3) $label.=$langs->trans("Customer");
                        if ($obj->client == 2 || $obj->client == 3) $label.=($obj->client==3?', ':'').$langs->trans("Prospect");
                        if ($obj->fournisseur) $label.=($obj->client?', ':'').$langs->trans("Supplier");
                        if ($obj->client || $obj->fournisseur) $label.=')';
                    }
                    if ($selected > 0 && $selected == $obj->rowid)
                    {
                        $out.= '<option value="'.$obj->rowid.'" selected>'.$label.'</option>';
                    }
                    else
					{
                        $out.= '<option value="'.$obj->rowid.'">'.$label.'</option>';
                    }

                    array_push($outarray, array('key'=>$obj->rowid, 'value'=>$label, 'label'=>$label));

                    $i++;
                    if (($i % 10) == 0) $out.="\n";
                }
            }
            $out.= '</select>'."\n";
        }
        else
        {
            dol_print_error($this->db);
        }

        $this->result=array('nbofthirdparties'=>$num);

        if ($outputmode) return $outarray;
        return $out;
    }
    
    
    
     /**
     *  Output html form to select a third party
     *
     *	@param	string	$selected       		Preselected type
     *	@param  string	$htmlname       		Name of field in form
     *  @param  string	$filter         		optional filters criteras (example: 's.rowid <> x', 's.client IN (1,3)')
     *	@param	string	$showempty				Add an empty field (Can be '1' or text key to use on empty line like 'SelectThirdParty')
     * 	@param	int		$showtype				Show third party type in combolist (customer, prospect or supplier)
     * 	@param	int		$forcecombo				Force to use combo box
     *  @param	array	$events					Ajax event options to run on change. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
     *	@param	int		$limit					Maximum number of elements
     *  @param	string	$morecss				Add more css styles to the SELECT component
     *	@param  string	$moreparam      		Add more parameters onto the select tag. For example 'style="width: 95%"' to avoid select2 component to go over parent container
	 *	@param	string	$selected_input_value	Value of preselected input text (for use with ajax)
     *  @param	int		$hidelabel				Hide label (0=no, 1=yes, 2=show search icon (before) and placeholder, 3 search icon after)
     *  @param	array	$ajaxoptions			Options for ajax_autocompleter
     * 	@return	string							HTML string with select box for thirdparty.
     */
    function select_order($selected='', $htmlname='orderid', $filter='', $showempty='', $showtype=0, $forcecombo=0, $events=array(), $limit=0, $morecss='minwidth100', $moreparam='', $selected_input_value='', $hidelabel=1, $ajaxoptions=array())
    {
    	global $conf,$user,$langs;

    	$out='';

    	if (0 && ! empty($conf->use_javascript_ajax) && ! empty($conf->global->COMPANY_USE_SEARCH_TO_SELECT) && ! $forcecombo)
    	{
    	    // No immediate load of all database
    		$placeholder='';
    		if ($selected && empty($selected_input_value))
    		{
    			require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
    			$societetmp = new Commande($this->db);
    			$societetmp->fetch($selected);
    			$selected_input_value=$societetmp->name;
    			unset($societetmp);
    		}
    		// mode 1
    		$urloption='htmlname='.$htmlname.'&outjson=1&filter='.$filter.($showtype?'&showtype='.$showtype:'');
    		$out.=  ajax_autocompleter($selected, $htmlname, dol_buildpath('/importline/js/ajaxsearchorder.php',1), $urloption, $conf->global->COMPANY_USE_SEARCH_TO_SELECT, 0, $ajaxoptions);
			$out.='<style type="text/css">
					.ui-autocomplete {
						z-index: 250;
					}
				</style>';
    		if (empty($hidelabel)) print $langs->trans("RefOrLabel").' : ';
    		else if ($hidelabel > 1) {
    			if (! empty($conf->global->MAIN_HTML5_PLACEHOLDER)) $placeholder=' placeholder="'.$langs->trans("RefOrLabel").'"';
    			else $placeholder=' title="'.$langs->trans("RefOrLabel").'"';
    			if ($hidelabel == 2) {
    				$out.=  img_picto($langs->trans("Search"), 'search');
    			}
    		} 
            $out.=  '<input type="text" class="'.$morecss.'" name="search_'.$htmlname.'" id="search_'.$htmlname.'" value="'.$selected_input_value.'"'.$placeholder.' '.(!empty($conf->global->THIRDPARTY_SEARCH_AUTOFOCUS) ? 'autofocus' : '').' />';
    		if ($hidelabel == 3) {
    			$out.=  img_picto($langs->trans("Search"), 'search');
    		}
    	}
    	else
    	{
    	    // Immediate load of all database
    		$out.=$this->select_order_list($selected, $htmlname, $filter, $showempty, $showtype, $forcecombo, $events, '', 0, $limit, $morecss, $moreparam);
    	}

    	return $out;
    }
    
    /**
     *  Output html form to select a third party.
     *  Note, you must use the select_company to get the component to select a third party. This function must only be called by select_company.
     *
     *	@param	string	$selected       Preselected type
     *	@param  string	$htmlname       Name of field in form
     *  @param  string	$filter         optional filters criteras (example: 's.rowid <> x', 's.client in (1,3)')
     *	@param	string	$showempty		Add an empty field (Can be '1' or text to use on empty line like 'SelectThirdParty')
     * 	@param	int		$showtype		Show third party type in combolist (customer, prospect or supplier)
     * 	@param	int		$forcecombo		Force to use combo box
     *  @param	array	$events			Event options. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
     *  @param	string	$filterkey		Filter on key value
     *  @param	int		$outputmode		0=HTML select string, 1=Array
     *  @param	int		$limit			Limit number of answers
     *  @param	string	$morecss		Add more css styles to the SELECT component
     *	@param  string	$moreparam      Add more parameters onto the select tag. For example 'style="width: 95%"' to avoid select2 component to go over parent container
     * 	@return	string					HTML string with
     */
    function select_order_list($selected='',$htmlname='orderid',$filter='',$showempty='', $showtype=0, $forcecombo=0, $events=array(), $filterkey='', $outputmode=0, $limit=0, $morecss='minwidth100', $moreparam='')
    {
        global $conf,$user,$langs;

        $out='';
        $num=0;
        $outarray=array();

        // On recherche les societes
        $sql = "SELECT p.ref,p.rowid,s.nom as name, s.name_alias";
        $sql.= " FROM ".MAIN_DB_PREFIX ."commande as p";
        $sql.= " INNER JOIN ".MAIN_DB_PREFIX ."societe as s ON s.rowid=p.fk_soc";

        $sql.= " WHERE s.entity IN (".getEntity('societe').") AND p.fk_statut !=0";
        if ($filter) $sql.= " AND (".$filter.")";
        // Add criteria
        
        $sql.=$this->db->order("ref","ASC");
		if ($limit > 0) $sql.=$this->db->plimit($limit);

		// Build output string
        dol_syslog(get_class($this)."::select_thirdparty_list", LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
           	if ($conf->use_javascript_ajax && ! $forcecombo)
            {
				include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
            	$comboenhancement =ajax_combobox($htmlname, $events, $conf->global->COMPANY_USE_SEARCH_TO_SELECT);
            	$out.= $comboenhancement;
            }

            // Construct $out and $outarray
            $out.= '<select id="'.$htmlname.'" class="flat'.($morecss?' '.$morecss:'').'"'.($moreparam?' '.$moreparam:'').' name="'.$htmlname.'">'."\n";

            $textifempty='';
            // Do not use textifempty = ' ' or '&nbsp;' here, or search on key will search on ' key'.
            //if (! empty($conf->use_javascript_ajax) || $forcecombo) $textifempty='';
            if (! empty($conf->global->COMPANY_USE_SEARCH_TO_SELECT))
            {
                if ($showempty && ! is_numeric($showempty)) $textifempty=$langs->trans($showempty);
                else $textifempty.=$langs->trans("All");
            }
            if ($showempty) $out.= '<option value="-1">'.$textifempty.'</option>'."\n";

			$num = $this->db->num_rows($resql);
            $i = 0;
            if ($num)
            {
                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($resql);
                    $label='';
                    if ($conf->global->SOCIETE_ADD_REF_IN_LIST) {
                    	$label.=$obj->ref;
                    }
                    else
                    {
                    	$label=$obj->ref." ".$obj->name;
                    }

					if(!empty($obj->name_alias)) {
						$label.=' ('.$obj->name_alias.')';
					}

                    if ($showtype)
                    {
                        if ($obj->client || $obj->fournisseur) $label.=' (';
                        if ($obj->client == 1 || $obj->client == 3) $label.=$langs->trans("Customer");
                        if ($obj->client == 2 || $obj->client == 3) $label.=($obj->client==3?', ':'').$langs->trans("Prospect");
                        if ($obj->fournisseur) $label.=($obj->client?', ':'').$langs->trans("Supplier");
                        if ($obj->client || $obj->fournisseur) $label.=')';
                    }
                    if ($selected > 0 && $selected == $obj->rowid)
                    {
                        $out.= '<option value="'.$obj->rowid.'" selected>'.$label.'</option>';
                    }
                    else
					{
                        $out.= '<option value="'.$obj->rowid.'">'.$label.'</option>';
                    }

                    array_push($outarray, array('key'=>$obj->rowid, 'value'=>$label, 'label'=>$label));

                    $i++;
                    if (($i % 10) == 0) $out.="\n";
                }
            }
            $out.= '</select>'."\n";
        }
        else
        {
            dol_print_error($this->db);
        }

        $this->result=array('nbofthirdparties'=>$num);

        if ($outputmode) return $outarray;
        return $out;
    }


    /**
     *  Output html form to select a third party
     *
     *	@param	string	$selected       		Preselected type
     *	@param  string	$htmlname       		Name of field in form
     *  @param  string	$filter         		optional filters criteras (example: 's.rowid <> x', 's.client IN (1,3)')
     *	@param	string	$showempty				Add an empty field (Can be '1' or text key to use on empty line like 'SelectThirdParty')
     * 	@param	int		$showtype				Show third party type in combolist (customer, prospect or supplier)
     * 	@param	int		$forcecombo				Force to use combo box
     *  @param	array	$events					Ajax event options to run on change. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
     *	@param	int		$limit					Maximum number of elements
     *  @param	string	$morecss				Add more css styles to the SELECT component
     *	@param  string	$moreparam      		Add more parameters onto the select tag. For example 'style="width: 95%"' to avoid select2 component to go over parent container
     *	@param	string	$selected_input_value	Value of preselected input text (for use with ajax)
     *  @param	int		$hidelabel				Hide label (0=no, 1=yes, 2=show search icon (before) and placeholder, 3 search icon after)
     *  @param	array	$ajaxoptions			Options for ajax_autocompleter
     * 	@return	string							HTML string with select box for thirdparty.
     */
    function select_invoice($selected='', $htmlname='invoiceid', $filter='', $showempty='', $showtype=0, $forcecombo=0, $events=array(), $limit=0, $morecss='minwidth100', $moreparam='', $selected_input_value='', $hidelabel=1, $ajaxoptions=array())
    {
        global $conf,$user,$langs;

        $out='';

        if (0 && ! empty($conf->use_javascript_ajax) && ! empty($conf->global->COMPANY_USE_SEARCH_TO_SELECT) && ! $forcecombo)
        {
            // No immediate load of all database
            $placeholder='';
            if ($selected && empty($selected_input_value))
            {
                require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
                $societetmp = new Facture($this->db);
                $societetmp->fetch($selected);
                $selected_input_value=$societetmp->name;
                unset($societetmp);
            }
            // mode 1
            $urloption='htmlname='.$htmlname.'&outjson=1&filter='.$filter.($showtype?'&showtype='.$showtype:'');
            $out.=  ajax_autocompleter($selected, $htmlname, dol_buildpath('/importline/js/ajaxsearchinvoice.php',1), $urloption, $conf->global->COMPANY_USE_SEARCH_TO_SELECT, 0, $ajaxoptions);
            $out.='<style type="text/css">
					.ui-autocomplete {
						z-index: 250;
					}
				</style>';
            if (empty($hidelabel)) print $langs->trans("RefOrLabel").' : ';
            else if ($hidelabel > 1) {
                if (! empty($conf->global->MAIN_HTML5_PLACEHOLDER)) $placeholder=' placeholder="'.$langs->trans("RefOrLabel").'"';
                else $placeholder=' title="'.$langs->trans("RefOrLabel").'"';
                if ($hidelabel == 2) {
                    $out.=  img_picto($langs->trans("Search"), 'search');
                }
            }
            $out.=  '<input type="text" class="'.$morecss.'" name="search_'.$htmlname.'" id="search_'.$htmlname.'" value="'.$selected_input_value.'"'.$placeholder.' '.(!empty($conf->global->THIRDPARTY_SEARCH_AUTOFOCUS) ? 'autofocus' : '').' />';
            if ($hidelabel == 3) {
                $out.=  img_picto($langs->trans("Search"), 'search');
            }
        }
        else
        {
            // Immediate load of all database
            $out.=$this->select_invoice_list($selected, $htmlname, $filter, $showempty, $showtype, $forcecombo, $events, '', 0, $limit, $morecss, $moreparam);
        }

        return $out;
    }

    /**
     *  Output html form to select a third party.
     *  Note, you must use the select_company to get the component to select a third party. This function must only be called by select_company.
     *
     *	@param	string	$selected       Preselected type
     *	@param  string	$htmlname       Name of field in form
     *  @param  string	$filter         optional filters criteras (example: 's.rowid <> x', 's.client in (1,3)')
     *	@param	string	$showempty		Add an empty field (Can be '1' or text to use on empty line like 'SelectThirdParty')
     * 	@param	int		$showtype		Show third party type in combolist (customer, prospect or supplier)
     * 	@param	int		$forcecombo		Force to use combo box
     *  @param	array	$events			Event options. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
     *  @param	string	$filterkey		Filter on key value
     *  @param	int		$outputmode		0=HTML select string, 1=Array
     *  @param	int		$limit			Limit number of answers
     *  @param	string	$morecss		Add more css styles to the SELECT component
     *	@param  string	$moreparam      Add more parameters onto the select tag. For example 'style="width: 95%"' to avoid select2 component to go over parent container
     * 	@return	string					HTML string with
     */
    function select_invoice_list($selected='',$htmlname='invoiceid',$filter='',$showempty='', $showtype=0, $forcecombo=0, $events=array(), $filterkey='', $outputmode=0, $limit=0, $morecss='minwidth100', $moreparam='')
    {
        global $conf,$user,$langs;

        $out='';
        $num=0;
        $outarray=array();

        if ((int)DOL_VERSION >= 10) {
            // On recherche les societes
            $sql = "SELECT f.ref,f.rowid,s.nom as name, s.name_alias";
            $sql.= " FROM ".MAIN_DB_PREFIX ."facture as f";
            $sql.= " INNER JOIN ".MAIN_DB_PREFIX ."societe as s ON s.rowid=f.fk_soc";

            $sql.= " WHERE s.entity IN (".getEntity('societe').")";
            $sql .=" AND f.fk_statut != 0";

            if ($filter) $sql.= " AND (".$filter.")";
            // Add criteria

            $sql.=$this->db->order("ref","ASC");
        } else {
            // On recherche les societes
            $sql = "SELECT f.facnumber,f.rowid,s.nom as name, s.name_alias";
            $sql.= " FROM ".MAIN_DB_PREFIX ."facture as f";
            $sql.= " INNER JOIN ".MAIN_DB_PREFIX ."societe as s ON s.rowid=f.fk_soc";

            $sql.= " WHERE s.entity IN (".getEntity('societe').")";
            $sql .=" AND f.fk_statut != 0";

            if ($filter) $sql.= " AND (".$filter.")";
            // Add criteria

            $sql.=$this->db->order("facnumber","ASC");
        }

        if ($limit > 0) $sql.=$this->db->plimit($limit);

        // Build output string
        dol_syslog(get_class($this)."::select_thirdparty_list", LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($conf->use_javascript_ajax && ! $forcecombo)
            {
                include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
                $comboenhancement =ajax_combobox($htmlname, $events, $conf->global->COMPANY_USE_SEARCH_TO_SELECT);
                $out.= $comboenhancement;
            }

            // Construct $out and $outarray
            $out.= '<select id="'.$htmlname.'" class="flat'.($morecss?' '.$morecss:'').'"'.($moreparam?' '.$moreparam:'').' name="'.$htmlname.'">'."\n";

            $textifempty='';
            // Do not use textifempty = ' ' or '&nbsp;' here, or search on key will search on ' key'.
            //if (! empty($conf->use_javascript_ajax) || $forcecombo) $textifempty='';
            if (! empty($conf->global->COMPANY_USE_SEARCH_TO_SELECT))
            {
                if ($showempty && ! is_numeric($showempty)) $textifempty=$langs->trans($showempty);
                else $textifempty.=$langs->trans("All");
            }
            if ($showempty) $out.= '<option value="-1">'.$textifempty.'</option>'."\n";

            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num)
            {
                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($resql);
                    $label='';
                    if ($conf->global->SOCIETE_ADD_REF_IN_LIST) {
                        $label.=$obj->ref;
                    }
                    else
                    {
                        $label=$obj->ref." ".$obj->name;
                    }

                    if(!empty($obj->name_alias)) {
                        $label.=' ('.$obj->name_alias.')';
                    }

                    if ($showtype)
                    {
                        if ($obj->client || $obj->fournisseur) $label.=' (';
                        if ($obj->client == 1 || $obj->client == 3) $label.=$langs->trans("Customer");
                        if ($obj->client == 2 || $obj->client == 3) $label.=($obj->client==3?', ':'').$langs->trans("Prospect");
                        if ($obj->fournisseur) $label.=($obj->client?', ':'').$langs->trans("Supplier");
                        if ($obj->client || $obj->fournisseur) $label.=')';
                    }
                    if ($selected > 0 && $selected == $obj->rowid)
                    {
                        $out.= '<option value="'.$obj->rowid.'" selected>'.$label.'</option>';
                    }
                    else
                    {
                        $out.= '<option value="'.$obj->rowid.'">'.$label.'</option>';
                    }

                    array_push($outarray, array('key'=>$obj->rowid, 'value'=>$label, 'label'=>$label));

                    $i++;
                    if (($i % 10) == 0) $out.="\n";
                }
            }
            $out.= '</select>'."\n";
        }
        else
        {
            dol_print_error($this->db);
        }

        $this->result=array('nbofthirdparties'=>$num);

        if ($outputmode) return $outarray;
        return $out;
    }
}

