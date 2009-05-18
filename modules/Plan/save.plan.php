<?php
// получаем вводимые параметры
$plan = $_SESSION['plan'];
$plan['comment'] = @html($_POST['comment']);
$plan['notice_sms'] = $_POST['notis_sms'] == "on" ? 1 : 0;
$plan['notice_email'] = $_POST['notice_email'] == "on" ? 1 : 0;
//$_SESSION['plan'] = false;
// !!!$plan['planning_horizon'] = @html($_POST['planning_horizon']);
// !!!$plan['planning_control'] = @html($_POST['planning_control']);
//$plan['total_income'] = @html($_POST['sum_income']);
// !!!$plan['is_detalize_income'] = $_POST['is_detalize_income'] == "on" ? 1 : 0;
//$plan['total_outcome'] = @html($_POST['sum_outcome']);
// !!!$plan['is_detalize_outcome'] = $_POST['is_detalize_outcome'] == "on" ? 1 : 0;

/*list($day,$month,$year) = explode(".", $_POST['date_begin_plan']);
$plan['date_start_plan'] = $year."-".$month."-".$day;*/


/*switch($plan['planning_control'])
{
	case 1:
		$plan['date_to_control'][0]['date_start'] = $plan['date_start_plan'];
		$plan['date_to_control'][0]['date_finish'] = $plan['date_finish_plan'];
		$plan['date_to_control'][0]['total_sum_plan'] = $plan['date_finish_plan'];
	break;
	
	case 2:
		$t = date("t");
	break;
}*/

// !!!$plan['check_is_p_operations'] = $_POST['check_is_p_operation'] == "on" ? 1 : 0;
// !!!$plan['notice_sms'] = $_POST['notis_sms'] == "on" ? 1 : 0;
// !!!$plan['notice_email'] = $_POST['notice_email'] == "on" ? 1 : 0;
// !!!$plan['comment'] = @html($_POST['comment']);
// !!!$plan['user_id'] = $_SESSION['user']['user_id'];

//!!!$category_income = @html($_POST['in']);
//!!!$category_outcome = @html($_POST['out']);
//!!!$user_accounts = @html($_POST['account']);

//!!!$ph->plan_category['income'] = $ph->isCheckCategory($category_income, 'income');
//!!!$ph->plan_category['outcome'] = $ph->isCheckCategory($category_outcome, 'outcome');
//!!!$plan['total_income'] = $ph->total_income;
//!!!$plan['total_outcome'] = $ph->total_outcome;
$ph->total_income = $plan['total_income'];
$ph->total_outcome = $plan['total_outcome'];
$user_accounts = $_SESSION['accounts'];
$ph->plan_category['income'] = $_SESSION['income'];
$ph->plan_category['outcome'] = $_SESSION['outcome'];

if (isset($plan['plan_id']) && $plan['plan_id'] != '')
{
	$ph->deleteUserPlan($_SESSION['user']['user_id'], $plan['plan_id'], &$dbs);
}	

if ($ph->savePlan($plan, $user_accounts['accounts'], &$dbs, $sys_currency))
{
	header("location: index.php?modules=plan");
}else{
	$tpl->assign("badOperation", "План не сохранен, попробуйте еще раз.");
}
?>