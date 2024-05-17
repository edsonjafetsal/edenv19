<?php

require_once '../../../main.inc.php';
class TablasListados
{


	public function mostrarbanking()
	{
		require_once DOL_DOCUMENT_ROOT . '/custom/reconciliation/class/leger.php';

		global $db,$conf;
		$sync = new Legger($db);
		$datos = new stdClass();

		$datos = $sync->outstanding();
		if (count((array)$datos) == 0) {
			echo '{"data": []}';
			return;
		}

		$datosJson = '{
		  "data": [';
		$i = 0;
		foreach ($datos as $item) {

			$datosJson .= '[
			      "' . ($i + 1) . '",
			      "' . $item->datec . '",
			      "' . $item->num_chq . '",
			      "' . $item->label . '",
			      "' . $item->amount . '"
			    ],';
			$i += 1;

		}


		$datosJson = substr($datosJson, 0, -1);

		$datosJson .= ']

		 }';

		echo $datosJson;

	}
	public function mostrarledger()
	{
		require_once DOL_DOCUMENT_ROOT . '/custom/reconciliation/class/leger.php';

		global $db,$conf;
		$sync = new Legger($db);
		$datos = new stdClass();
		$fechaini=	$_REQUEST["startdate"];
		$fechafin=	$_REQUEST["enddate"];
		$datos = $sync->ledger($fechaini,$fechafin);
		if (count((array)$datos) == 0) {
			echo '{"data": []}';
			return;
		}

		$datosJson = '{
		  "data": [';
		$i = 0;
		foreach ($datos as $item) {

			$datosJson .= '[
			      "' . ($i + 1) . '",
			      "' . $item->piece_num . '",
			      "' . $item->journal_label . '",
			      "' . $item->numero_compte . '",
			      "' . $item->label_operation . '",
			      "' . $item->debit . '"
			    ],';
			$i += 1;

		}


		$datosJson = substr($datosJson, 0, -1);

		$datosJson .= ']

		 }';

		echo $datosJson;

	}
	public function mostrardeposit()
	{
		require_once DOL_DOCUMENT_ROOT . '/custom/reconciliation/class/leger.php';

		global $db,$conf;
		$sync = new Legger($db);
		$datos = new stdClass();

		$datos = $sync->deposit();
		if (count((array)$datos) == 0) {
			echo '{"data": []}';
			return;
		}

		$datosJson = '{
		  "data": [';
		$i = 0;
		foreach ($datos as $item) {

			$datosJson .= '[
			      "' . ($i + 1) . '",
			      "' . $item->datec . '",
			      "' . $item->num_chq . '",
			      "' . $item->label . '",
			      "' . $item->amount . '"
			    ],';
			$i += 1;

		}


		$datosJson = substr($datosJson, 0, -1);

		$datosJson .= ']

		 }';

		echo $datosJson;

	}

}
/**
 * Define all constants needed for ajax request
 */
if (!defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', 1);
} // Disables token renewal
if (!defined('NOREQUIREMENU')) {
	define('NOREQUIREMENU', '1');
}
if (!defined('NOREQUIREHTML')) {
	define('NOREQUIREHTML', '1');
}
if (!defined('NOREQUIREAJAX')) {
	define('NOREQUIREAJAX', '1');
}
if (!defined('NOREQUIRESOC')) {
	define('NOREQUIRESOC', '1');
}
if (!defined('NOCSRFCHECK')) {
	define('NOCSRFCHECK', '1');
}
if (empty($_GET ['keysearch']) && !defined('NOREQUIREHTML')) {
	define('NOREQUIREHTML', '1');
}

$res = 0;


global $db,$user;

header("HTTP/1.1 200 OK");

$action = GETPOST('action', 'alpha');
$ledger= GETPOST('ledger');
$description= GETPOST('description');
$date= GETPOST('date');
$id= GETPOST('id');
$fecha=strtotime($date);
$fecha=$db->idate($fecha);
$description= GETPOST('description');
global $langs, $conf, $db;
//get php input var
$phpInput = file_get_contents('php://input');

/**
 * @return void
 * @throws \QuickBooksOnline\API\Exception\SdkException
 */


switch ($action) {
	case 'deleteevent':
		require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
		$actioncom = new ActionComm($db);
		$actioncom->fetch($id);
		$actioncom->delete($user);
		break;
	case 'editevent':
		require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
		$actioncom = new ActionComm($db);
		$actioncom->fetch($id);
		$actioncom->note_private = $description;
		$actioncom->note_public = $description;
		$actioncom->note = $description;
		$actioncom->update_note($description);
		$actioncom->update_note_public($description);
		$actioncom->update($user);
		break;
	case 'getthirdpartyinfo':
		require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
		$thirdparty = new Societe($db);
		$thirdparty->fetch(GETPOST('socid'));
		$datos = new stdClass();
		$datos->name = $thirdparty->name;
		$datos->address = $thirdparty->address;
		$datos->zip = $thirdparty->zip;
		$datos->result= 'ok';
        $result=array('result' => 'ok','data' => $datos);
		echo json_encode($result);


}

//echo json_encode(array('result' => 'ok'));



