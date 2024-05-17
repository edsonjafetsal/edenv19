<?php

function UpdateRoles(){
	global $db,$conf,$user;

	if($_REQUEST["action"] == "updatesoc"){
		$sqlPropal = "SELECT lp.fk_soc as societe from llx_propal lp where lp.rowid =  ".$_REQUEST["socid"];
		$ressqlPropal = $db->query($sqlPropal);
		if($ressqlPropal){
			$objPropal = $db->fetch_object($ressqlPropal);
			$sql = "SELECT rowid as id, nom as nom FROM llx_societe where rowid = ".$objPropal->societe." order by nom ASC";
			$resql = $db->query($sql);
			$objrow =  $db->fetch_object($resql);
		}

		require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
		require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
		require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
		require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
		$formcompany = new FormCompany($db);
		$sqlSocPeople = "select ls2.rowid as idsoc  from llx_societe ls ";
		$sqlSocPeople .= "left join llx_socpeople ls2 on ls2.fk_soc=ls.rowid ";
		$sqlSocPeople .= "where ls.rowid =".$objrow->id;
		$resSocPreople = $db->query($sqlSocPeople);
		if ($resSocPreople){
			$objrowSoc =  $db->fetch_object($resSocPreople);
			$objectContact = new Contact($db);
			$res = $objectContact->fetch($objrowSoc->idsoc, $user);
			$objectContact->fetchRoles();
			$objsoc = new Societe($db);
			$objsoc->fetch($objectContact->socid);
			// Obtener los roles del formData
			$newRoles = isset($_POST['roles']) ? json_decode($_POST['roles'], true) : [];
			// Obtener los roles anteriores del contacto
			$oldRoles = $objectContact->roles;
			if($_REQUEST["switchStatus"]=="on"){

					// Verificar si el rol 4613 está presente en los roles antiguos
					if (!in_array(198, $newRoles)) {
						$newRoles[] = 198;
					}
					$key199 = array_search(199, $newRoles);
					if ($key199 !== false) {
						unset($newRoles[$key199]);
					}
					$rolesToAdd = array_diff($newRoles, $oldRoles);
					$objectContact->roles = $rolesToAdd;
					$result = $objectContact->update($objrowSoc->idsoc, $user);


			}elseif ($_REQUEST["switchStatus"]=="off"){
				$key = array_search(198, $newRoles);
				if ($key !== false) {
					unset($newRoles[$key]);
					$newRoles[$key] = 199;
				}
				$objectContact->roles = $newRoles;
				$result = $objectContact->update($objrowSoc->idsoc, $user);
			}

		}

			}
		}

function getBulkEmail($specificId = null){
	global $db, $conf;
	$conts = []; // Inicializamos un array para almacenar los valores

	if ($specificId !== null) { // Verificar si se proporcionó un ID específico
		$toselectid = $specificId; // Usar el ID específico proporcionado
		$code = 'BulkEmail';
		$sqlBulkExist = "SELECT t.email as email";
		$sqlBulkExist .= " FROM llx_c_type_contact tc, llx_element_contact ec ";
		$sqlBulkExist .= " LEFT JOIN llx_socpeople t on ec.fk_socpeople = t.rowid ";
		$sqlBulkExist .= " WHERE ec.element_id =".$toselectid." AND ec.fk_c_type_contact=tc.rowid AND tc.code = '".$code."' AND tc.element='propal' AND tc.source = 'external' AND tc.active=1 ORDER BY t.lastname asc";
		$resBulk = $db->query($sqlBulkExist);
		$obj = $db->fetch_object($resBulk);
		if($obj) {
			$conts[] = $obj->email;
		}
	} else { // Si no se proporciona un ID específico, proceder como antes
		if(isset($_REQUEST["toselect"]) && is_array($_REQUEST["toselect"])) {
			foreach($_REQUEST["toselect"] as $toselectid){
				$code = 'BulkEmail';
				$sqlBulkExist = "SELECT t.email as email";
				$sqlBulkExist .= " FROM llx_c_type_contact tc, llx_element_contact ec ";
				$sqlBulkExist .= " LEFT JOIN llx_socpeople t on ec.fk_socpeople = t.rowid ";
				$sqlBulkExist .= " WHERE ec.element_id =".$toselectid." AND ec.fk_c_type_contact=tc.rowid AND tc.code = '".$code."' AND tc.element='propal' AND tc.source = 'external' AND tc.active=1 ORDER BY t.lastname asc";
				$resBulk = $db->query($sqlBulkExist);
				$obj = $db->fetch_object($resBulk);
				if($obj) {
					$conts[] = $obj->email;
				}
			}
		} elseif(isset($_REQUEST["id"])) { // Si solo hay un ID en lugar de un arreglo
			$toselectid = $_REQUEST["id"]; // Tomar el valor de $_REQUEST["id"]
			$code = 'BulkEmail';
			$sqlBulkExist = "SELECT t.email as email";
			$sqlBulkExist .= " FROM llx_c_type_contact tc, llx_element_contact ec ";
			$sqlBulkExist .= " LEFT JOIN llx_socpeople t on ec.fk_socpeople = t.rowid ";
			$sqlBulkExist .= " WHERE ec.element_id =".$toselectid." AND ec.fk_c_type_contact=tc.rowid AND tc.code = '".$code."' AND tc.element='propal' AND tc.source = 'external' AND tc.active=1 ORDER BY t.lastname asc";
			$resBulk = $db->query($sqlBulkExist);
			$obj = $db->fetch_object($resBulk);
			if($obj) {
				$conts[] = $obj->email;
			}
		}
	}

	$valueString = implode(',', $conts);
	return $valueString;
}


function getInfoBulkEmail($field = 'email', $specificId = null){
	global $db, $conf;
	$conts = array(); // Inicializamos un array para almacenar los valores

	if ($specificId !== null) { // Verificar si se proporcionó un ID específico
	$toselectid = $specificId; // Usar el ID específico proporcionado
	$code = 'BulkEmail';
	$sqlBulNameTittlekExist = "SELECT t.$field as value ";
	$sqlBulNameTittlekExist .= "FROM llx_c_type_contact tc, llx_element_contact ec LEFT JOIN llx_socpeople t ON ec.fk_socpeople = t.rowid ";
	$sqlBulNameTittlekExist .= "WHERE ec.element_id = ".$toselectid." AND tc.code = '".$code."' AND ec.fk_c_type_contact=tc.rowid AND tc.element='propal' AND tc.source = 'external' AND tc.active=1 ORDER BY t.lastname ASC ";

	$resBulk = $db->query($sqlBulNameTittlekExist);
	$obj = $db->fetch_object($resBulk);
	if ($obj) {
		$value = $obj->value;
	} else { // Si no se proporciona un ID específico, retornar un valor vacío
		$value = '';
	}

	return $value;
} else { // Si no se proporciona un ID específico, proceder como antes
		if(isset($_REQUEST["toselect"]) && is_array($_REQUEST["toselect"])) {
			foreach($_REQUEST["toselect"] as $toselectid) {
				$code = 'BulkEmail';
				$sqlBulNameTittlekExist = "SELECT ec.rowid, ec.fk_socpeople as id, t.firstname, t.lastname, t.civility as title, t.email, tc.code, tc.libelle ";
				$sqlBulNameTittlekExist .= "FROM llx_c_type_contact tc, llx_element_contact ec LEFT JOIN llx_socpeople t ON ec.fk_socpeople = t.rowid ";
				$sqlBulNameTittlekExist .= "WHERE ec.element_id = ".$toselectid." AND tc.code = '".$code."' AND ec.fk_c_type_contact=tc.rowid AND tc.element='propal' AND tc.source = 'external' AND tc.active=1 ORDER BY t.lastname ASC ";

				$resBulk = $db->query($sqlBulNameTittlekExist);
				while($obj = $db->fetch_object($resBulk)) {
					$conts[] = $obj->$field;
				}
			}
		} elseif(isset($_REQUEST["id"])) { // Si solo hay un ID en lugar de un arreglo
			$toselectid = $_REQUEST["id"]; // Tomar el valor de $_REQUEST["id"]
			$code = 'BulkEmail';
			$sqlBulNameTittlekExist = "SELECT ec.rowid, ec.fk_socpeople as id, t.firstname, t.lastname, t.civility as title, t.email, tc.code, tc.libelle ";
			$sqlBulNameTittlekExist .= "FROM llx_c_type_contact tc, llx_element_contact ec LEFT JOIN llx_socpeople t ON ec.fk_socpeople = t.rowid ";
			$sqlBulNameTittlekExist .= "WHERE ec.element_id = ".$toselectid." AND tc.code = '".$code."' AND ec.fk_c_type_contact=tc.rowid AND tc.element='propal' AND tc.source = 'external' AND tc.active=1 ORDER BY t.lastname ASC ";

			$resBulk = $db->query($sqlBulNameTittlekExist);
			while($obj = $db->fetch_object($resBulk)) {
				$conts[] = $obj->$field;
			}
		}
	}
	return $conts;
}

function transformTitlesU($title){
	$titleMap = array(
		'MME' => 'Mrs.',
		'MR' => 'Mr.',
		'MLE' => 'Ms.',
		'MTRE' => 'Master',
		'DR' => 'Doctor'
	);

	// Transformar el título según el mapa de codificación específico
	if (array_key_exists($title, $titleMap)) {
		return $titleMap[$title];
	} else {
		// Si el título no está en el mapa, se deja sin cambios
		return $title;
	}
}



// Función para transformar los títulos a la codificación específica
function transformTitles($titles){
	$transformedTitles = array();
	$titleMap = array(
		'MME' => 'Mrs.',
		'MR' => 'Mr.',
		'MLE' => 'Ms.',
		'MTRE' => 'Master',
		'DR' => 'Doctor'
	);

	// Dividir los títulos por coma y transformarlos según el mapa de codificación específico
	$titlesArray = explode(',', $titles);
	foreach ($titlesArray as $title) {
		if (array_key_exists($title, $titleMap)) {
			$transformedTitles[] = $titleMap[$title];
		} else {
			// Si el título no está en el mapa, se deja sin cambios
			$transformedTitles[] = $title;
		}
	}

	// Convertir el array transformado de títulos de nuevo a una cadena separada por comas
	return implode(',', $transformedTitles);
}
