<?php
/* Copyright (C) 2004-2018 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2018	   Nicolas ZABOURI 	<info@inovea-conseil.com>
 * Copyright (C) 2019 Admin <marcello.gribaudo@gmail.com>
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

/**
 * 	\defgroup   agendarecurevents     Module Agendarecurevents
 *  \brief      Agendarecurevents module descriptor.
 *
 *  \file       htdocs/agendarecurevents/core/modules/modAgendarecurevents.class.php
 *  \ingroup    agendarecurevents
 *  \brief      Description and activation file for module Agendarecurevents
 */
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';


/**
 *  Description and activation class for module Agendarecurevents
 */
class modAgendarecurevents extends DolibarrModules {
    /**
     * Constructor. Define names, constants, directories, boxes, permissions
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db) {
        global $langs,$conf;

        $this->db = $db;

        // Id for module (must be unique).
        // Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
        $this->numero = 220820;		// TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve id number for your module
        // Key text used to identify module (for permissions, menus, etc...)
        $this->rights_class = 'agendarecurevents';

        // Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
        // It is used to group modules by family in module setup page
        $this->family = "projects";
        // Module position in the family on 2 digits ('01', '10', '20', ...)
        $this->module_position = '90';
        // Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
        //$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));

        // Module label (no space allowed), used if translation string 'ModuleAgendarecureventsName' not found (Agendarecurevents is name of module).
        $this->name = preg_replace('/^mod/i','',get_class($this));
        // Module description, used if translation string 'ModuleAgendarecureventsDesc' not found (Agendarecurevents is name of module).
        $this->description = "AgendarecureventsDescription";
        // Used only if file README.md and README-LL.md not found.
        $this->descriptionlong = "Agendarecurevents description (Long)";

        $this->editor_name = 'Marcello Gribaudo';
        $this->editor_url = 'https://www.opigi.com';


        // Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
        $this->version = '1.1';

        //Url to the file with your last numberversion of this module
        //$this->url_last_version = 'http://www.example.com/versionmodule.txt';
        // Key used in llx_const table to save module status enabled/disabled (where AGENDARECUREVENTS is value of property name of module in uppercase)
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        // Name of image file used for this module.
        // If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
        // If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
        $this->picto='action';

        // Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
        $this->module_parts = array('triggers' => 1,                                 	// Set this to 1 if module has its own trigger directory (core/triggers)
                                    'login' => 0,                                    	// Set this to 1 if module has its own login method file (core/login)
                                    'substitutions' => 0,                            	// Set this to 1 if module has its own substitution function file (core/substitutions)
                                    'menus' => 0,                                    	// Set this to 1 if module has its own menus handler directory (core/menus)
                                    'theme' => 0,                                    	// Set this to 1 if module has its own theme directory (theme)
                                    'tpl' => 0,                                      	// Set this to 1 if module overwrite template dir (core/tpl)
                                    'barcode' => 0,                                  	// Set this to 1 if module has its own barcode directory (core/modules/barcode)
                                    'models' => 0,                                      // Set this to 1 if module has its own models directory (core/modules/xxx)
                                    'css' => array(),                                   // Set this to relative path of css file if module has its own css file
                                    'js' => array()                         ,           // Set this to relative path of js file if module must load a js on all pages
                                    'hooks' => array(),                                 // Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context 'all'
                                    'moduleforexternal' => 0				// Set this to 1 if feature of module are opened to external users
        );


        $this->dirs = array();

        // Config pages. Put here list of php page, stored into agendarecurevents/admin directory, to use to setup module.
        $this->config_page_url = array("setup.php@agendarecurevents");
        
        
        // Dependencies
        $this->hidden = false;			// A condition to hide module
        $this->depends = array('always'=>"modAgenda");		// List of module class names as string that must be enabled if this module is enabled. Example: array('always1'=>'modModuleToEnable1','always2'=>'modModuleToEnable2', 'FR1'=>'modModuleToEnableFR'...)
        //$this->depends = array();
        $this->requiredby = array();	// List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
        $this->conflictwith = array();	// List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)
        $this->langfiles = array("agendarecurevents@agendarecurevents");
        $this->phpmin = array(5,4);					// Minimum version of PHP required by module
        $this->need_dolibarr_version = array(3,0);		// Minimum version of Dolibarr required by module
        $this->warnings_activation = array();			// Warning to show when we activate module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
        $this->warnings_activation_ext = array();		// Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
        //$this->automatic_activation = array('FR'=>'AgendarecureventsWasAutomaticallyActivatedBecauseOfYourCountryChoice');
        //$this->always_enabled = true;								// If true, can't be disabled

        // Constants
        // List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
        // Example: $this->const=array(0=>array('AGENDARECUREVENTS_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
        //                             1=>array('AGENDARECUREVENTS_MYNEWCONST2','chaine','myvalue','This is another constant to add',0, 'current', 1)
        // );
        $this->const = array();

        if (! isset($conf->agendarecurevents) || ! isset($conf->agendarecurevents->enabled)) {}


        // Array to add new pages in new tabs
        $this->tabs = array();
        $this->tabs[] = array('data'=>'action:+recurevent:RecurrentEvent:agendarecurevents@agendarecurevents:$user->rights->agendarecurevents->create:/agendarecurevents/recurevents.php?id=__ID__');  					
        // Example:
        // $this->tabs[] = array('data'=>'objecttype:+tabname1:Title1:mylangfile@agendarecurevents:$user->rights->agendarecurevents->read:/agendarecurevents/mynewtab1.php?id=__ID__');  					// To add a new tab identified by code tabname1
        // $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@agendarecurevents:$user->rights->othermodule->read:/agendarecurevents/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
        // $this->tabs[] = array('data'=>'objecttype:-tabname:NU:conditiontoremove');                                                     										// To remove an existing tab identified by code tabname
        //
        // Where objecttype can be
        // 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
        // 'contact'          to add a tab in contact view
        // 'contract'         to add a tab in contract view
        // 'group'            to add a tab in group view
        // 'intervention'     to add a tab in intervention view
        // 'invoice'          to add a tab in customer invoice view
        // 'invoice_supplier' to add a tab in supplier invoice view
        // 'member'           to add a tab in fundation member view
        // 'opensurveypoll'	  to add a tab in opensurvey poll view
        // 'order'            to add a tab in customer order view
        // 'order_supplier'   to add a tab in supplier order view
        // 'payment'		  to add a tab in payment view
        // 'payment_supplier' to add a tab in supplier payment view
        // 'product'          to add a tab in product view
        // 'propal'           to add a tab in propal view
        // 'project'          to add a tab in project view
        // 'stock'            to add a tab in stock view
        // 'thirdparty'       to add a tab in third party view
        // 'user'             to add a tab in user view


        // Dictionaries
        $this->dictionaries=array();
        /* Example:
        $this->dictionaries=array(
            'langs'=>'mylangfile@agendarecurevents',
            'tabname'=>array(MAIN_DB_PREFIX."table1",MAIN_DB_PREFIX."table2",MAIN_DB_PREFIX."table3"),		// List of tables we want to see into dictonnary editor
            'tablib'=>array("Table1","Table2","Table3"),													// Label of tables
            'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),	// Request to select fields
            'tabsqlsort'=>array("label ASC","label ASC","label ASC"),																					// Sort order
            'tabfield'=>array("code,label","code,label","code,label"),																					// List of fields (result of select to show dictionary)
            'tabfieldvalue'=>array("code,label","code,label","code,label"),																				// List of fields (list of fields to edit a record)
            'tabfieldinsert'=>array("code,label","code,label","code,label"),																			// List of fields (list of fields for insert)
            'tabrowid'=>array("rowid","rowid","rowid"),																									// Name of columns with primary key (try to always name it 'rowid')
            'tabcond'=>array($conf->agendarecurevents->enabled,$conf->agendarecurevents->enabled,$conf->agendarecurevents->enabled)												// Condition to show each dictionary
        );
        */


        // Boxes/Widgets
	// Add here list of php file(s) stored in agendarecurevents/core/boxes that contains class to show a widget.
        $this->boxes = array(
        	//0=>array('file'=>'agendarecureventswidget1.php@agendarecurevents','note'=>'Widget provided by Agendarecurevents','enabledbydefaulton'=>'Home'),
        	//1=>array('file'=>'agendarecureventswidget2.php@agendarecurevents','note'=>'Widget provided by Agendarecurevents'),
        	//2=>array('file'=>'agendarecureventswidget3.php@agendarecurevents','note'=>'Widget provided by Agendarecurevents')
        );


        // Cronjobs (List of cron jobs entries to add when module is enabled)
        // unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
        /*$this->cronjobs = array(
                0=>array('label'=>'MyJob label', 'jobtype'=>'method', 'class'=>'/agendarecurevents/class/myobject.class.php', 'objectname'=>'MyObject', 'method'=>'doScheduledJob', 'parameters'=>'', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'$conf->agendarecurevents->enabled', 'priority'=>50)
        );*/
        // Example: $this->cronjobs=array(0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'$conf->agendarecurevents->enabled', 'priority'=>50),
        //                                1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'$conf->agendarecurevents->enabled', 'priority'=>50)
        // );


        // Permissions
        $this->rights = array();		// Permission array used by this module

        $r=0;
        $this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
        $this->rights[$r][1] = 'CreateRecurrentEvents';	// Permission label
        $this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
        $this->rights[$r][4] = 'create';				// In php code, permission will be checked by test if ($user->rights->agendarecurevents->level1->level2)
        $this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->agendarecurevents->level1->level2)


        // Main menu entries
        $this->menu = array();			// List of menus to add
        $r=0;


        // Exports
        $r=1;

        /* BEGIN MODULEBUILDER EXPORT MYOBJECT */
        /*
        $langs->load("agendarecurevents@agendarecurevents");
        $this->export_code[$r]=$this->rights_class.'_'.$r;
        $this->export_label[$r]='MyObjectLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
        $this->export_icon[$r]='myobject@agendarecurevents';
        $keyforclass = 'MyObject'; $keyforclassfile='/mymobule/class/myobject.class.php'; $keyforelement='myobject';
        include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
        $keyforselect='myobject'; $keyforaliasextra='extra'; $keyforelement='myobject';
        include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
        //$this->export_dependencies_array[$r]=array('mysubobject'=>'ts.rowid', 't.myfield'=>array('t.myfield2','t.myfield3')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
        $this->export_sql_start[$r]='SELECT DISTINCT ';
        $this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'myobject as t';
        $this->export_sql_end[$r] .=' WHERE 1 = 1';
        $this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('myobject').')';
        $r++; */
        /* END MODULEBUILDER EXPORT MYOBJECT */
    }

    /**
     *	Function called when module is enabled.
     *	The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
     *	It also creates data directories
     *
     *	@param      string	$options    Options when enabling module ('', 'noboxes')
     *	@return     int             	1 if OK, 0 if KO
     */
    public function init($options='') {
        $result=$this->_load_tables('/agendarecurevents/sql/');
        if ($result < 0) return -1; // Do not activate module if not allowed errors found on module SQL queries (the _load_table run sql with run_sql with error allowed parameter to 'default')

        // Create extrafields
        include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
        $extrafields = new ExtraFields($this->db);

        //$result1=$extrafields->addExtraField('assigned', "Assigned", 'boolean', 1,  3, 'actioncomm',   0, 0, '', '', 1, '', 0, 0, '', '', 'agendarecurevents@agendarecurevents', '$conf->agendarecurevents->enabled');
        //$result2=$extrafields->addExtraField('myattr2', "New Attr 2 label", 'varchar', 1, 10, 'project',      0, 0, '', '', 1, '', 0, 0, '', '', 'agendarecurevents@agendarecurevents', '$conf->agendarecurevents->enabled');
        //$result3=$extrafields->addExtraField('myattr3', "New Attr 3 label", 'varchar', 1, 10, 'bank_account', 0, 0, '', '', 1, '', 0, 0, '', '', 'agendarecurevents@agendarecurevents', '$conf->agendarecurevents->enabled');
        //$result4=$extrafields->addExtraField('myattr4', "New Attr 4 label", 'select',  1,  3, 'thirdparty',   0, 1, '', array('options'=>array('code1'=>'Val1','code2'=>'Val2','code3'=>'Val3')), 1 '', 0, 0, '', '', 'agendarecurevents@agendarecurevents', '$conf->agendarecurevents->enabled');
        //$result5=$extrafields->addExtraField('myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', 0, 0, '', '', 'agendarecurevents@agendarecurevents', '$conf->agendarecurevents->enabled');

        $sql = array();

        $a = $this->_init($sql, $options);
        return $a;
    }

    /**
     *	Function called when module is disabled.
     *	Remove from database constants, boxes and permissions from Dolibarr database.
     *	Data directories are not deleted
     *
     *	@param      string	$options    Options when enabling module ('', 'noboxes')
     *	@return     int             	1 if OK, 0 if KO
     */
    public function remove($options = '') {
        $sql = array();

        return $this->_remove($sql, $options);
    }
}
