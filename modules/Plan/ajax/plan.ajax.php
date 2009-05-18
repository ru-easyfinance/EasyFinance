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

	// создаем объект класса DB_Simple
	$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
	$dbs->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
} catch (Exception $e) {
	message_error(CRITICAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
}

// получаем действие
$action = html($g_action);

switch ($action) {

	case "change_date_period":
		$planning_horizon = $_GET['period'];

		switch ($planning_horizon)
		{
			case "1":
				$week=date("W");
				$sun_day=7*$week-date("w",mktime(0,0,0,1,7*$week));

				$finish_date = date("Y-m-d",mktime(0,0,0,1,$sun_day-6));
				$start_date = date("Y-m-d",mktime(0,0,0,1,$sun_day-20));
			break;

			case "2":
				$finish_date = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
				$start_date = date("Y-m-d", mktime(0, 0, 0, date("m")-3, "01", date("Y")));
			break;

			case "3":
				$finish_date = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
				$start_date = date("Y-m-d", mktime(0, 0, 0, date("m")-9, "01", date("Y")));
			break;

			case "4":
				$finish_date = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
				$start_date = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")-3));
			break;
		}

		$listDetalizeCategoriesIncome = "<div id='d_0'>".$ph->getDetalizeCategoriesForPlan($_SESSION['user_category'], "in", $start_date, $finish_date)."</div>";

		$listDetalizeCategoriesOutcome = "<div id='d_1'>".$ph->getDetalizeCategoriesForPlan($_SESSION['user_category'], "out", $start_date, $finish_date)."</div>";

		$listDetalizeCategories = "<div id='drain'>".$listDetalizeCategoriesIncome.$listDetalizeCategoriesOutcome."</div>";

		/*$filename = UPLOAD_DIR.$_SESSION['user']['user_id'].'.xml';
		$handle = fopen($filename, 'a');
		 chmod ($filename, 0777);
		fwrite($handle, $listDetalizeCategories);
		fclose($handle);*/
		//header("Content-type: text/xml");
		echo $listDetalizeCategories;
		exit;
	break;

	case "get_param_period_date":
		$planning_horizon = html($_GET['planning_horizon']);

		switch ($planning_horizon)
		{
			case 2:
				for ($i=1; $i<32; $i++)
				{
					$day[$i]['day'] = $i;
				}
				$tpl->assign("day", $day);
				$tpl->assign("month", $sys_month);
			break;

			case 3:
				$start = "01.01.2009";
				$finish = "31.03.2009";
			break;

			case 4:
				$start = "01.01.2009";
				$finish = "31.12.2009";
			break;
		}

		$tpl->assign("planning_horizon", $planning_horizon);
		echo $tpl->fetch("Plan/plan.param_period_date.html");
		exit;
	break;

	case "get_period_date":
		$planning_horizon = html($_GET['planning_horizon']);
		switch ($planning_horizon)
		{
			case 1:
				$start = "22.12.2008";
				$finish = "28.12.2008";
			break;

			case 2:
				$start = "11.01.2009";
				$finish = "11.02.2009";
			break;

			case 3:
				$start = "01.01.2009";
				$finish = "31.03.2009";
			break;

			case 4:
				$start = "01.01.2009";
				$finish = "31.12.2009";
			break;
		}
		$tpl->assign("planning_horizon", $planning_horizon);
		$tpl->assign("start", $start);
		$tpl->assign("finish", $finish);

		echo $tpl->fetch("Plan/plan.period_setting.html");
		exit;
	break;

}

?>