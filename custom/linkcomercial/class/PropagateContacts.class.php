<?php


class PropagateContacts extends CommonObject
{
	public static $elemento = 'supplier_proposal';
	public static $id1 = 0;

	public static function propagate($docorigen, $docdestino)
	{
		global $conf, $db;
		// Propagate contacts
		if (!empty($conf->global->MAIN_PROPAGATE_CONTACTS_FROM_ORIGIN))   // Get contact from origin object
		{
		    self::$id1 = $docorigen->id;
			$originforcontact = $docorigen->element;
			$originidforcontact = $docorigen->element;
			$sqlcontact = "SELECT ctc.code, ctc.source, ec.fk_socpeople FROM " . MAIN_DB_PREFIX . "element_contact as ec, " . MAIN_DB_PREFIX . "c_type_contact as ctc";
			$sqlcontact .= " WHERE element_id = " . $docorigen->id . " AND ec.fk_c_type_contact = ctc.rowid AND ctc.element = '" . $db->escape($originforcontact) . "'";

			$resqlcontact = $db->query($sqlcontact);
			if ($resqlcontact) {
				while ($objcontact = $db->fetch_object($resqlcontact)) {
					//print $objcontact->code.'-'.$objcontact->source.'-'.$objcontact->fk_socpeople."\n";
					(new PropagateContacts)->add_contacto($objcontact->fk_socpeople, $objcontact->code, $objcontact->source, 0, $docorigen, $docdestino); // May failed because of duplicate key or because code of contact type does not exists for new object
				}
			} else dol_print_error($resqlcontact);
		}

	}
	public static function propagateplano($iddocorigen, $iddocdestino)
	{
		global $conf, $db;
		// Propagate contacts
		if (!empty($conf->global->MAIN_PROPAGATE_CONTACTS_FROM_ORIGIN))   // Get contact from origin object
		{
			self::$id1 = $iddocorigen;
			$originforcontact = 'commande';
			$originidforcontact = 'commande';
			$sqlcontact = "SELECT ctc.code, ctc.source, ec.fk_socpeople FROM " . MAIN_DB_PREFIX . "element_contact as ec, " . MAIN_DB_PREFIX . "c_type_contact as ctc";
			$sqlcontact .= " WHERE element_id = " . $iddocorigen . " AND ec.fk_c_type_contact = ctc.rowid AND ctc.element = '" . $db->escape($originforcontact) . "'";

			$resqlcontact = $db->query($sqlcontact);
			if ($resqlcontact) {
				while ($objcontact = $db->fetch_object($resqlcontact)) {
					//print $objcontact->code.'-'.$objcontact->source.'-'.$objcontact->fk_socpeople."\n";
					(new PropagateContacts)->add_contacto($objcontact->fk_socpeople, $objcontact->code, $objcontact->source, 0, $docorigen, $docdestino); // May failed because of duplicate key or because code of contact type does not exists for new object
				}
			} else dol_print_error($resqlcontact);
		}

	}
	public function add_contacto($fk_socpeople, $type_contact, $source = 'external', $notrigger = 0, $elementorigen, $docdestino)
	{
		// phpcs:enable
		global $user, $langs, $db;


		// Check parameters
		if ($fk_socpeople <= 0) {
			$langs->load("errors");
			$this->error = $langs->trans("ErrorWrongValueForParameterX", "1");
			dol_syslog(get_class($this) . "::add_contact " . $this->error, LOG_ERR);
			return -1;
		}
		if (!$type_contact) {
			$langs->load("errors");
			$this->error = $langs->trans("ErrorWrongValueForParameterX", "2");
			dol_syslog(get_class($this) . "::add_contact " . $this->error, LOG_ERR);
			return -2;
		}

		$id_type_contact = 0;
		if (is_numeric($type_contact)) {
			$id_type_contact = $type_contact;
		} else {
			// We look for id type_contact
			$sql = "SELECT tc.rowid";
			$sql .= " FROM " . MAIN_DB_PREFIX . "c_type_contact as tc";
			$sql .= " WHERE tc.element='" . $db->escape($elementorigen->element) . "'";
			$sql .= " AND tc.source='" . $db->escape($source) . "'";
			$sql .= " AND tc.code='" . $db->escape($type_contact) . "' AND tc.active=1";
			//print $sql;
			$resql = $db->query($sql);
			if ($resql) {
				$obj = $db->fetch_object($resql);
				if ($obj) $id_type_contact = $obj->rowid;
			}
		}

		if ($id_type_contact == 0) {
			$this->error = 'CODE_NOT_VALID_FOR_THIS_ELEMENT';
			dol_syslog("CODE_NOT_VALID_FOR_THIS_ELEMENT: Code type of contact '" . $type_contact . "' does not exists or is not active for element " . self::$elemento . ", we can ignore it");
			return -3;
		}

		$datecreate = dol_now();

		// Socpeople must have already been added by some trigger, then we have to check it to avoid DB_ERROR_RECORD_ALREADY_EXISTS error
		$TListeContacts = $this->liste_contacto(-1, $source,0,'',$elementorigen->element);
		$already_added = false;
		if (is_array($TListeContacts) && !empty($TListeContacts)) {
			foreach ($TListeContacts as $array_contact) {
				if ($array_contact['status'] == 4 && $array_contact['id'] == $fk_socpeople && $array_contact['fk_c_type_contact'] == $id_type_contact) {
					$already_added = true;
					break;
				}
			}
		}
		switch ($docdestino->element) {
			case 'propal':
				$id_type_contact = 31;
				break;
			case 'commande':
				$id_type_contact = 91;
				break;
			case 'supplier_proposals':
				$id_type_contact = 110;
				break;
			case 'order_supplier':
				$id_type_contact = 145;
				break;
		}

		if (1 == 1) {
			$db->begin();
			//insertar primero en element_element
			$sql="INSERT INTO llx_element_element (fk_source, sourcetype, fk_target, targettype) VALUES (".$elementorigen->id.", 'commande', ".$docdestino->id.", 'order_supplier');";
			// Insert into database
			$sql .= "INSERT INTO " . MAIN_DB_PREFIX . "element_contact";
			$sql .= " (element_id, fk_socpeople, datecreate, statut, fk_c_type_contact) ";
			$sql .= " VALUES (" . $docdestino->id . ", " . $fk_socpeople . " , ";
			$sql .= "'" . $db->idate($datecreate) . "'";
			$sql .= ", 4, " . $id_type_contact;
			$sql .= ")";

			$resql = $db->query($sql);
			if ($resql) {
				if (!$notrigger) {
					$result = $this->call_trigger(strtoupper(self::$elemento) . '_ADD_CONTACT', $user);
					if ($result < 0) {
						$db->rollback();
						return -1;
					}
				}

				$db->commit();
				return 1;
			} else {
				if ($db->errno() == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
					$this->error = $db->errno();
					$db->rollback();
					return -2;
				} else {
					$this->error = $db->error();
					$db->rollback();
					return -1;
				}
			}
		} else return 0;
	}

	public function liste_contacto($status = -1, $source = 'external', $list = 0, $code = '',$elementorigen)
	{
		// phpcs:enable
		global $langs, $db;

		$tab = array();

		$sql = "SELECT ec.rowid, ec.statut as statuslink, ec.fk_socpeople as id, ec.fk_c_type_contact"; // This field contains id of llx_socpeople or id of llx_user
		if ($source == 'internal') $sql .= ", '-1' as socid, t.statut as statuscontact, t.login, t.photo";
		if ($source == 'external' || $source == 'thirdparty') $sql .= ", t.fk_soc as socid, t.statut as statuscontact";
		$sql .= ", t.civility as civility, t.lastname as lastname, t.firstname, t.email";
		$sql .= ", tc.source, tc.element, tc.code, tc.libelle";
		$sql .= " FROM " . MAIN_DB_PREFIX . "c_type_contact tc";
		$sql .= ", " . MAIN_DB_PREFIX . "element_contact ec";
		if ($source == 'internal') $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "user t on ec.fk_socpeople = t.rowid";
		if ($source == 'external' || $source == 'thirdparty') $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "socpeople t on ec.fk_socpeople = t.rowid";
		$sql .= " WHERE ec.element_id =" . self::$id1;
		$sql .= " AND ec.fk_c_type_contact=tc.rowid";
		$sql .= " AND tc.element='" . $db->escape($elementorigen) . "'";
		if ($code) $sql .= " AND tc.code = '" . $db->escape($code) . "'";
		if ($source == 'internal') $sql .= " AND tc.source = 'internal'";
		if ($source == 'external' || $source == 'thirdparty') $sql .= " AND tc.source = 'external'";
		$sql .= " AND tc.active=1";
		if ($status >= 0) $sql .= " AND ec.statut = " . $status;
		$sql .= " ORDER BY t.lastname ASC";

		dol_syslog(get_class($this) . "::liste_contact", LOG_DEBUG);
		$resql = $db->query($sql);
		if ($resql) {
			$num = $db->num_rows($resql);
			$i = 0;
			while ($i < $num) {
				$obj = $db->fetch_object($resql);

				if (!$list) {
					$transkey = "TypeContact_" . $obj->element . "_" . $obj->source . "_" . $obj->code;
					$libelle_type = ($langs->trans($transkey) != $transkey ? $langs->trans($transkey) : $obj->libelle);
					$tab[$i] = array('source' => $obj->source, 'socid' => $obj->socid, 'id' => $obj->id,
						'nom' => $obj->lastname, // For backward compatibility
						'civility' => $obj->civility, 'lastname' => $obj->lastname, 'firstname' => $obj->firstname, 'email' => $obj->email, 'login' => $obj->login, 'photo' => $obj->photo, 'statuscontact' => $obj->statuscontact,
						'rowid' => $obj->rowid, 'code' => $obj->code, 'libelle' => $libelle_type, 'status' => $obj->statuslink, 'fk_c_type_contact' => $obj->fk_c_type_contact);
				} else {
					$tab[$i] = $obj->id;
				}

				$i++;
			}

			return $tab;
		} else {
			$this->error = $db->lasterror();
			dol_print_error($db);
			return -1;
		}
	}


}
