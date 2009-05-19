<?
define("SCRIPTS_ROOT", "../");
require_once(SCRIPTS_ROOT."core/classes/hmBills.class.php");
require_once (SCRIPTS_ROOT . "core/external/DBSimple/Mysql.php");

if (empty($_SESSION['user'])) {
    header("Location: index.php");
}

$action = html($g_action);

switch ($action) {
	case "getbilltypes":
		$rows = hmBills::getBillTypes();
		$tpl->assign("rows", $rows);
		echo $tpl->fetch("billwz.billtypes.html");
		exit;
	break;
	case "makeform":
		$type = $_GET['type_id'];
		$bill = new hmBill();
		$bill->newEmptyBill($type);
		$tpl->assign("fields", $bill->fields());
		echo $tpl->fetch("billwz.billform.html");
		exit;
	break;
	case "validate":

		$type = $_GET['type_id'];
		$bill = new hmBill();
		$bill->newEmptyBill($type);
		$errors = Array();
		foreach ($bill->fields() as $name => $field) {
			if ((isset($_GET[$name]) && !empty($_GET[$name])) || $field->permission == "view") {
				if (!$field->validate($_GET[$name])) {
					$errors[$name] = "error";
				}
			} else {
				$errors[$name] = "error";	
			}
		}
		if (count($errors)) {

			$tpl->assign("fields", $bill->fields());
			$tpl->assign("data", $_GET);
			$tpl->assign("errors", $errors);
			echo $tpl->fetch("billwz.billform.html");
		} else {
			echo hmBills::CreateNewBill($_SESSION['user']['user_id'], $_GET['type_id'], $_GET);
		}
		exit;
	break;
}
?>