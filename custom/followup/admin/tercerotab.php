<?php
/* Copyright (C) 2001-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005      Brice Davoleau       <brice.davoleau@gmail.com>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2006-2019 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2007      Patrick Raguin  		<patrick.raguin@gmail.com>
 * Copyright (C) 2010      Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2015      Marcos García        <marcosgdf@gmail.com>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *  \file       htdocs/societe/agenda.php
 *  \ingroup    societe
 *  \brief      Page of third party events
 */

require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
global $user, $conf, $langs, $db, $hookmanager;
$langs->loadLangs(array("companies", "bills", "propal", "orders"));

if (GETPOST('actioncode', 'array'))
{
	$actioncode = GETPOST('actioncode', 'array', 3);
	if (!count($actioncode)) $actioncode = '0';
} else {
	$actioncode = GETPOST("actioncode", "alpha", 3) ?GETPOST("actioncode", "alpha", 3) : (GETPOST("actioncode") == '0' ? '0' : (empty($conf->global->AGENDA_DEFAULT_FILTER_TYPE_FOR_OBJECT) ? '' : $conf->global->AGENDA_DEFAULT_FILTER_TYPE_FOR_OBJECT));
}
$search_agenda_label = GETPOST('search_agenda_label');

// Security check
$socid = GETPOST('socid', 'int');
if ($user->socid) $socid = $user->socid;
$result = restrictedArea($user, 'societe', $socid, '&societe');

$limit = GETPOST('limit', 'int') ?GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
if (empty($page) || $page == -1) { $page = 0; }     // If $page is not defined, or '' or -1
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

/*
 *	Actions
 */

$parameters = array('id'=>$socid);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	if (GETPOST('action')=='setfollowup'){
		$object = new Societe($db);
		$object->fetch($socid);
		$object->array_options['options_followupenable'] = GETPOST('value');
		$object->update($socid, $user);
//		$object->updateExtraField('followupenable', GETPOST('value'));
	}
	// Cancel
	if (GETPOST('cancel', 'alpha') && !empty($backtopage))
	{
		header("Location: ".$backtopage);
		exit;
	}

	// Purge search criteria
	if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) // All tests are required to be compatible with all browsers
	{
		$actioncode = '';
		$search_agenda_label = '';
	}
}



/*
 *	View
 */

$form = new Form($db);
$extrajs='';
$extracss='';

$extrajs = array(
	'/followup/inc/bower_components/chart.js/Chart.js',
	'/followup/inc/bower_components/raphael/raphael.min.js',
	'/followup/inc/bower_components/morris.js/morris.js',
	'/followup/inc/bootstrap/dist/js/bootstrap.js',
	'/followup/inc/sweet/sweetalert2.min.js',
	'../includes/jquery/plugins/blockUI/jquery.blockUI.js'
);

$extracss = array(
	'/followup/inc/bootstrap/dist/css/bootstrap.css',
	'/followup/inc/bower_components/morris.js/morris.css',
	'/followup/inc/sweet/sweetalert2.min.css',
);



llxHeader('', $langs->trans("FollowUp"), $help_url, '', '', '', $extrajs, $extracss);
//print load_fiche_titre($langs->trans("Follow Up"), $linkback, 'followup@followup', 0, 'followup');

if ($socid > 0)
{
	require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';

	$object = new Societe($db);
	$result = $object->fetch($socid);


	if (!empty($conf->notification->enabled)) $langs->load("mails");
	$head = societe_prepare_head($object);

	print dol_get_fiche_head($head, 'followup', $langs->trans("Follow Up"), -1, 'company');

	$linkback = '<a href="'.DOL_URL_ROOT.'/comm/action/list.php?search_actioncode=AC_OTH">'.$langs->trans("BackToList").'</a>';

	dol_banner_tab($object, 'socid', $linkback, ($user->socid ? 0 : 1), 'rowid', 'nom');
	print '<form action="'.$_SERVER["PHP_SELF"].'?socid='.$socid.'" method="post">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="setfollowup">';
	print '<tr class="oddeven"><td colspan="2">';
	print '<div class="bg-gray color-palette">';
	if (!$object->array_options['options_followupenable'])	{
		print '<a href="'.$_SERVER["PHP_SELF"].'?action=setfollowup&token='.newToken().'&value=1&socid='.$socid.'">';
		print img_picto($langs->trans("Activated"), 'switch_on');
		print '</a>';
	} else {
		print '<a href="'.$_SERVER["PHP_SELF"].'?action=setfollowup&token='.newToken().'&value=0&socid='.$socid.'">';
		print img_picto($langs->trans("Disabled"), 'switch_off');
		print '</a>';
	}

	print '<span>';
	print '</form>';
	if ($object->array_options['options_followupenable']){
	print '  Enable Follow Up';
	} else{
	print '  Disable Follow Up';
	}
	print '<span class="hideifnotset">';
	print '</span></div>';
	print '<div class="fichecenter">';

	print '<div class="underbanner clearboth"></div>';

	include DOL_DOCUMENT_ROOT.'/custom/followup/followupprincipal.php';

	print '</div>';

	print dol_get_fiche_end();



}

// End of page
llxFooter();
$db->close();
