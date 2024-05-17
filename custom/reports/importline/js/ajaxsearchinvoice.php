<?php

/**
 * Written by INOVEA-CONSEIL <info@inovea-conseil.com>
 * Copyright 2017 
 * */
$res = 0;
if (!$res && file_exists("../main.inc.php"))
    $res = @include '../main.inc.php';     // to work if your module directory is into dolibarr root htdocs directory
if (!$res && file_exists("../../main.inc.php"))
    $res = @include '../../main.inc.php';   // to work if your module directory is into a subdir of root htdocs directory
if (!$res && file_exists("../../../main.inc.php"))
    $res = @include '../../../main.inc.php';   // to work if your module directory is into a subdir of root htdocs directory
if (!$res && file_exists("../../../dolibarr/htdocs/main.inc.php"))
    $res = @include '../../../dolibarr/htdocs/main.inc.php';     // Used on dev env only
if (!$res && file_exists("../../../../dolibarr/htdocs/main.inc.php"))
    $res = @include '../../../../dolibarr/htdocs/main.inc.php';   // Used on dev env only
if (!$res)
    die("Include of main fails");
global $langs, $conf;
$langs->load('importline@importline');
require_once(DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php');

global $user;

$ga = array();

$sql = "SELECT s.rowid, s.nom as name, s.client,";
if ((int) DOL_VERSION >= 10) {
    $sql .= " f.rowid as factureid, f.fk_statut, f.total_ht, f.ref, f.remise, ";
} else {
    $sql .= " f.rowid as factureid, f.fk_statut, f.total_ht, f.facnumber, f.remise, ";
}

$sql .= " f.datep as dp, f.fin_validite as datelimite";
if (!$user->rights->societe->client->voir && !$socid)
    $sql .= ", sc.fk_soc, sc.fk_user";
$sql .= " FROM " . MAIN_DB_PREFIX . "societe as s, " . MAIN_DB_PREFIX . "facture as f, " . MAIN_DB_PREFIX . "c_propalst as c";
if (!$user->rights->societe->client->voir && !$socid)
    $sql .= ", " . MAIN_DB_PREFIX . "societe_commerciaux as sc";
$sql .= " WHERE f.entity IN (" . getEntity('facture') . ")";
$sql .= " AND f.fk_soc = s.rowid";
$sql .= " AND f.fk_statut = c.id";
if (!$user->rights->societe->client->voir && !$socid) { //restriction
    $sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = " . $user->id;
}
if ($socid)
    $sql .= " AND s.rowid = " . $socid;
    $sql .= " AND f.fk_statut != " . self::STATUS_DRAFT;
if ($notcurrentuser > 0)
    $sql .= " AND f.fk_user_author <> " . $user->id;
if($_POST['text']){
    if ((int) DOL_VERSION >= 10) {
        $sql .= " AND f.ref LIKE '%'".$_POST['text']."'%' ";
    } else {
        $sql .= " AND f.facnumber LIKE '%'".$_POST['text']."'%' ";
    }
}
$sql .= $this->db->order($sortfield, $sortorder);
$sql .= $this->db->plimit($limit, $offset);

$result = $this->db->query($sql);
if ($result) {
    $num = $this->db->num_rows($result);
    if ($num) {
        $i = 0;
        while ($i < $num) {
            $obj = $this->db->fetch_object($result);

            if ($shortlist == 1) {
                if ((int) DOL_VERSION >= 10) {
                    $ga[$obj->facid] = $obj->ref;
                } else {
                    $ga[$obj->facid] = $obj->facnumber;
                }
            } else if ($shortlist == 2) {
                if ((int) DOL_VERSION >= 10) {
                    $ga[$obj->facid] = $obj->ref . ' (' . $obj->name . ')';
                } else {
                    $ga[$obj->facid] = $obj->facnumber . ' (' . $obj->name . ')';
                }
            } else {
                $ga[$i]['id'] = $obj->facid;
                if ((int) DOL_VERSION >= 10) {
                    $ga[$i]['ref'] = $obj->ref;
                } else {
                    $ga[$i]['ref'] = $obj->facnumber;
                }
                $ga[$i]['name'] = $obj->name;
            }

            $i++;
        }
    }
    
    return json_encode($ga);
} else {
    dol_print_error($this->db);
    return -1;
}
    