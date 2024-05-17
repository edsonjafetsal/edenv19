<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2009 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis@dolibarr.fr>
 * Copyright (C) 2011-2012 Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2013-2018 Ferran Marcet		<fmarcet@2byte.es>
 * Copyright (C) 2019-2021 Ruben Ruger		    <rruger@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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

/**
 * 		\defgroup   reports     Module reports
 *      \brief      Reporting showing tool.
 *					Such a file must be copied into htdocs/includes/module directory.
 */

/**
 *      \file       htdocs/includes/modules/modReports.class.php
 *      \ingroup    reports
 *      \brief      Description and activation file for module reports
 */
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';


/**
 * 		\class      modMyModule
 *      \brief      Description and activation class for module MyModule
 */
class modReports extends DolibarrModules
{
	/**
	 *   \brief      Constructor. Define names, constants, directories, boxes, permissions
	 *   \param      DB      Database handler
	 */
	public function __construct($db)
	{
        global $langs,$conf;

        $this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 400005;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'reports';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = 'technic';
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = 'Reporting tool';
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '19.0.0';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 2;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto = 'reports@reports';

		$this->editor_name = '<b>2byte.es</b>';
		$this->editor_web = 'www.2byte.es';

		// Defined if the directory /mymodule/includes/triggers/ contains triggers or not
		$this->triggers = 0;
		$this->module_parts = array('css' => array('/reports/css/reports.css'));

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mymodule/temp");
		$this->dirs = array();
		$r=0;

		// Relative path to module style sheet if exists. Example: '/mymodule/css/mycss.css'.
		//$this->style_sheet = '/mymodule/mymodule.css.php';

		// Config pages. Put here list of php page names stored in admmin directory used to setup module.
		$this->config_page_url = array('reports.php@reports');

		// Dependencies
		$this->depends = array();		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
		$this->phpmin = array(5,6);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(7,0);	// Minimum version of Dolibarr required by module
		$this->langfiles = array('reports@reports');

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(0=>array('MYMODULE_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
		//                             1=>array('MYMODULE_MYNEWCONST2','chaine','myvalue','This is another constant to add',0) );
		$this->const = array();

		// Array to add new pages in new tabs
		// Example: $this->tabs = array('objecttype:+tabname1:Title1:@mymodule:$user->rights->mymodule->read:/mymodule/mynewtab1.php?id=__ID__',  // To add a new tab identified by code tabname1
        //                              'objecttype:+tabname2:Title2:@mymodule:$user->rights->othermodule->read:/mymodule/mynewtab2.php?id=__ID__',  // To add another new tab identified by code tabname2
        //                              'objecttype:-tabname');                                                     // To remove an existing tab identified by code tabname
		// where objecttype can be
		// 'thirdparty'       to add a tab in third party view
		// 'intervention'     to add a tab in intervention view
		// 'order_supplier'   to add a tab in supplier order view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'invoice'          to add a tab in customer invoice view
		// 'order'            to add a tab in customer order view
		// 'product'          to add a tab in product view
		// 'stock'            to add a tab in stock view
		// 'propal'           to add a tab in propal view
		// 'member'           to add a tab in fundation member view
		// 'contract'         to add a tab in contract view
		// 'user'             to add a tab in user view
		// 'group'            to add a tab in group view
		// 'contact'          to add a tab in contact view
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
        $this->tabs = array();

        // Dictionnaries
        $this->dictionaries=array();
        /*
        $this->dictionaries=array(
            'langs'=>'cabinetmed@cabinetmed',
            'tabname'=>array(MAIN_DB_PREFIX."cabinetmed_diaglec",MAIN_DB_PREFIX."cabinetmed_examenprescrit",MAIN_DB_PREFIX."cabinetmed_motifcons"),
            'tablib'=>array("DiagnostiqueLesionnel","ExamenPrescrit","MotifConsultation"),
            'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'cabinetmed_diaglec as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'cabinetmed_examenprescrit as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'cabinetmed_motifcons as f'),
            'tabsqlsort'=>array("label ASC","label ASC","label ASC"),
            'tabfield'=>array("code,label","code,label","code,label"),
            'tabfieldvalue'=>array("code,label","code,label","code,label"),
            'tabfieldinsert'=>array("code,label","code,label","code,label"),
            'tabrowid'=>array("rowid","rowid","rowid"),
            'tabcond'=>array($conf->cabinetmed->enabled,$conf->cabinetmed->enabled,$conf->cabinetmed->enabled)
        );
        */

        // Boxes
		// Add here list of php file(s) stored in includes/boxes that contains class to show a box.
        $this->boxes = array();			// List of boxes
		$r=0;
		// Example:
		/*
		$this->boxes[$r][1] = "myboxa.php";
		$r++;
		$this->boxes[$r][1] = "myboxb.php";
		$r++;
		*/

		// Permissions
		$this->rights = array();
		$this->rights_class = 'reports';
		$r=0;

		$r++;
		$this->rights[$r][0] = 4000040;
		$this->rights[$r][1] = 'DesignReports';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'admin';

		$r++;
		$this->rights[$r][0] = 4000041;
		$this->rights[$r][1] = 'UseReports';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'use';

		$sql = 'SELECT rowid, name, code';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'reports_report';
		$sql.= ' WHERE 	active =1';

		$resql = $this->db->query($sql);
		if($resql){
			$num = $this->db->num_rows($resql);
			$i = 0;
			while($i < $num){
				$obj = $this->db->fetch_object($resql);
				$r++;
				$this->rights[$r][0] = '400004'.($obj->rowid+1);
				$this->rights[$r][1] = $obj->name;
				$this->rights[$r][2] = 'a';
				$this->rights[$r][3] = 1;
				$this->rights[$r][4] = $obj->code;

				$i++;
			}
		}

		// Add here list of permission defined by an id, a label, a boolean and two constant strings.
		// Example:
		// $this->rights[$r][0] = 2000; 				// Permission id (must not be already used)
		// $this->rights[$r][1] = 'Permision label';	// Permission label
		// $this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		// $this->rights[$r][4] = 'level1';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		// $this->rights[$r][5] = 'level2';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		// $r++;

		// Main menu entries
		$this->menus = array();			// List of menus to add
		$r=0;

		// Add here entries to declare new menus
		// Menu Superior: 0
		$this->menu[$r]=array(	'fk_menu'=>0,			// Put 0 if this is a top menu
									'type'=>'top',			// This is a Top menu entry
									'titre'=>'Reports',
									'mainmenu'=>'reports',
									'leftmenu'=>'1',		// Use 1 if you also want to add left menu entries using this descriptor.
									'url'=>'/reports/index.php',
									'langs'=>'reports@reports',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
									'position'=>100,
									'enabled'=>'$conf->reports->enabled',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
									'perms'=>'$user->rights->reports->use',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
									'target'=>'',
									'user'=>0);				// 0=Menu for internal users, 1=external users, 2=both
		 $r++;
	}

	/**
	 * Function called when module is enabled.
	 * The init function adds tabs, constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 * It also creates data directories
	 *
	 * @param string $options   Options when enabling module ('', 'newboxdefonly', 'noboxes')
	 *                          'noboxes' = Do not insert boxes
	 *                          'newboxdefonly' = For boxes, insert def of boxes only and not boxes activation
	 * @return int				1 if OK, 0 if KO
	 */
    public function init($options = '')
    {
        $sql = array();

        dol_mkdir(dol_buildpath('/reports/',0).'includes/reportico/templates_c/', '',777);

        $result=$this->load_tables();

        $reportsPath = '/reports/includes/reportico/projects/Dolibarr/';

        $modVersion = strtok($this->version,'.');

        for ($version = $modVersion; $version >= 6; $version--) {

			if (version_compare((int)DOL_VERSION, $version) <= 0) {

				$dir = dol_buildpath($reportsPath, 0);
				$updatedir = @opendir(dol_buildpath('/reports/includes/reportico/projects/updates/' . $version . '/',
					0));
				if (is_resource($updatedir)) {

					$updatefiles = array();

					while (($file = readdir($updatedir)) !== false) {

						$updatefiles[] = $file;

					}

					sort($updatefiles);

					foreach ($updatefiles as $file) {

						if (preg_match('/\.xml$/i', $file)) {

							$origin = dol_buildpath('/reports/includes/reportico/projects/updates/' . $version . '/' . $file);
							$replaced = $dir . $file;

							if (dol_copy($origin, $replaced) == -1) {

								$msg = 'dol_copy failed Permission denied to overwrite target file';
								setEventMessages($msg, null, 'warnings');
								return false;

							} elseif (dol_copy($origin, $replaced) == -2) {

								$msg = 'dol_copy failed Permission denied to write into target directory';
								setEventMessages($msg, null, 'warnings');
								return false;

							} elseif (dol_copy($origin, $replaced) == -3) {

								$msg = 'dol_copy failed to copy';
								setEventMessages($msg, null, 'warnings');
								return false;

							}

						}

					}

				}

			}

			if ($updatedir) {
				closedir($updatedir);
			}
		}

        if(version_compare(DOL_VERSION, 3.8) < 0) {
            //reports no compatible dolibarr 3.7
            $sql2 = 'UPDATE '.MAIN_DB_PREFIX.'reports_report SET active = 0 WHERE rowid IN (25,38,42)';
            $result2 = $this->db->query($sql2);
        }

        return $this->_init($sql);
    }

	/**
	 * Function called when module is disabled.
	 * The remove function removes tabs, constants, boxes, permissions and menus from Dolibarr database.
	 * Data directories are not deleted
	 *
	 * @param      string	$options    Options when enabling module ('', 'noboxes')
	 * @return     int             		1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql);
	}


	/**
	 *		\brief		Create tables, keys and data required by module
	 * 					Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * 					and create data commands must be stored in directory /mymodule/sql/
	 *					This function is called by this->init.
	 * 		\return		int		<=0 if KO, >0 if OK
	 */
	public function load_tables()
	{
		return $this->_load_tables('/reports/sql/');
	}
}

