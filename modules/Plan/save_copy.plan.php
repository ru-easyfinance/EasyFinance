<?php

$id = html($_POST['id']);
$month = html($_POST['planning_month']);
$year = html($_POST['planning_year']);

$plan = $ph->getSettingsFromPlan($id, &$dbs, $_SESSION['user']['user_id']);
$user_accounts = $ph->geAccountsFromPlan($id, &$dbs, $_SESSION['user']['user_id']);
$income = $ph->getCategoriesFromPlan($id, &$dbs, 0);
$outcome = $ph->plan_category['outcome']['outcome'] = $ph->getCategoriesFromPlan($id, &$dbs, 1);

unset($plan['plan_id']);

$plan['date_start_plan'] = date("Y-m-d", mktime(0, 0, 0, $month, "01", $year));
$plan['date_finish_plan'] = date("Y-m-d", mktime(0, 0, 0, $month+1, "01", $year));
$plan['comment'] = html($_POST['comment']);
$plan['notice_email'] = $_POST['notice_email'] == "on" ? 1 : 0;

$ph->plan_category['income']['income'] = $income;
$ph->plan_category['outcome']['outcome'] = $outcome;

$cnt = count($user_accounts);
for ($i=0; $i<$cnt; $i++)
{
	$accounts[$user_accounts[$i]['account_id']] = 'on';
}

if ($ph->checkPeriodPlan($plan['date_start_plan'], $plan['date_finish_plan'], &$dbs))
{
	if ($ph->savePlan($plan, $accounts, &$dbs, $sys_currency))
	{
		header("location: index.php?modules=plan");
	}else{
		$tpl->assign("badOperation", "План не сохранен, попробуйте еще раз.");
		$tpl->assign('plan_copy', 1);
	}	
}else{
	$tpl->assign("badOperation", "План на выбранный период уже существует.");
	$tpl->assign('plan_copy', 1);
}

?>