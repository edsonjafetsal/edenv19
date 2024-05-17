<?php
/* Copyright (C) 2018-2018 Andre Schild        <a.schild@aarboard.ch>
 * Copyright (C) 2005-2010 Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin       <regis.houssin@inodbox.com>
 *
 * This file is an example to follow to add your own email selector inside
 * the Dolibarr email tool.
 * Follow instructions given in README file to know what to change to build
 * your own emailing list selector.
 * Code that need to be changed in this file are marked by "CHANGE THIS" tag.
 */

/**
 *	\file       htdocs/core/modules/mailings/salesRep.modules.php
 *	\ingroup    mailing
 *	\brief      Example file to provide a list of recipients for mailing module
 */

include_once DOL_DOCUMENT_ROOT.'/core/modules/mailings/modules_mailings.php';


/**
 *	Class to manage a list of personalised recipients for mailing feature
 */
class mailing_salesRep extends MailingTargets
{
	public $name = 'salesRep';
	// This label is used if no translation is found for key XXX neither MailingModuleDescXXX where XXX=name is found
	public $desc = "Sales Representative ";
	public $require_admin = 0;

	public $require_module = array("societe"); // This module allows to select by categories must be also enabled if category module is not activated

	/**
	 * @var string String with name of icon for myobject. Must be the part after the 'object_' into object_myobject.png
	 */
	public $picto = 'company';

	/**
	 * @var DoliDB Database handler.
	 */
	public $db;


	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		global $conf, $langs;
		$langs->load("companies");

		$this->db = $db;
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *    This is the main function that returns the array of emails
	 *
	 *    @param	int		$mailing_id    	Id of mailing. No need to use it.
	 *    @return   int 					<0 if error, number of emails added if ok
	 */
	public function add_to_target($mailing_id)
	{
		// phpcs:enable
		global $conf, $langs;

		$cibles = array();

		$addDescription = "";
		// Select the third parties from category
		if (empty($_POST['filter_SalesRep']))
		{
//			$sql = "SELECT s.rowid as id, s.email as email, s.nom as name, null as fk_contact, null as firstname, null as label";
//			$sql .= " FROM ".MAIN_DB_PREFIX."societe as s";
//			$sql .= " WHERE s.email <> ''";
//			$sql .= " AND s.entity IN (".getEntity('societe').")";
//			$sql .= " AND s.email NOT IN (SELECT email FROM ".MAIN_DB_PREFIX."mailing_cibles WHERE fk_mailing=".$mailing_id.")";
			setEventMessages('Filter  Sales Rep ','is empty','errors');
			$id =GETPOST('id', 'int');
			header("Location: ".$_SERVER['PHP_SELF']."?id=".$id);
			exit;
		} else {
			$addFilter = "";
			if (GETPOSTISSET("filter_status")) {
				if (strlen($addDescription) > 0) {
					$addDescription .= ";";
				}
				$addDescription .= $langs->trans("Status")."=";
				if (GETPOST("filter_status") == '1') {
					$addFilter .= " AND s.status=1";
					$addDescription .= $langs->trans("Enabled");
				} else {
					$addFilter .= " AND s.status=0";
					$addDescription .= $langs->trans("Disabled");
				}
			}
			$sql = "SELECT s.rowid as id, ls.email as email, sp.firstname as firstname , sp.lastname  as name, ";
			$sql .= "CONCAT(sp.firstname, ' ', sp.lastname )  as nameFull, sp.rowid as fk_contact, ";
			$sql .= " lsc.fk_c_type_contact as TypeContact ";
			$sql .= " FROM ".MAIN_DB_PREFIX."societe as s ";
			$sql .= " left join ".MAIN_DB_PREFIX."societe_commerciaux  AS sc on s.rowid  = sc.fk_soc   ";
			$sql .= " left join ".MAIN_DB_PREFIX."user AS sp    on sc.fk_user = sp.rowid    ";
			$sql .= " left join ".MAIN_DB_PREFIX."societe_contacts lsc  on s.rowid =lsc.fk_soc     ";
			$sql .= " left join ".MAIN_DB_PREFIX."socpeople ls  on ls.rowid =lsc.fk_socpeople   ";

			$sql .= " WHERE s.email <> ''";
			$sql .= " AND s.entity IN (".getEntity('societe').")";
			$sql .= " AND s.email NOT IN (SELECT email FROM ".MAIN_DB_PREFIX."mailing_cibles WHERE fk_mailing=".$mailing_id.")";
			//$sql .= " AND cs.fk_soc = s.rowid";
			$sql .= "  and ls.email <>''  ";

			$stcomm =GETPOST('filter_stcomm', 'int');
			$filter_SalesRep =GETPOST('filter_SalesRep', 'int');
			$filter_type_contact =GETPOST('filter_type_contact', 'int');

			if($filter_SalesRep>0&& $filter_SalesRep !="")  $sql .= "  AND sc.fk_user =".((int) $filter_SalesRep);
			if($filter_type_contact>0&& $filter_type_contact !="")  $sql .= "  AND  lsc.fk_c_type_contact  =".((int) $filter_type_contact);
			if($stcomm >=(-1) && $stcomm !="") $sql .= "   AND (s.fk_stcomm IN (".((int) $stcomm)."))  ";
			$sql .= $addFilter;
			$sql .= "  GROUP  BY  ls.email  ";
		}
		$sql .= " ORDER BY ls.email";

		// Stock recipients emails into targets table
		$result = $this->db->query($sql);
		if ($result)
		{
			$num = $this->db->num_rows($result);
			$i = 0;
			$j = 0;

			dol_syslog(get_class($this)."::add_to_target mailing ".$num." targets found");

			$old = '';
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($result);
				if ($old <> $obj->email)
				{
					$otherTxt = ('Sales Representation');
					if (strlen($addDescription) > 0 && strlen($otherTxt) > 0)
					{
						$otherTxt .= ";";
					}
					$otherTxt .= $addDescription;
					$cibles[$j] = array(
								'email' => $obj->email,
								'fk_contact' => $obj->fk_contact,
								'lastname' => $obj->name, // For a thirdparty, we must use name
								'firstname' => $obj->firstname, // For a thirdparty, lastname is ''
								'other' => $otherTxt,
								'source_url' => $this->url($obj->id),
								'source_id' => $obj->id,
								'source_type' => 'thirdparty'
					);
					$old = $obj->email;
					$j++;
				}

				$i++;
			}
		} else {
			dol_syslog($this->db->error());
			$this->error = $this->db->error();
			return -1;
		}

		return parent::addTargetsToDatabase($mailing_id, $cibles);
	}


	/**
	 *	On the main mailing area, there is a box with statistics.
	 *	If you want to add a line in this report you must provide an
	 *	array of SQL request that returns two field:
	 *	One called "label", One called "nb".
	 *
	 *	@return		array		Array with SQL requests
	 */
	public function getSqlArrayForStats()
	{
		// CHANGE THIS: Optionnal

		//var $statssql=array();
		//$this->statssql[0]="SELECT field1 as label, count(distinct(email)) as nb FROM mytable WHERE email IS NOT NULL";
		return array();
	}


	/**
	 *	Return here number of distinct emails returned by your selector.
	 *	For example if this selector is used to extract 500 different
	 *	emails from a text file, this function must return 500.
	 *
	 *  @param      string	$sql        Requete sql de comptage
	 *	@return		int					Nb of recipients
	 */
	public function getNbOfRecipients($sql = '')
	{
		global $conf;

		$sql = " SELECT count(u.rowid) as nb";
		$sql .= " FROM  ".MAIN_DB_PREFIX."user as u WHERE ";
		$sql .= " u.entity IN (".getEntity('societe').") and u.email <> ''";
		$sql .= " ORDER BY statut DESC, lastname ASC";

		// La requete doit retourner un champ "nb" pour etre comprise
		// par parent::getNbOfRecipients
		return parent::getNbOfRecipients($sql);
	}

	/**
	 *  This is to add a form filter to provide variant of selector
	 *	If used, the HTML select must be called "filter"
	 *
	 *  @return     string      A html select zone
	 */
	public function formFilter()
	{
		global $conf, $langs;

		$langs->load("companies");

		$s = $langs->trans("Sales Rep").': ';
		$s .= '<select name="filter_SalesRep" class="flat">';

		// Show categories
//		$sql = "SELECT rowid, label, type, visible";
//		$sql .= " FROM ".MAIN_DB_PREFIX."categorie";
//		$sql .= " WHERE type in (1,2)"; // We keep only categories for suppliers and customers/prospects
//		// $sql.= " AND visible > 0";	// We ignore the property visible because third party's categories does not use this property (only products categories use it).
//		$sql .= " AND entity = ".$conf->entity;
//		$sql .= " ORDER BY label";
	// Show categories
		//select * from llx_c_type_contact where `element`  = 'propal'

		//  from llx_element_contact AS ec  , llx_socpeople ls
		//  where 1=1
		//	and 	ec.fk_socpeople = ls.rowid
		//  group by fk_socpeople
//		$sql = "select CONCAT( ls.firstname, ' ', ls.lastname) as name, ls.rowid as id  ";
//		$sql .= " FROM ".MAIN_DB_PREFIX."element_contact AS ec  , ".MAIN_DB_PREFIX."socpeople ls ";
//		//$sql .= " FROM ".MAIN_DB_PREFIX."c_type_contact";
//		$sql .= " WHERE  1=1"; // We keep only categories for suppliers and customers/prospects
//		$sql .= " AND 	ec.fk_socpeople = ls.rowid "; // We keep only categories for suppliers and customers/prospects
//		$sql .= " AND 	ls.statut=1 "; // We keep only categories for suppliers and customers/prospects
//		$sql .= " AND 	 ls.email IS NOT NULL "; // We keep only categories for suppliers and customers/prospects
//		$sql .= " AND ls.entity IN (".getEntity('societe').")";
//		// $sql.= " AND visible > 0";	// We ignore the property visible because third party's categories does not use this property (only products categories use it).
//		$sql .= " group by fk_socpeople";
		//$sql .= " ORDER BY rowid";
//
// SELECT u.rowid, u.lastname, u.firstname, u.statut as status, u.login, u.photo, u.gender, u.entity, u.admin
// FROM llx_user as u WHERE u.entity IN (0,1) ORDER BY statut DESC, lastname ASC

		$sql = " SELECT u.rowid as id , CONCAT( u.firstname, ' ', u.lastname) as name, u.statut as status, u.login, u.photo, u.gender, u.entity, u.admin";
		$sql .= " FROM  ".MAIN_DB_PREFIX."user as u WHERE ";
		$sql .= " u.entity IN (".getEntity('societe').") and u.email <> ''";
		$sql .= " ORDER BY statut DESC, lastname ASC";
		//print $sql;
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);



			if ($num) $s .= '<option value="0">&nbsp;</option>';
			else $s .= '<option value="0">'.$langs->trans("ContactsAllShort").'</option>';

			$i = 0;
			while ($i < $num)
			{ //select 1  sales rep
				$obj = $this->db->fetch_object($resql);
				global $db;
				$sql = "SELECT ls2.email   as total";
				$sql .=" FROM ".MAIN_DB_PREFIX."societe_commerciaux ls  ";
				$sql .=" left join ".MAIN_DB_PREFIX."societe ls2  on ls.fk_soc = ls2.rowid  ";
				$sql .=" where ls.fk_user = ".$obj->id;
				$sql .=" group by ls2.email  ";
				// $sql .="   group by fk_c_type_contact";
				$re2 = $this->db->query($sql);
				$num2 = $this->db->num_rows($re2);
				if($num2>0){
					$Count = $this->db->fetch_object($re2);
					$totalnum=$num2;
				}else{$totalnum =0;}
				$s .= '<option value="'.$obj->id.'">'.dol_trunc($obj->name, 38, 'middle');

				//$s .= '</option>';
				$s .= ' ('.$totalnum.')</option>';
				$i++;
			}
		} else {
			dol_print_error($this->db);
		}
		$s .= '</select> <br>';




		//segundo select
		$s .= $langs->trans("Type Contact").': ';


		$sql = "SELECT *";
		$sql .= " FROM ".MAIN_DB_PREFIX."c_type_contact";
		$sql .= " WHERE `element`  = 'propal'"; // We keep only categories for suppliers and customers/prospects
		// $sql.= " AND visible > 0";	// We ignore the property visible because third party's categories does not use this property (only products categories use it).
		$sql .= " and active  =1";
		$sql .= " ORDER BY rowid";
		$resql = $this->db->query($sql);
		$num = $this->db->num_rows($resql);
		$s .= '<select name="filter_type_contact" class="flat">';  //FILTRO 2
		if ($num) $s .= '<option value="0">&nbsp;</option>';
		else $s .= '<option value="0">'.$langs->trans("ContactsAllShort").'</option>';
		$i = 0;
		while ($i < $num)
		{
			$obj = $this->db->fetch_object($resql);
			global $db;    //COUNT FINTRO 1
			$sql = "SELECT COUNT(ls.rowid) as total";
			$sql .=" FROM ".MAIN_DB_PREFIX."societe ls left join ".MAIN_DB_PREFIX."societe_contacts lsc on ls.rowid = lsc.fk_soc  where lsc.fk_c_type_contact = ".$obj->rowid;
			$re2 = $this->db->query($sql);
			$num2 = $this->db->num_rows($re2);
			if($num2>0){
				$Count = $this->db->fetch_object($re2);
				$totalnum=$Count->total;
			}else{$totalnum =0;}
			$s .= '<option value="'.$obj->rowid.'">'.dol_trunc($obj->libelle, 38, 'middle');

			//$s .= '</option>';
			$s .= ' ('.$Count->total.')</option>';
			$i++;
		}
		$s .= '</select> <br>';




		//tercero select
		$s .= $langs->trans('ProspectStatus');   //select status
		$sql='  select lcs.id as id,   lcs.libelle  as label from '.MAIN_DB_PREFIX.'c_stcomm lcs where active=1';
		$re2 = $this->db->query($sql);
		$num = $this->db->num_rows($re2);
		$s .= ': <select name="filter_stcomm" class="flat">';
		$s .= '<option value="">&nbsp;</option>';
		$i=0;
		while ($i < $num)
		{	$obj = $this->db->fetch_object($re2);
				$sql ="select count(s.rowid) AS total from ".MAIN_DB_PREFIX."societe s where  s.fk_stcomm =".$obj->id;
					$re2Status = $this->db->query($sql);
					$num2Status = $this->db->num_rows($re2Status);
					if($num2Status>0){
						$CountStatus = $this->db->fetch_object($re2Status);
						$totalnumStatus=$CountStatus->total;
					}else{$totalnumStatus =0;}


			$s .= '<option value="'.$obj->id.'">'.dol_trunc($obj->label, 38, 'middle');

			//$s .= '</option>';
			$s .= ' ('.$totalnumStatus.')</option>';
			$i++;
		}
//		if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS) && empty($conf->global->SOCIETE_DISABLE_CUSTOMERS) && empty($conf->global->SOCIETE_DISABLE_PROSPECTSCUSTOMERS)) {
//			$s .= '<option value="3">'.$langs->trans('ProspectCustomer').'</option>';
//		}
//		if (empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) {
//			$s .= '<option value="1">'.$langs->trans('Customer').'</option>';
//		}
//		$s .= '<option value="0">'.$langs->trans('NorProspectNorCustomer').'</option>';

		$s .= '</select> ';

		$s .= $langs->trans("Status");
		$s .= ': <select name="filter_status" class="flat">';
		$s .= '<option value="-1">&nbsp;</option>';
		$s .= '<option value="1" selected>'.$langs->trans("Enabled").'</option>';
		$s .= '<option value="0">'.$langs->trans("Disabled").'</option>';
		$s .= '</select>';
		return $s;
	}


	/**
	 *  Can include an URL link on each record provided by selector shown on target page.
	 *
	 *  @param	int		$id		ID
	 *  @return string      	Url link
	 */
	public function url($id)
	{
		return '<a href="'.DOL_URL_ROOT.'/societe/card.php?socid='.$id.'">'.img_object('', "company").'</a>';
	}
}
