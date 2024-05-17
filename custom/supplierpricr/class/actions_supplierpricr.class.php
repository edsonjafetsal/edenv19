<?php
/* Copyright (C) 2014-2022	Charlene Benke		<charlene@patas-monkey.com>
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
 * 	\file	   	htdocs/supplierpricr/class/actions_supplierpricr.class.php
 * 	\ingroup	transporteur
 * 	\brief	  	Fichier de la classe des actions/hooks de transporteur
 */

dol_include_once('/supplierpricr/class/supplierpricr.class.php');

class ActionsSupplierpricr // extends CommonObject 
{

	// ajout du bouton pour les frais de transport

	function addMoreActionsButtons($parameters, $object, $action)
	{
		global $langs, $user ; // $conf, $db;
		// si on a des lignes de saisies et que l'on est à l'état signé
		if (count($object->lines) > 0 && (int) $object->statut >= 1 && $user->rights->supplierpricr->create ) {
			$langs->load("supplierpricr@supplierpricr");
			$objectelement = array("supplier_proposal", "order_supplier", "invoice_supplier");
			$arrayaction= array(
				"", "confirm_validate", "confirm_reopen", "classify",
				"addsupplierpricr", "setstatut", "builddoc", "dellink"
			);
			if (in_array($object->element, $objectelement) && in_array($action, $arrayaction)) {
				// on affiche le bouton d'ajout des prix
				print '<div class="inline-block divButAction">';
				print '<a class="butAction" style="background:#E87400;" ';
				print 'href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&token='.newToken().'&action=addsupplierpricr"';
				print '>'.$langs->trans("AddSupplierPricr").'</a>';
				print '</div>';
			}
		}
		return 0;
	}

	// Ajout des lignes de prix
	function doActions($parameters, $object, $action)
	{
		global $db, $langs;
		$langs->load("supplierpricr@supplierpricr");

		if ($action == "addsupplierpricr") {
			$supplierpricrStatic =  New Supplierpricr($db);
			$ret = $supplierpricrStatic->add_supplierprice($object);

			if ($ret >= 0)
				setEventMessages($langs->trans("SupplierPriceUpdated", $ret, count($object->lines)), null, 'mesgs');
			elseif ($ret == -9999) 
				setEventMessages($langs->trans("NoRefFournInOneLine"), null, 'errors');
			else
				setEventMessages($langs->trans("ErrorOnSupplierPrice", $supplierpricrStatic->error), null, 'errors');

		}
		return 0;
	}
}