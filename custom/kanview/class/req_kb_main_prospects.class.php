<?php

/*
 * Copyright (C) 2017-2019-2020 ProgSI (contact@progsi.ma)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file kanview/class/abstract_my_table.class.php
 * \ingroup kanview
 * \brief This file is a CRUD class file (Create/Read/Update/Delete)
 * Put some comments here
 */
// Protection (if external user for example)
if (!($conf->kanview->enabled)) {
  accessforbidden('', 0, 0);
  exit();
}

require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';

// $build = str_replace('.', '', KANVIEW_VERSION);

/**
 * Class ReqKbMainProspects
 *
 * Put some comments here
 */
class ReqKbMainProspects extends CommonObject {

  /**
   *
   * @var string module auquel appartient cet objet, ne doit pas être modifié
   */
  public $modulepart = 'kanview';

  /**
   * nom du champ id (ex.: 'rowid')
   */
  public $idfield = 'rowid';

  /**
   * nom du champ Ref (ex.
   * : 'ref', 'code')
   */
  public $reffield = 'rowid';

  /**
   * nbre total des enregistrements
   *
   */
  public $nbtotalofrecords = 0; // voir fetchAll()

  /**/
  public $container = 'kanview';

  /**
   *
   * @var ReqKbMainProspectsLine[] Lines
   */
  public $lines = array();
  // public $prop1;

  public $rowid;
  public $id;
  public $nom;
  public $name_alias;
  public $entity;
  public $ref_ext;
  // public $ref_int;
  public $statut;
  public $parent;
  public $tms;
  public $datec;
  public $status;
  public $code_client;
  public $code_fournisseur;
  public $code_compta;
  public $code_compta_fournisseur;
  public $address;
  public $zip;
  public $town;
  public $fk_departement;
  public $fk_pays;
  public $fk_account;
  public $phone;
  public $fax;
  public $url;
  public $email;
  public $fk_effectif;
  public $fk_typent;
  public $fk_forme_juridique;
  public $fk_currency;
  public $siren;
  public $siret;
  public $ape;
  public $idprof4;
  public $idprof5;
  public $idprof6;
  public $tva_intra;
  public $capital;
  public $fk_stcomm;
  public $note_private;
  public $note_public;
  public $model_pdf;
  public $prefix_comm;
  public $client;
  public $fournisseur;
  public $supplier_account;
  public $fk_prospectlevel;
  public $fk_incoterms;
  public $location_incoterms;
  public $customer_bad;
  public $customer_rate;
  public $supplier_rate;
  public $fk_user_creat;
  public $fk_user_modif;
  public $remise_client;
  public $mode_reglement;
  public $cond_reglement;
  public $mode_reglement_supplier;
  public $cond_reglement_supplier;
  public $fk_shipping_method;
  public $tva_assuj;
  public $barcode;
  public $fk_barcode_type;
  public $price_level;
  public $outstanding_limit;
  public $default_lang;
  public $logo;
  public $code_client_nom;
  public $stcomm_code;
  public $stcomm_libelle;
  public $prospectlevel_label;
  public $pays_code;
  public $pays_code_iso;
  public $pays_label;
  public $effectif_code;
  public $effectif_libelle;
  public $typent_code;
  public $typent_libelle;

  // sql where params declarations


  /**
   */
  // -------------------------------------------- __construct()

  /**
   * Constructor
   *
   * @param DoliDb $db
   *        	Database handler
   */
  public function __construct(DoliDB $db) {
    global $langs;
    $this->db = $db;

    // désactivation temporaire du mode ONLY_FULL_GROUP_BY (qui interdit l'utilisation du GROUP BY sans tous les champs de la requête) 
    if ($this->db->type == 'mysql' || $this->db->type == 'mysqli') {
      $sqlTmp1 = "SET SESSION sql_mode=(SELECT REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY,',''));";
      $sqlTmp2 = "SET SESSION sql_mode=(SELECT REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY,',''));";
      $db->query($sqlTmp1);
      $db->query($sqlTmp2);
    }
    return 1;
  }

  // --------------------------------------------------- init()

  /**
   * Initialise object with example values
   *
   * @return void
   */
  public function init() {
    // $this->prop1 = '';

    $this->rowid                   = '';
    $this->id                      = '';
    $this->nom                     = '';
    $this->name_alias              = '';
    $this->entity                  = '';
    $this->ref_ext                 = '';
    // $this->ref_int                 = '';
    $this->statut                  = '';
    $this->parent                  = '';
    $this->tms                     = '';
    $this->datec                   = '';
    $this->status                  = '';
    $this->code_client             = '';
    $this->code_fournisseur        = '';
    $this->code_compta             = '';
    $this->code_compta_fournisseur = '';
    $this->address                 = '';
    $this->zip                     = '';
    $this->town                    = '';
    $this->fk_departement          = '';
    $this->fk_pays                 = '';
    $this->fk_account              = '';
    $this->phone                   = '';
    $this->fax                     = '';
    $this->url                     = '';
    $this->email                   = '';
    $this->fk_effectif             = '';
    $this->fk_typent               = '';
    $this->fk_forme_juridique      = '';
    $this->fk_currency             = '';
    $this->siren                   = '';
    $this->siret                   = '';
    $this->ape                     = '';
    $this->idprof4                 = '';
    $this->idprof5                 = '';
    $this->idprof6                 = '';
    $this->tva_intra               = '';
    $this->capital                 = '';
    $this->fk_stcomm               = '';
    $this->note_private            = '';
    $this->note_public             = '';
    $this->model_pdf               = '';
    $this->prefix_comm             = '';
    $this->client                  = '';
    $this->fournisseur             = '';
    $this->supplier_account        = '';
    $this->fk_prospectlevel        = '';
    $this->fk_incoterms            = '';
    $this->location_incoterms      = '';
    $this->customer_bad            = '';
    $this->customer_rate           = '';
    $this->supplier_rate           = '';
    $this->fk_user_creat           = '';
    $this->fk_user_modif           = '';
    $this->remise_client           = '';
    $this->mode_reglement          = '';
    $this->cond_reglement          = '';
    $this->mode_reglement_supplier = '';
    $this->cond_reglement_supplier = '';
    $this->fk_shipping_method      = '';
    $this->tva_assuj               = '';
    $this->barcode                 = '';
    $this->fk_barcode_type         = '';
    $this->price_level             = '';
    $this->outstanding_limit       = '';
    $this->default_lang            = '';
    $this->logo                    = '';
    $this->code_client_nom         = '';
    $this->stcomm_code             = '';
    $this->stcomm_libelle          = '';
    $this->prospectlevel_label     = '';
    $this->pays_code               = '';
    $this->pays_code_iso           = '';
    $this->pays_label              = '';
    $this->effectif_code           = '';
    $this->effectif_libelle        = '';
    $this->typent_code             = '';
    $this->typent_libelle          = '';
  }

  // ------------------------------------------------------- fetchOne()

  /**
   * Load first object in memory from the database
   *
   * @return int <0 if KO, 0 if not found, >0 if OK
   */
  public function fetchOne($_ORDERBY = '', $_isNewOrderBy = true, $_WHERE = '', $_isNewWhere = true, $_HAVING = '', $_isNewHaving = true) {
    dol_syslog(__METHOD__, LOG_DEBUG);

    $sql = $this->getCodeSQL($_ORDERBY, $_isNewOrderBy, $_WHERE, $_isNewWhere, $_HAVING, $_isNewHaving);

    // var_dump($sql);

    $resql = $this->db->query($sql);
    if ($resql) {
      $numrows = $this->db->num_rows($resql);
      if ($numrows) {
        $obj = $this->db->fetch_object($resql);

        // $this->id = $obj->rowid;
        $this->copyObject($obj, $this);
      }

      $this->db->free($resql);

      if ($numrows) {
        return 1;
      }
      else {
        return 0;
      }
    }
    else {
      $this->errors[] = 'Error ' . $this->db->lasterror();
      dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

      return - 1;
    }
  }

  // ------------------------------------------------------- fetchOneByField()

  /**
   * Load first object in memory from the database by field
   *
   * @return int <0 if KO, 0 if not found, >0 if OK
   */
  public function fetchOneByField($fieldName, $fieldValue) {
    dol_syslog(__METHOD__, LOG_DEBUG);

    $sql          = $this->getCodeSQL($ORDERBY      = '', $isNewOrderBy = true, $WHERE        = $fieldName . " = '" . $fieldValue . "' ", $isNewWhere   = true, $HAVING       = '', $isNewHaving  = true);

    // var_dump($sql);

    $resql = $this->db->query($sql);
    if ($resql) {
      $numrows = $this->db->num_rows($resql);
      if ($numrows) {
        $obj = $this->db->fetch_object($resql);

        // $this->id = $obj->rowid;
        $this->copyObject($obj, $this);
      }

      $this->db->free($resql);

      if ($numrows) {
        return 1;
      }
      else {
        return 0;
      }
    }
    else {
      $this->errors[] = 'Error ' . $this->db->lasterror();
      dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

      return - 1;
    }
  }

  // ------------------------------------------------------- fetchById()

  /**
   * Load first object in memory from the database by Id
   *
   * @return int <0 if KO, 0 if not found, >0 if OK
   */
  public function fetchById($rowid) {
    dol_syslog(__METHOD__, LOG_DEBUG);

    $idField = 'rowid';

    if (empty($idField))
      $idField = 'rowid';

    $sql          = $this->getCodeSQL($ORDERBY      = '', $isNewOrderBy = true, $WHERE        = $idField . ' = ' . intval($rowid), $isNewWhere   = true, $HAVING       = '', $isNewHaving  = true);

    // var_dump($sql);

    $resql = $this->db->query($sql);
    if ($resql) {
      $numrows = $this->db->num_rows($resql);
      if ($numrows) {
        $obj = $this->db->fetch_object($resql);

        // $this->id = $obj->rowid;
        $this->copyObject($obj, $this);
      }

      $this->db->free($resql);

      if ($numrows) {
        return 1;
      }
      else {
        return 0;
      }
    }
    else {
      $this->errors[] = 'Error ' . $this->db->lasterror();
      dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

      return - 1;
    }
  }

  // ------------------------------------------------------- fetchByRef()

  /**
   * Load first object in memory from the database by Ref
   *
   * @return int <0 if KO, 0 if not found, >0 if OK
   */
  public function fetchByRef($ref) {
    dol_syslog(__METHOD__, LOG_DEBUG);

    $refField = 'rowid'; // 'ref_int';

    if (empty($refField))
      $refField = 'ref';

    $sql          = $this->getCodeSQL($ORDERBY      = '', $isNewOrderBy = true, $WHERE        = $refField . " = '" . $ref . "' ", $isNewWhere   = true, $HAVING       = '', $isNewHaving  = true);

    // var_dump($sql);

    $resql = $this->db->query($sql);
    if ($resql) {
      $numrows = $this->db->num_rows($resql);
      if ($numrows) {
        $obj = $this->db->fetch_object($resql);

        // $this->id = $obj->rowid;
        $this->copyObject($obj, $this);
      }

      $this->db->free($resql);

      if ($numrows) {
        return 1;
      }
      else {
        return 0;
      }
    }
    else {
      $this->errors[] = 'Error ' . $this->db->lasterror();
      dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

      return - 1;
    }
  }

  // ---------------------------------------------- fetchAll()

  /**
   * Load object in memory from the database
   *
   * @param int $LIMIT
   *        	Clause LIMIT
   * @param int $OFFSET
   *        	Clause OFFSET
   * @param string $ORDERBY
   *        	Clause ORDER BY (sans le "ORDER BY", et même syntax que SQL)
   *        	Ce paramètre, s'il est non vide, est prioritaire sur la clause ORDER BY initialement fourni par la requete de la classe
   * @return int <0 if KO, number of records if OK
   */
  public function fetchAll($LIMIT = 0, $OFFSET = 0, $ORDERBY = '', $isNewOrderBy = true, $WHERE = '', $isNewWhere = true, $HAVING = '', $isNewHaving = true) {
    dol_syslog(__METHOD__, LOG_DEBUG);

    global $conf;

    $sql = $this->getCodeSQL($ORDERBY, $isNewOrderBy, $WHERE, $isNewWhere, $HAVING, $isNewHaving);

    // nbre total des enregistrements (avant d'appliquer limit/offset)
    if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
      $result                 = $this->db->query($sql);
      if ($result)
        $this->nbtotalofrecords = $this->db->num_rows($result);
    }

    if (!empty($LIMIT)) {
      $sql .= ' ' . $this->db->plimit($LIMIT, $OFFSET);
    }

    $this->lines = array();

    $resql = $this->db->query($sql);
    if ($resql) {
      $num = $this->db->num_rows($resql);

      while ($obj = $this->db->fetch_object($resql)) {
        $line = new ReqKbMainProspectsLine();

        // $line->id = $obj->rowid;
        $this->copyObject($obj, $line);

        $this->lines[] = $line;
      }

      $this->db->free($resql);

      return $num;
    }
    else {
      $this->errors[] = 'Error ' . $this->db->lasterror();
      dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

      return - 1;
    }
  }

  // -------------------------------------------------- copyObject()

  /**
   * copie un objet du même type que celui en cours vers un autre objet du meme type sauf l'id
   *
   * @param $objSource objet
   *        	du même type à copier
   */
  public function copyObject($objSource, $objDest) {

    // $objDest->prop1 = $objSource->prop1;

    $objDest->rowid                   = $objSource->rowid;
    $objDest->id                      = $objSource->id;
    $objDest->nom                     = $objSource->nom;
    $objDest->name_alias              = $objSource->name_alias;
    $objDest->entity                  = $objSource->entity;
    $objDest->ref_ext                 = $objSource->ref_ext;
    // $objDest->ref_int                 = $objSource->ref_int;
    $objDest->statut                  = $objSource->statut;
    $objDest->parent                  = $objSource->parent;
    $objDest->tms                     = $this->db->jdate($objSource->tms);
    $objDest->datec                   = $this->db->jdate($objSource->datec);
    $objDest->status                  = $objSource->status;
    $objDest->code_client             = $objSource->code_client;
    $objDest->code_fournisseur        = $objSource->code_fournisseur;
    $objDest->code_compta             = $objSource->code_compta;
    $objDest->code_compta_fournisseur = $objSource->code_compta_fournisseur;
    $objDest->address                 = $objSource->address;
    $objDest->zip                     = $objSource->zip;
    $objDest->town                    = $objSource->town;
    $objDest->fk_departement          = $objSource->fk_departement;
    $objDest->fk_pays                 = $objSource->fk_pays;
    $objDest->fk_account              = $objSource->fk_account;
    $objDest->phone                   = $objSource->phone;
    $objDest->fax                     = $objSource->fax;
    $objDest->url                     = $objSource->url;
    $objDest->email                   = $objSource->email;
    $objDest->fk_effectif             = $objSource->fk_effectif;
    $objDest->fk_typent               = $objSource->fk_typent;
    $objDest->fk_forme_juridique      = $objSource->fk_forme_juridique;
    $objDest->fk_currency             = $objSource->fk_currency;
    $objDest->siren                   = $objSource->siren;
    $objDest->siret                   = $objSource->siret;
    $objDest->ape                     = $objSource->ape;
    $objDest->idprof4                 = $objSource->idprof4;
    $objDest->idprof5                 = $objSource->idprof5;
    $objDest->idprof6                 = $objSource->idprof6;
    $objDest->tva_intra               = $objSource->tva_intra;
    $objDest->capital                 = $objSource->capital;
    $objDest->fk_stcomm               = $objSource->fk_stcomm;
    $objDest->note_private            = $objSource->note_private;
    $objDest->note_public             = $objSource->note_public;
    $objDest->model_pdf               = $objSource->model_pdf;
    $objDest->prefix_comm             = $objSource->prefix_comm;
    $objDest->client                  = $objSource->client;
    $objDest->fournisseur             = $objSource->fournisseur;
    $objDest->supplier_account        = $objSource->supplier_account;
    $objDest->fk_prospectlevel        = $objSource->fk_prospectlevel;
    $objDest->fk_incoterms            = $objSource->fk_incoterms;
    $objDest->location_incoterms      = $objSource->location_incoterms;
    $objDest->customer_bad            = $objSource->customer_bad;
    $objDest->customer_rate           = $objSource->customer_rate;
    $objDest->supplier_rate           = $objSource->supplier_rate;
    $objDest->fk_user_creat           = $objSource->fk_user_creat;
    $objDest->fk_user_modif           = $objSource->fk_user_modif;
    $objDest->remise_client           = $objSource->remise_client;
    $objDest->mode_reglement          = $objSource->mode_reglement;
    $objDest->cond_reglement          = $objSource->cond_reglement;
    $objDest->mode_reglement_supplier = $objSource->mode_reglement_supplier;
    $objDest->cond_reglement_supplier = $objSource->cond_reglement_supplier;
    $objDest->fk_shipping_method      = $objSource->fk_shipping_method;
    $objDest->tva_assuj               = $objSource->tva_assuj;
    $objDest->barcode                 = $objSource->barcode;
    $objDest->fk_barcode_type         = $objSource->fk_barcode_type;
    $objDest->price_level             = $objSource->price_level;
    $objDest->outstanding_limit       = $objSource->outstanding_limit;
    $objDest->default_lang            = $objSource->default_lang;
    $objDest->logo                    = $objSource->logo;
    $objDest->code_client_nom         = $objSource->code_client_nom;
    $objDest->stcomm_code             = $objSource->stcomm_code;
    $objDest->stcomm_libelle          = $objSource->stcomm_libelle;
    $objDest->prospectlevel_label     = $objSource->prospectlevel_label;
    $objDest->pays_code               = $objSource->pays_code;
    $objDest->pays_code_iso           = $objSource->pays_code_iso;
    $objDest->pays_label              = $objSource->pays_label;
    $objDest->effectif_code           = $objSource->effectif_code;
    $objDest->effectif_libelle        = $objSource->effectif_libelle;
    $objDest->typent_code             = $objSource->typent_code;
    $objDest->typent_libelle          = $objSource->typent_libelle;
    // $objDest->datec = $this->db->jdate($objSource->datec);
    // $objDest->tms = $this->db->jdate($objSource->tms);
  }

  // ------------------------------------------------ getCodeSQL()

  /**
   * renvoie la clause FROM sans le FROM
   */
  public function getCodeSQL($_ORDERBY = '', $_isNewOrderBy = true, $_WHERE = '', $_isNewWhere = true, $_HAVING = '', $_isNewHaving = true) {
    global $user, $conf;

    // si l'utilisateur n'a le droit de voir que les tiers dont il est commercial
    // on cherche les ids de ces tiers
    // KANVIEW_PROSPECTS_SHOW_ALL est un paramètre caché permettant d'afficher tous les prospects 
    //                            quelque soit l'état de la permission $user->rights->societe->client->voir
    $tiersPermisIds      = '-1';
    $tiersPermisIdsArray = array();
    if (empty($conf->global->KANVIEW_PROSPECTS_SHOW_ALL) && !$user->rights->societe->client->voir) {
      $sqlTiersPermis    = "SELECT fk_soc "
          . " FROM " . MAIN_DB_PREFIX . "societe_commerciaux "
          . " WHERE fk_user = " . intval($user->id);
      $resSqlTiersPermis = $this->db->query($sqlTiersPermis);
      if ($resSqlTiersPermis) {
        while ($objTmp = $this->db->fetch_object($resSqlTiersPermis)) {
          $tiersPermisIdsArray[] = $objTmp->fk_soc;
        }
        $tiersPermisIds = implode(',', $tiersPermisIdsArray);
        if (empty($tiersPermisIds))
          $tiersPermisIds = '-1';
        // var_dump($tiersPermisIds);
      }
    }

    $sql = '';

    $sql = "SELECT ";

    $fields_for_select = array(
        "t.rowid AS rowid",
        "t.rowid AS id",
        "t.nom AS nom",
        "t.name_alias AS name_alias",
        "t.entity AS entity",
        "t.ref_ext AS ref_ext",
        // "t.ref_int AS ref_int",
        "t.statut AS statut",
        "t.parent AS parent",
        "t.tms AS tms",
        "t.datec AS datec",
        "t.status AS status",
        "t.code_client AS code_client",
        "t.code_fournisseur AS code_fournisseur",
        "t.code_compta AS code_compta",
        "t.code_compta_fournisseur AS code_compta_fournisseur",
        "t.address AS address",
        "t.zip AS zip",
        "t.town AS town",
        "t.fk_departement AS fk_departement",
        "t.fk_pays AS fk_pays",
        "t.fk_account AS fk_account",
        "t.phone AS phone",
        "t.fax AS fax",
        "t.url AS url",
        "t.email AS email",
        "t.fk_effectif AS fk_effectif",
        "t.fk_typent AS fk_typent",
        "t.fk_forme_juridique AS fk_forme_juridique",
        "t.fk_currency AS fk_currency",
        "t.siren AS siren",
        "t.siret AS siret",
        "t.ape AS ape",
        "t.idprof4 AS idprof4",
        "t.idprof5 AS idprof5",
        "t.idprof6 AS idprof6",
        "t.tva_intra AS tva_intra",
        "t.capital AS capital",
        "t.fk_stcomm AS fk_stcomm",
        "t.note_private AS note_private",
        "t.note_public AS note_public",
        "t.model_pdf AS model_pdf",
        "t.prefix_comm AS prefix_comm",
        "t.client AS client",
        "t.fournisseur AS fournisseur",
        "t.supplier_account AS supplier_account",
        "t.fk_prospectlevel AS fk_prospectlevel",
        "t.fk_incoterms AS fk_incoterms",
        "t.location_incoterms AS location_incoterms",
        "t.customer_bad AS customer_bad",
        "t.customer_rate AS customer_rate",
        "t.supplier_rate AS supplier_rate",
        "t.fk_user_creat AS fk_user_creat",
        "t.fk_user_modif AS fk_user_modif",
        "t.remise_client AS remise_client",
        "t.mode_reglement AS mode_reglement",
        "t.cond_reglement AS cond_reglement",
        "t.mode_reglement_supplier AS mode_reglement_supplier",
        "t.cond_reglement_supplier AS cond_reglement_supplier",
        "t.fk_shipping_method AS fk_shipping_method",
        "t.tva_assuj AS tva_assuj",
        "t.barcode AS barcode",
        "t.fk_barcode_type AS fk_barcode_type",
        "t.price_level AS price_level",
        "t.outstanding_limit AS outstanding_limit",
        "t.default_lang AS default_lang",
        "t.logo AS logo",
        "CONCAT(t.code_client, '-', t.nom) AS code_client_nom",
        MAIN_DB_PREFIX . "c_stcomm.code AS stcomm_code",
        MAIN_DB_PREFIX . "c_stcomm.libelle AS stcomm_libelle",
        MAIN_DB_PREFIX . "c_prospectlevel.label AS prospectlevel_label",
        MAIN_DB_PREFIX . "c_country.code AS pays_code",
        MAIN_DB_PREFIX . "c_country.code_iso AS pays_code_iso",
        MAIN_DB_PREFIX . "c_country.label AS pays_label",
        MAIN_DB_PREFIX . "c_effectif.code AS effectif_code",
        MAIN_DB_PREFIX . "c_effectif.libelle AS effectif_libelle",
        MAIN_DB_PREFIX . "c_typent.code AS typent_code",
        MAIN_DB_PREFIX . "c_typent.libelle AS typent_libelle",
    );

    $fields_for_group_by = array(
        "t.rowid",
        "t.rowid",
        "t.nom",
        "t.name_alias",
        "t.entity",
        "t.ref_ext",
        // "t.ref_int",
        "t.statut",
        "t.parent",
        "t.tms",
        "t.datec",
        "t.status",
        "t.code_client",
        "t.code_fournisseur",
        "t.code_compta",
        "t.code_compta_fournisseur",
        "t.address",
        "t.zip",
        "t.town",
        "t.fk_departement",
        "t.fk_pays",
        "t.fk_account",
        "t.phone",
        "t.fax",
        "t.url",
        "t.email",
        "t.fk_effectif",
        "t.fk_typent",
        "t.fk_forme_juridique",
        "t.fk_currency",
        "t.siren",
        "t.siret",
        "t.ape",
        "t.idprof4",
        "t.idprof5",
        "t.idprof6",
        "t.tva_intra",
        "t.capital",
        "t.fk_stcomm",
        "t.note_private",
        "t.note_public",
        "t.model_pdf",
        "t.prefix_comm",
        "t.client",
        "t.fournisseur",
        "t.supplier_account",
        "t.fk_prospectlevel",
        "t.fk_incoterms",
        "t.location_incoterms",
        "t.customer_bad",
        "t.customer_rate",
        "t.supplier_rate",
        "t.fk_user_creat",
        "t.fk_user_modif",
        "t.remise_client",
        "t.mode_reglement",
        "t.cond_reglement",
        "t.mode_reglement_supplier",
        "t.cond_reglement_supplier",
        "t.fk_shipping_method",
        "t.tva_assuj",
        "t.barcode",
        "t.fk_barcode_type",
        "t.price_level",
        "t.outstanding_limit",
        "t.default_lang",
        "t.logo",
        "code_client_nom",
        MAIN_DB_PREFIX . "c_stcomm.code",
        MAIN_DB_PREFIX . "c_stcomm.libelle",
        MAIN_DB_PREFIX . "c_prospectlevel.label",
        MAIN_DB_PREFIX . "c_country.code",
        MAIN_DB_PREFIX . "c_country.code_iso",
        MAIN_DB_PREFIX . "c_country.label",
        MAIN_DB_PREFIX . "c_effectif.code",
        MAIN_DB_PREFIX . "c_effectif.libelle",
        MAIN_DB_PREFIX . "c_typent.code",
        MAIN_DB_PREFIX . "c_typent.libelle",
    );

//    $sql .= " " . "t.rowid AS rowid,";
//    $sql .= " " . "t.rowid AS id,";
//    $sql .= " " . "t.nom AS nom,";
//    $sql .= " " . "t.name_alias AS name_alias,";
//    $sql .= " " . "t.entity AS entity,";
//    $sql .= " " . "t.ref_ext AS ref_ext,";
//    // $sql .= " " . "t.ref_int AS ref_int,";
//    $sql .= " " . "t.statut AS statut,";
//    $sql .= " " . "t.parent AS parent,";
//    $sql .= " " . "t.tms AS tms,";
//    $sql .= " " . "t.datec AS datec,";
//    $sql .= " " . "t.status AS status,";
//    $sql .= " " . "t.code_client AS code_client,";
//    $sql .= " " . "t.code_fournisseur AS code_fournisseur,";
//    $sql .= " " . "t.code_compta AS code_compta,";
//    $sql .= " " . "t.code_compta_fournisseur AS code_compta_fournisseur,";
//    $sql .= " " . "t.address AS address,";
//    $sql .= " " . "t.zip AS zip,";
//    $sql .= " " . "t.town AS town,";
//    $sql .= " " . "t.fk_departement AS fk_departement,";
//    $sql .= " " . "t.fk_pays AS fk_pays,";
//    $sql .= " " . "t.fk_account AS fk_account,";
//    $sql .= " " . "t.phone AS phone,";
//    $sql .= " " . "t.fax AS fax,";
//    $sql .= " " . "t.url AS url,";
//    $sql .= " " . "t.email AS email,";
//    $sql .= " " . "t.fk_effectif AS fk_effectif,";
//    $sql .= " " . "t.fk_typent AS fk_typent,";
//    $sql .= " " . "t.fk_forme_juridique AS fk_forme_juridique,";
//    $sql .= " " . "t.fk_currency AS fk_currency,";
//    $sql .= " " . "t.siren AS siren,";
//    $sql .= " " . "t.siret AS siret,";
//    $sql .= " " . "t.ape AS ape,";
//    $sql .= " " . "t.idprof4 AS idprof4,";
//    $sql .= " " . "t.idprof5 AS idprof5,";
//    $sql .= " " . "t.idprof6 AS idprof6,";
//    $sql .= " " . "t.tva_intra AS tva_intra,";
//    $sql .= " " . "t.capital AS capital,";
//    $sql .= " " . "t.fk_stcomm AS fk_stcomm,";
//    $sql .= " " . "t.note_private AS note_private,";
//    $sql .= " " . "t.note_public AS note_public,";
//    $sql .= " " . "t.model_pdf AS model_pdf,";
//    $sql .= " " . "t.prefix_comm AS prefix_comm,";
//    $sql .= " " . "t.client AS client,";
//    $sql .= " " . "t.fournisseur AS fournisseur,";
//    $sql .= " " . "t.supplier_account AS supplier_account,";
//    $sql .= " " . "t.fk_prospectlevel AS fk_prospectlevel,";
//    $sql .= " " . "t.fk_incoterms AS fk_incoterms,";
//    $sql .= " " . "t.location_incoterms AS location_incoterms,";
//    $sql .= " " . "t.customer_bad AS customer_bad,";
//    $sql .= " " . "t.customer_rate AS customer_rate,";
//    $sql .= " " . "t.supplier_rate AS supplier_rate,";
//    $sql .= " " . "t.fk_user_creat AS fk_user_creat,";
//    $sql .= " " . "t.fk_user_modif AS fk_user_modif,";
//    $sql .= " " . "t.remise_client AS remise_client,";
//    $sql .= " " . "t.mode_reglement AS mode_reglement,";
//    $sql .= " " . "t.cond_reglement AS cond_reglement,";
//    $sql .= " " . "t.mode_reglement_supplier AS mode_reglement_supplier,";
//    $sql .= " " . "t.cond_reglement_supplier AS cond_reglement_supplier,";
//    $sql .= " " . "t.fk_shipping_method AS fk_shipping_method,";
//    $sql .= " " . "t.tva_assuj AS tva_assuj,";
//    $sql .= " " . "t.barcode AS barcode,";
//    $sql .= " " . "t.fk_barcode_type AS fk_barcode_type,";
//    $sql .= " " . "t.price_level AS price_level,";
//    $sql .= " " . "t.outstanding_limit AS outstanding_limit,";
//    $sql .= " " . "t.default_lang AS default_lang,";
//    $sql .= " " . "t.logo AS logo,";
//    $sql .= " " . "concat(t.code_client, '-', t.nom) AS code_client_nom,";
//    $sql .= " " . "" . MAIN_DB_PREFIX . "c_stcomm.code AS stcomm_code,";
//    $sql .= " " . "" . MAIN_DB_PREFIX . "c_stcomm.libelle AS stcomm_libelle,";
//    $sql .= " " . "" . MAIN_DB_PREFIX . "c_prospectlevel.label AS prospectlevel_label,";
//    $sql .= " " . "" . MAIN_DB_PREFIX . "c_country.code AS pays_code,";
//    $sql .= " " . "" . MAIN_DB_PREFIX . "c_country.code_iso AS pays_code_iso,";
//    $sql .= " " . "" . MAIN_DB_PREFIX . "c_country.label AS pays_label,";
//    $sql .= " " . "" . MAIN_DB_PREFIX . "c_effectif.code AS effectif_code,";
//    $sql .= " " . "" . MAIN_DB_PREFIX . "c_effectif.libelle AS effectif_libelle,";
//    $sql .= " " . "" . MAIN_DB_PREFIX . "c_typent.code AS typent_code,";
//    $sql .= " " . "" . MAIN_DB_PREFIX . "c_typent.libelle AS typent_libelle, ";

    $sql .= implode(",", $fields_for_select);
    $sql .= ", ";

    if ($this->db->type != 'pgsql') { // mysql
      $sql .= " CONCAT(',', GROUP_CONCAT(DISTINCT sc.fk_user), ',') AS rowids_commerciaux ";
    }
    // bugfix du double problème :
    //   - PostgreSQL n'accepte pas un argument integer pour STRING_AGG() 
    //   - et dolibarr ne convertit pas correctement l'expression GROUP_CONCAT(CAST())
    else {
      $sql .= " CONCAT(',', STRING_AGG(DISTINCT sc.fk_user::TEXT), ',') AS rowids_commerciaux ";
    }

    $sql .= " FROM ";
    $sql .= "" . MAIN_DB_PREFIX . "societe as t    "
        . " LEFT JOIN " . MAIN_DB_PREFIX . "c_stcomm on t.fk_stcomm = " . MAIN_DB_PREFIX . "c_stcomm.id  "  // sélectionne même les prospect avec statut inconnu
        . " LEFT JOIN " . MAIN_DB_PREFIX . "c_prospectlevel on t.fk_prospectlevel = " . MAIN_DB_PREFIX . "c_prospectlevel.code  "
        . " LEFT JOIN " . MAIN_DB_PREFIX . "c_country on t.fk_pays = " . MAIN_DB_PREFIX . "c_country.rowid  "
        . " LEFT JOIN " . MAIN_DB_PREFIX . "c_effectif on t.fk_effectif = " . MAIN_DB_PREFIX . "c_effectif.id  "
        . " LEFT JOIN " . MAIN_DB_PREFIX . "c_typent on t.fk_typent = " . MAIN_DB_PREFIX . "c_typent.id "
        . " LEFT JOIN " . MAIN_DB_PREFIX . "societe_commerciaux sc ON t.rowid = sc.fk_soc ";

    // --------- WHERE
    // $WHERE = " (t.client = 2 OR t.client = 3) "; // filtre géré par l'interface ($search_prospect_client)
    $WHERE = " 1 = 1 ";
    // $WHERE .= " AND t.fk_stcomm = " . MAIN_DB_PREFIX . "c_stcomm.id ";  // permet de ne pas sélectionner les prospects avec statut inconnu
    $WHERE .= " AND t.entity IN (" . getEntity('societe', 1) . ")";

    // si l'utilisateur n'a le droit de voir que les tiers dont il est commercial
    if (empty($conf->global->KANVIEW_PROSPECTS_SHOW_ALL) && !$user->rights->societe->client->voir) {
      $WHERE .= " AND t.rowid IN (" . $tiersPermisIds . ") ";
    }

    $WHERE = trim($WHERE);
    if (!empty($_WHERE)) {
      if ($_isNewWhere)
        $sql .= " WHERE " . $_WHERE; // on remplace le where actuel
      else {
        if ($WHERE !== "") {
          $sql .= " WHERE " . $WHERE . " AND (" . $_WHERE . ") "; // on ajoute le nouveau where
        }
        else {
          $sql .= " WHERE " . $_WHERE;
        }
      }
    }
    elseif ($WHERE !== "") {
      $sql .= " WHERE " . $WHERE;
    }

    // ----------- GROUP BY
    // $GROUPBY = "t.rowid";
    // $sql     .= " GROUP BY " . $GROUPBY;
    $sql .= " GROUP BY " . implode(",", $fields_for_group_by);

    // ----------- HAVING
    $HAVING  = "";
    $_HAVING = trim($_HAVING);
    if (!empty($_HAVING)) {
      if ($_isNewHaving)
        $sql .= " HAVING " . $_HAVING; // on remplace le having actuel
      else {
        if ($HAVING !== "") {
          $sql .= " HAVING " . $HAVING . " AND (" . $_HAVING . ") "; // on ajoute le nouveau having
        }
        else {
          $sql .= " HAVING " . $_HAVING;
        }
      }
    }
    elseif ($HAVING !== "") {
      $sql .= " HAVING " . $HAVING;
    }

    // ----------- ORDER BY
    $ORDERBY = "t.datec DESC, t.nom";
    $ORDERBY = trim($ORDERBY);
    if (!empty($_ORDERBY)) {
      if ($_isNewOrderBy)
        $sql .= " ORDER BY " . $_ORDERBY; // on remplace l'ancien $ORDERBY par le nouveau $_ORDERBY
      else
      if ($ORDERBY !== "")
        $sql .= " ORDER BY " . $ORDERBY . ", " . $_ORDERBY; // // on ajoute le nouveau $_ORDERBY à l'ancien $ORDERBY
      else
        $sql .= " ORDER BY " . $_ORDERBY;
    }
    elseif ($ORDERBY !== "") {
      $sql .= " ORDER BY " . $ORDERBY;
    }

    return $sql;
  }

  // ---------------------------------------------- initAsSpecimen()

  /**
   * Initialise object with example values
   * Id must be 0 if object instance is a specimen
   *
   * @return void
   */
  public function initAsSpecimen() {
    $this->id    = 0;
    $this->rowid = 0;

    // $this->prop1 = '';
    // __INIT_AS_SPECIMEN__
  }

  // -------------------------------------------------- generateDocument()

  /**
   * Create a document onto disk accordign to template module.
   *
   * @param string $modele
   *        	Force le mnodele a utiliser ('' to not force)
   * @param Translate $outputlangs
   *        	objet lang a utiliser pour traduction
   * @param int $hidedetails
   *        	Hide details of lines
   * @param int $hidedesc
   *        	Hide description
   * @param int $hideref
   *        	Hide ref
   * @return int 0 if KO, 1 if OK
   */
  public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0) {
    global $conf, $langs;

    $langs->load("kanview@kanview");

    // Positionne le modele sur le nom du modele a utiliser
    if (!dol_strlen($modele)) {
      if (!empty($conf->global->KANVIEW_ADDON_PDF)) {
        $modele = $conf->global->KANVIEW_ADDON_PDF;
      }
      else {
        $modele = 'generic';
      }
    }

    $modelpath = "core/modules/kanview/doc/";

    return $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref);
  }

  //
  // ------------------------------------- toArray()
  //
  // renvoie l'objet au format Array
  public function toArray() {
    $object_array = array();
    $fields_array = array('rowid', 'id', 'nom', 'name_alias', 'entity', 'ref_ext', 'statut', 'parent', 'tms', 'datec', 'status', 'code_client', 'code_fournisseur', 'code_compta', 'code_compta_fournisseur', 'address', 'zip', 'town', 'fk_departement', 'fk_pays', 'fk_account', 'phone', 'fax', 'url', 'email', 'fk_effectif', 'fk_typent', 'fk_forme_juridique', 'fk_currency', 'siren', 'siret', 'ape', 'idprof4', 'idprof5', 'idprof6', 'tva_intra', 'capital', 'fk_stcomm', 'note_private', 'note_public', 'model_pdf', 'prefix_comm', 'client', 'fournisseur', 'supplier_account', 'fk_prospectlevel', 'fk_incoterms', 'location_incoterms', 'customer_bad', 'customer_rate', 'supplier_rate', 'fk_user_creat', 'fk_user_modif', 'remise_client', 'mode_reglement', 'cond_reglement', 'mode_reglement_supplier', 'cond_reglement_supplier', 'fk_shipping_method', 'tva_assuj', 'barcode', 'fk_barcode_type', 'price_level', 'outstanding_limit', 'default_lang', 'logo', 'code_client_nom', 'stcomm_code', 'stcomm_libelle', 'prospectlevel_label', 'pays_code', 'pays_code_iso', 'pays_label', 'effectif_code', 'effectif_libelle', 'typent_code', 'typent_libelle',);

    $count = count($fields_array);

    for ($i = 0; $i < $count; $i++) {
      if (property_exists($this, $fields_array[$i])) {
        $object_array[$fields_array[$i]] = $this->{$fields_array[$i]};
      }
    }

    return $object_array;
  }

  //
  // ------------------------------------- toLinesArray()
  //
  // renvoie les lignes de l'objet au format Array
  public function toLinesArray() {
    $lines_array  = array();
    $fields_array = array('rowid', 'id', 'nom', 'name_alias', 'entity', 'ref_ext', 'statut', 'parent', 'tms', 'datec', 'status', 'code_client', 'code_fournisseur', 'code_compta', 'code_compta_fournisseur', 'address', 'zip', 'town', 'fk_departement', 'fk_pays', 'fk_account', 'phone', 'fax', 'url', 'email', 'fk_effectif', 'fk_typent', 'fk_forme_juridique', 'fk_currency', 'siren', 'siret', 'ape', 'idprof4', 'idprof5', 'idprof6', 'tva_intra', 'capital', 'fk_stcomm', 'note_private', 'note_public', 'model_pdf', 'prefix_comm', 'client', 'fournisseur', 'supplier_account', 'fk_prospectlevel', 'fk_incoterms', 'location_incoterms', 'customer_bad', 'customer_rate', 'supplier_rate', 'fk_user_creat', 'fk_user_modif', 'remise_client', 'mode_reglement', 'cond_reglement', 'mode_reglement_supplier', 'cond_reglement_supplier', 'fk_shipping_method', 'tva_assuj', 'barcode', 'fk_barcode_type', 'price_level', 'outstanding_limit', 'default_lang', 'logo', 'code_client_nom', 'stcomm_code', 'stcomm_libelle', 'prospectlevel_label', 'pays_code', 'pays_code_iso', 'pays_label', 'effectif_code', 'effectif_libelle', 'typent_code', 'typent_libelle',);

    $count = count($fields_array);

    $countlines = count($this->lines);
    for ($j = 0; $j < $countlines; $j++) {
      for ($i = 0; $i < $count; $i++) {
        if (property_exists($this->lines[$j], $fields_array[$i])) {
          $lines_array[$j][$fields_array[$i]] = $this->lines->{$fields_array[$i]};
        }
      }
    }

    return $lines_array;
  }
}

/**
 * Class ReqKbMainProspectsLine
 */
class ReqKbMainProspectsLine {

  /**
   *
   * @var string module auquel appartient cet objet, ne doit pas être modifié
   */
  public $modulepart = 'kanview';

  /**
   * nom du champ id (ex.: 'rowid')
   */
  public $idfield = 'rowid';

  /**
   * nom du champ Ref (ex.
   * : 'ref', 'code')
   */
  public $reffield = 'rowid'; // 'ref_int';
  // public $prop1;

  public $rowid;
  public $id;
  public $nom;
  public $name_alias;
  public $entity;
  public $ref_ext;
  // public $ref_int;
  public $statut;
  public $parent;
  public $tms;
  public $datec;
  public $status;
  public $code_client;
  public $code_fournisseur;
  public $code_compta;
  public $code_compta_fournisseur;
  public $address;
  public $zip;
  public $town;
  public $fk_departement;
  public $fk_pays;
  public $fk_account;
  public $phone;
  public $fax;
  public $url;
  public $email;
  public $fk_effectif;
  public $fk_typent;
  public $fk_forme_juridique;
  public $fk_currency;
  public $siren;
  public $siret;
  public $ape;
  public $idprof4;
  public $idprof5;
  public $idprof6;
  public $tva_intra;
  public $capital;
  public $fk_stcomm;
  public $note_private;
  public $note_public;
  public $model_pdf;
  public $prefix_comm;
  public $client;
  public $fournisseur;
  public $supplier_account;
  public $fk_prospectlevel;
  public $fk_incoterms;
  public $location_incoterms;
  public $customer_bad;
  public $customer_rate;
  public $supplier_rate;
  public $fk_user_creat;
  public $fk_user_modif;
  public $remise_client;
  public $mode_reglement;
  public $cond_reglement;
  public $mode_reglement_supplier;
  public $cond_reglement_supplier;
  public $fk_shipping_method;
  public $tva_assuj;
  public $barcode;
  public $fk_barcode_type;
  public $price_level;
  public $outstanding_limit;
  public $default_lang;
  public $logo;
  public $code_client_nom;
  public $stcomm_code;
  public $stcomm_libelle;
  public $prospectlevel_label;
  public $pays_code;
  public $pays_code_iso;
  public $pays_label;
  public $effectif_code;
  public $effectif_libelle;
  public $typent_code;
  public $typent_libelle;
}

// *******************************************************************************************************************
//                                                  FIN
// *******************************************************************************************************************






















