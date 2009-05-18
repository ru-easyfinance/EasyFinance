<?
define("SCRIPTS_ROOT", "../");
require_once(SCRIPTS_ROOT."core/classes/hmBills.class.php");
require_once(SCRIPTS_ROOT."core/external/DBSimple/Mysql.php");

if (empty($_SESSION['user'])) {
    header("Location: index.php");
}

$tpl->assign('name_page', 'bills');
$action = html($g_action);

switch ($action) {
    case "add":
    	
	break;
	
	default:
		
		$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		$dbs->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
		$bills = new hmBills($_SESSION['user']['user_id'], &$dbs);
		$bills->loadBills();
		pre($bills->Bills());
		$tpl->assign_by_ref("bills", $bills->Bills());
		
	break;
}
?>