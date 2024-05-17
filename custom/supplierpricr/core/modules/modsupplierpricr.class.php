<?php
/* Copyright (C) 2017-2022		Charlene BENKE	<charlene@patas-monkey.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
include_once(DOL_DOCUMENT_ROOT ."/core/modules/DolibarrModules.class.php");

/**
 * 		\class      modsupplierpricr
 *      \brief      Description and activation class for module MyModule
 */
class modsupplierpricr extends DolibarrModules
{

	var $disabled;

	/**
	 *   \brief      Constructor. Define names, constants, directories, boxes, permissions
	 *   \param      DB      Database handler
	 */
	function __construct($db)
	{
		global $langs; // $conf

		$langs->load('upplierpricr@supplierpricr');
		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 160115;

		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found
		$this->name = preg_replace('/^mod/i', '', get_class($this));

		$this->editor_name = "Patas-Monkey";
		$this->editor_web = "http://www.patas-monkey.com";

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "srm";

		// Module description, used if translation string 'ModuleXXXDesc' not found
		$this->description = $langs->trans("SupplierPricRPresentation");

		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = $this->getLocalVersion();

		// Key used in llx_const table to save module status enabled/disabled
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto=$this->name.'.png@'.$this->name;

		// Defined if the directory /mymodule/inc/triggers/ contains triggers or not
		$this->module_parts = array(
			'hooks' => array( 'supplier_proposalcard', 'ordersuppliercard', 'invoicesuppliercard')
			);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mymodule/temp");
		$this->dirs = array('/supplierpricr/temp');

		// Dependencies
		// List of modules id that must be enabled if this module is enabled
		$this->depends = array();
		$this->requiredby = array(); // List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = array(); // List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)
		$this->phpmin = array(4,3);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,4);	// Minimum version of Dolibarr required by module

		$this->langfiles = array($this->name."@".$this->name);

		// Config pages. Put here list of php page, stored into webmail/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@supplierpricr");

		// Constants
		// List of particular constants to add when module is enabled
		$this->const = array();

		// Array to add new pages in new tabs
		$this->tabs = array();

		// Boxes
		$this->boxes = array();			// List of boxes

		$r=0;
		$this->rights = array();
		$this->rights_class = $this->name;

		$r++;
		$this->rights[$r][0] = 1601151;
		$this->rights[$r][1] = 'Permettre la création des prix fournisseurs';
		$this->rights[$r][2] = 'c';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'create';

		// supplierpricr Menu
		/*
		$r=0;
		$this->menu[$r]=array(	
				'fk_menu'=>'fk_mainmenu=commercial',
				'type'=>'left',	
				'titre'=>'SupplierPricR',
				'mainmenu'=>'commercial',
				'leftmenu'=>'supplierpricr',
				'url'=>'/supplierpricr/index.php?leftmenu=supplierpricr',
				'langs'=>'supplierpricr@supplierpricr',
				'position'=>110, 'enabled'=>'1',
				'perms'=>'1',
				'target'=>'', 'user'=>2);
		$r++;
		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=commercial,fk_leftmenu=supplierpricr',
					'type'=>'left',
					'titre'=>'CreateSupplierShort',
					'mainmenu'=>'', 'leftmenu'=>'',
					'url'=>'/supplierpricr/fiche.php?action=add',
					'langs'=>'supplierpricr@supplierpricr',
					'position'=>110, 'enabled'=>'1',
					'perms'=>'1', 'target'=>'',
					'user'=>2);
		$r++;
		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=commercial,fk_leftmenu=supplierpricr',
					'type'=>'left',
					'titre'=>'ListSupplier',
					'mainmenu'=>'', 'leftmenu'=>'',
					'url'=>'/supplierpricr/list.php',
					'langs'=>'supplierpricr@supplierpricr',
					'position'=>110, 'enabled'=>'1',
					'perms'=>'1', 'target'=>'',
					'user'=>2);
		$r++;
		*/
	}

	/**
	 *		\brief      Function called when module is enabled.
	 *					The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *					It also creates data directories.
	 *      \return     int             1 if OK, 0 if KO
	 */
	function init($options='')
	{
//		global $conf;
		// Permissions
		$this->remove($options);

		$sql = array();
//		$result=$this->load_tables();
		return $this->_init($sql, $options);
	}

	/**
	 *		\brief		Function called when module is disabled.
	 *              	Remove from database constants, boxes and permissions from Dolibarr database.
	 *					Data directories are not deleted.
	 *      \return     int             1 if OK, 0 if KO
	 */
	function remove($options='')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}

	/**
	 *		Create tables, keys and data required by module
	 * 		Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * 		and create data commands must be stored in directory /mymodule/sql/
	 *		This function is called by this->init.
	 *
	 * 		@return		int		<=0 if KO, >0 if OK
	 */
	function load_tables()
	{
		//return $this->_load_tables('/myspec904/sql/');
	}

	function getVersion($translated = 1)
	{
		global $langs, $conf;
		$currentversion = $this->version;

		if (!empty($conf->global->PATASMONKEY_SKIP_CHECKVERSION) && $conf->global->PATASMONKEY_SKIP_CHECKVERSION == 1)
			return $currentversion;

		if ($this->disabled) {
			$newversion= $langs->trans("DolibarrMinVersionRequiered")." : ".$this->dolibarrminversion;
			$currentversion="<font color=red><b>".img_error($newversion).$currentversion."</b></font>";
			return $currentversion;
		}

		$context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
		$changelog = @file_get_contents(
						str_replace("www", "dlbdemo", $this->editor_web).'/htdocs/custom/'.$this->name.'/changelog.xml',
						false, $context
		);
		if ($changelog === false)
			return $currentversion;	// not connected
		else {
			$sxelast = simplexml_load_string(nl2br($changelog));
			if ($sxelast === false)
				return $currentversion;
			else
				$tblversionslast=$sxelast->Version;

			$lastversion = $tblversionslast[count($tblversionslast)-1]->attributes()->Number;

			if ($lastversion != (string) $this->version) {
				if ($lastversion > (string) $this->version) {
					$newversion= $langs->trans("NewVersionAviable")." : ".$lastversion;
					$currentversion="<font title='".$newversion."' color=orange><b>".$currentversion."</b></font>";
				} else
					$currentversion="<font title='Version Pilote' color=red><b>".$currentversion."</b></font>";
			}
		}
		return $currentversion;
	}

	function getChangeLog()
	{
		// Libraries
		dol_include_once("/".$this->name."/core/lib/patasmonkey.lib.php");
		return getChangeLog($this->name);
	}

	function getLocalVersion()
	{
		global $langs;
		$context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
		$changelog = @file_get_contents(dol_buildpath($this->name, 0).'/changelog.xml', false, $context);
		$sxelast = simplexml_load_string(nl2br($changelog));
		if ($sxelast === false)
			return $langs->trans("ChangelogXMLError").dol_buildpath($this->name, 0).'/changelog.xml' ;
		else {
			$tblversionslast=$sxelast->Version;
			$currentversion = (string) $tblversionslast[count($tblversionslast)-1]->attributes()->Number;
			$tblDolibarr=$sxelast->Dolibarr;
			$minVersionDolibarr=$tblDolibarr->attributes()->minVersion;
			if ((int) DOL_VERSION < (int) $minVersionDolibarr) {
				$this->dolibarrminversion=$minVersionDolibarr;
				$this->disabled = true;
			}
		}
		return $currentversion;
	}
}
