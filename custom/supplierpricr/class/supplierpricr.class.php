<?php
/* Copyright (C) 2016-2022	Charlene BENKE	<charlene@patas-monkey.com>
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
 *	\file	   htdocs/customline/class/customline.class.php
 *	\ingroup	tools
 *	\brief	  File of class to customline moduls
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';


/**
 *	Class to manage members type
 */
class Supplierpricr
{

	/**
	 *	Constructor
	 *
	 *	@param 		DoliDB		$db		Database handler
	 */
	function __construct($db)
	{
		$this->db = $db;
	}


	// on ajoute / modifie l'objet
	function add_supplierprice(&$object)
	{
		global $langs, $conf, $user;

		$fk_soc = $object->socid;
		$nbcreate=0;
		$nbreffournempty = 0;
		// controle de la ref fournisseur
		foreach ($object->lines as $curline) {
			if ($curline->ref_fourn == "" && $curline->fk_product > 0 )
				$nbreffournempty++;
		}
			
		if ($nbreffournempty > 0 && !empty($conf->global->SUPPLIERPRICR_BLOCKONEMPTYREFFOURN))
			return -9999;

		foreach ($object->lines as $curline) {
			if ($curline->fk_product > 0 && (empty($conf->global->SUPPLIERPRICR_BLOCKONEMPTYREFFOURN) 
				|| (!empty($conf->global->SUPPLIERPRICR_BLOCKONEMPTYREFFOURN) && $curline->ref_fourn != "" ))) {

				$fk_product=$curline->fk_product;
				// si on force la quantité toujours à 1
				if (!empty($conf->global->SUPPLIERPRICR_QTYONLYONE)) {
					$qty=1;
					$subprice=($curline->subprice);
				} else {
					$qty=$curline->qty;
					$subprice=($curline->subprice * $curline->qty);
				}

				$remise_percent=$curline->remise_percent;
				$tva_tx=$curline->tva_tx;
				if ($curline->ref_fourn != "" )
					$ref_fourn=$curline->ref_fourn;
				else
					$ref_fourn=$curline->ref;

				// on vérifie que le prix fournisseur n'est pas déjà existant
				$sql = "SELECT rowid, remise_percent, price, tva_tx from ".MAIN_DB_PREFIX."product_fournisseur_price ";
				$sql .= " WHERE fk_soc=".$fk_soc;
				$sql .= " AND ref_fourn='".$ref_fourn."'";
				$sql .= " AND quantity=".$qty;
				//print $sql;
				$resql = $this->db->query($sql);
				if ($resql) {

					$num = $this->db->num_rows($resql);
					// si on a trouvé un prix
					if ($num  > 0) {
						$obj = $this->db->fetch_object($resql);
						// on met à jour le prix si un element différent
						if ($obj->remise_percent != $remise_percent || $obj->price != $subprice || $obj->tva_tx != $tva_tx) {
							$sql ="UPDATE ".MAIN_DB_PREFIX."product_fournisseur_price ";
							$sql.=" SET remise_percent =".price2num($remise_percent);
							$sql.=" , datec = now()";
							$sql.=" , price =".price2num($subprice);
							$sql.=" , tva_tx =".price2num($tva_tx);
							if ($qty != 1 && $qty != 0)
								$sql.= " , unitprice =".price2num($subprice / $qty);
							else
								$sql.= " , unitprice =".price2num($subprice);
							$sql.= " WHERE rowid=".$obj->rowid;

							$result=$this->db->query($sql);

							if ($result) {
								$nbcreate++;
								$this->db->free($result);
							} else {
								$this->error=$this->db->error();
								return -1;
							}
						} else {
							// pas de changement, le prix n'est pas à mettre à jour
							// met-on le prix de création afin de connaitre la dernière actualisation?
						}
					} else {
						// nouveau prix
						$sql ="INSERT INTO ".MAIN_DB_PREFIX."product_fournisseur_price";
						$sql.= " (entity, datec, fk_product, fk_soc, ref_fourn, price,";
						$sql.="   remise_percent, quantity, unitprice, tva_tx, fk_user) ";
						$sql.=" VALUES ";
						$sql.= " ( ".$conf->entity;
						$sql.= " , now() ";
						$sql.= " , ".$fk_product;
						$sql.= " , ".$fk_soc;
						$sql.= " , '".$ref_fourn."'";
						$sql.= " , ".price2num($subprice);
						$sql.= " , ".price2num($remise_percent);
						$sql.= " , ".price2num($qty);
						if ($qty != 1 && $qty != 0)
							$sql.= " , ".price2num($subprice / $qty);
						else
							$sql.= " , ".price2num($subprice);
						$sql.= " , ".price2num($tva_tx);
						$sql.= " , ".$user->id;
						$sql.= " )";

						$result=$this->db->query($sql);

						if ($result) {
							$nbcreate++;
							$this->db->free($result);
						} else {
							$this->error=$this->db->error();
							return -2;
						}
					}

				} else {
					$this->error=$this->db->error();
					return -3;
				}
			}
		}
		return $nbcreate;
	}
}
