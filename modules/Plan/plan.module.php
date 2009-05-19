<?php
/**
* file: plan.module.php
* author: Roman Korostov
* date: 30/11/08	
**/

// подключаем все необходимые библиотеки
require_once (SYS_DIR_LIBS . "PlanHandler.php");
require_once (SYS_DIR_LIBS . "external/DBSimple/Mysql.php");
require_once (SYS_DIR_LIBS . "money.class.php");

// если пользователь не авторизован, фигачим его на главную страницу
if (empty($_SESSION['user'])) {
    header("Location: index.php");
}


// Инициируем контроллер планирования бюджета - объект, выполняющий всё, что связано с планированием. Всё в нём.
try {
	$conf['account'] = $acc;
	$conf['money'] = new Money($db, $user);
	$conf['category'] = $cat;
	$conf['sys_currency'] = $sys_currency;
	
	$ph = new PlanHandler($conf);
	
	$_plan_path = SYS_DIR_MOD."Plan/";
	
	// создаем объект класса DB_Simple
	$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
	$dbs->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
} catch (Exception $e) {
	message_error(CRITICAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
}


// получаем действие
$action = html($g_action);

if (!empty($_POST['save_plan']) && empty($action))
{
	$action = "save_plan";	
}

if (!empty($_GET['month']) && !empty($_GET['year']))
{
	$month = html($_GET['month']);
	$year = html($_GET['year']);
}else{
	$month = date("m");	
	$year = date("Y");
}
	$last_period = date("Y-m-d", mktime(0, 0, 0, $month-1, "01", $year));
	$current_period = date("Y-m-d", mktime(0, 0, 0, $month, "01", $year));
	$next_period = date("Y-m-d", mktime(0, 0, 0, $month+1, "01", $year));
	
	$date['last_period'] = $current_period;
	$date['next_period'] = $next_period;
	
	$history_plan['history_settings']['text_last_period'] = "$month.$year";
	
	
	list($year,$month,$day) = explode("-", $last_period);
	$history_plan['history_settings']['last_period'] = "&month=$month&year=$year";	
	
	list($year,$month,$day) = explode("-", $next_period);
	$history_plan['history_settings']['next_period'] = "&month=$month&year=$year";	
	$history_plan['history_settings']['text_next_period'] = "$month.$year";
	
	$plan = $ph->getUserPlan($_SESSION['user']['user_id'], &$dbs, $date);
	if (empty($plan['plan_settings']['plan_id']))
	{		
		$tpl->assign('empty_history', true);
	}
/*}
else{
	
	$date['last_period'] = $current_period;
	$date['next_period'] = $next_period;
	
	$plan = $ph->getUserPlan($_SESSION['user']['user_id'], &$dbs);	
	list($day,$month,$year) = explode(".", $plan['plan_settings']['start_plan']);	
	
	$last_period = date("Y-m-d", mktime(0, 0, 0, $month-1, "01", $year));
	$next_period = date("Y-m-d", mktime(0, 0, 0, $month+1, "01", $year));
	
	list($year,$month,$day) = explode("-", $last_period);
	$history_plan['history_settings']['last_period'] = "&month=$month&year=$year";
	
	list($year,$month,$day) = explode("-", $next_period);
	$history_plan['history_settings']['next_period'] = "&month=$month&year=$year";
}*/

if (!empty($plan['plan_settings']['plan_id']) && empty($action))
{
	$action = "get_user_plan";
	
	$_SESSION['plan'] =  $ph->getSettingsFromPlan($plan['plan_settings']['plan_id'], &$dbs, $_SESSION['user']['user_id']);
	$_SESSION['income'] = $ph->getCategoriesFromPlan($plan['plan_settings']['plan_id'], &$dbs, 0);
	$_SESSION['outcome'] = $ph->getCategoriesFromPlan($plan['plan_settings']['plan_id'], &$dbs, 1);
	$_SESSION['accounts'] = $ph->geAccountsFromPlan($plan['plan_settings']['plan_id'], &$dbs, $_SESSION['user']['user_id']);
}

switch ($action){
	
	case "get_user_plan":
		require_once ($_plan_path . "get_user.plan.php");
	break;
	
	case "create_new_plan":
		$tpl->assign('empty_history', false);
		require_once ($_plan_path . "create_new.plan.php");
	break;
	
	case "save_plan":
		require_once ($_plan_path . "save.plan.php");
	break;
	
	case "delete_plan":	
		require_once ($_plan_path . "delete.plan.php");
	break;
	
	case "cancel_plan":
		require_once ($_plan_path . "cancel.plan.php");
	break;
	
	case "copy_plan":
		$tpl->assign('empty_history', false);
		require_once ($_plan_path . "copy.plan.php");
	break;
	
	case "save_copy_plan":
		require_once ($_plan_path . "save_copy.plan.php");
	break;
	
	case "history_plan":
		require_once ($_plan_path . "history.plan.php");
	break;
	
	default:		
		$tpl->assign('step', 1);		
		require_once ($_plan_path . "history.plan.php");
	break;
}

$tpl->assign('name_page', 'Plan/plan');
?>