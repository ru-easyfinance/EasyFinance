<?php
$step = $_GET['step'];
		
$ps = new PlanSettings();
$plan = $ps->plan;
$income = $ps->income;
$outcome = $ps->outcome;
$accounts = $ps->accounts;

if (isset($_POST['planning_horizon']) && $_POST['planning_horizon'] != '')
{
	$plan['planning_horizon'] = @html($_POST['planning_horizon']);
	
	$plan_conf['day'] = date('d');
	$plan_conf['month'] = date('m');
	$plan_conf['year'] = date('Y');	
	
	$ps->plan['planning_horizon'] = $plan['planning_horizon'];
	
	switch($plan['planning_horizon'])
	{		
		case 1:
			//$plan['date_finish_plan'] = date("Y-m-d", mktime(0, 0, 0, $month, $day+8, $year));
			$week=date("W");
			$sun_day=7*$week-date("w",mktime(0,0,0,1,7*$week));

			$plan['date_start_plan'] = date("Y-m-d",mktime(0,0,0,1,$sun_day-6));
			$plan['date_finish_plan'] = date("Y-m-d",mktime(0,0,0,1,$sun_day));
			
			//$cnt_control_point = 1;
		break;
		case 2:
			//$plan['date_finish_plan'] = date("Y-m-d", mktime(0, 0, 0, $month+1, $day, $year));
			/*$plan['date_start_plan'] = date("Y-m-d", mktime(0, 0, 0, $plan_conf['month'], "01", $plan_conf['year']));
			$plan['date_finish_plan'] = date("Y-m-d", mktime(0, 0, 0, $plan_conf['month']+1, "01"-1, $plan_conf['year']));*/
			
			$month = html($_POST['planning_month']);
			$year = html($_POST['planning_year']);
			
			$plan['date_start_plan'] = date("Y-m-d", mktime(0, 0, 0, $month, "01", $year));
			$plan['date_finish_plan'] = date("Y-m-d", mktime(0, 0, 0, $month+1, "01", $year));			
			
			$plan['planning_month'] = $month;
			$plan['planning_year'] = $year;
			
			//$cnt_control_point = round(date("t") / 7);
		break;
		case 3:
			//$plan['date_finish_plan'] = date("Y-m-d", mktime(0, 0, 0, $month+3, $day, $year));
			$kvartal= intval((date('n')+2)/3);
			switch ($kvartal)
			{
				case 1:
					$month_start = 1;
					$month_finish = 3;
				break;
				case 2:
					$month_start = 4;
					$month_finish = 6;
				break;
				case 3:
					$month_start = 7;
					$month_finish = 9;
				break;
				case 4:
					$month_start = 10;
					$month_finish = 12;
				break;
			}
			$day = '01';
			$plan['date_start_plan'] = date("Y-m-d", mktime(0, 0, 0, $month_start, $day, $plan_conf['year']));
			$plan['date_finish_plan'] = date("Y-m-d", mktime(0, 0, 0, $month_finish+1, $day-1, $plan_conf['year']));
			
			//$cnt_control_point = 3;
		break;
		case 4:
			//$plan['date_finish_plan'] = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year+1));
			$plan['date_start_plan'] = date("Y-m-d", mktime(0, 0, 0, "01", "01", $plan_conf['year']));
			$plan['date_finish_plan'] = date("Y-m-d", mktime(0, 0, 0, "12"+1, "01"-1, $plan_conf['year']));
			// если контроль за планом месяц, то каунт = 3, иначе всегда 1
			//$cnt_control_point = $plan['planning_control'] == 2 ? 12 : 4;
		break;
	}
}
$ps->plan = $plan;
if (isset($_POST['in']) && $_POST['in'] != '')
{
	$category_income = @html($_POST['in']);
	
	$ph->plan_category['income'] = $ph->isCheckCategory($category_income, 'income');
	$income['income'] = $ph->plan_category['income'];
	$plan['total_income'] = $ph->total_income;
}

if (isset($_POST['out']) && $_POST['out'] != '')
{
	$category_outcome = @html($_POST['out']);
	
	$ph->plan_category['outcome'] = $ph->isCheckCategory($category_outcome, 'outcome');
	$outcome['outcome'] = $ph->plan_category['outcome'];
	$plan['total_outcome'] = $ph->total_outcome;
}

if (isset($_POST['account']) && $_POST['account'] != '')
{
	$accounts['accounts'] = @html($_POST['account']);
}

$plan['planning_control'] = @html($_POST['planning_control']);
$plan['is_detalize_income'] = $_POST['is_detalize_income'] == "on" ? 1 : 0;
$plan['is_detalize_outcome'] = $_POST['is_detalize_outcome'] == "on" ? 1 : 0;

$plan['check_is_p_operations'] = $_POST['check_is_p_operation'] == "on" ? 1 : 0;		
		
$plan['user_id'] = $_SESSION['user']['user_id'];

switch ($step)
{
	case "1":
		if (!empty($plan['plan_id']))
		{
			$tpl->assign('step', 2);	
		}else{
			$tpl->assign('step', 1);
		}
	break;
	
	case "2":
		$tpl->assign('step', 2);
	break;
	
	case "3":
		$date = $ps->getDateDetalizeCategoriesForPlan();
		$categories = $ph->getPrefixCategories(0, $_SESSION['user']['user_id']);
		$listDetalizeIncomeCategories = $ph->getDetalizeCategoriesForPlan($categories, "in", $date['start_date'], $date['finish_date'], $_SESSION['income']);				
		$tpl->assign('listDetalizeIncomeCategories',$listDetalizeIncomeCategories);				
		
		$tpl->assign('step', 3);
	break;
	
	case "4":
		$date = $ps->getDateDetalizeCategoriesForPlan();
		$categories = $ph->getPrefixCategories(1, $_SESSION['user']['user_id']);
		$listDetalizeOutcomeCategories = $ph->getDetalizeCategoriesForPlan($categories, "out", $date['start_date'], $date['finish_date'], $_SESSION['outcome']);
		$tpl->assign('listDetalizeOutcomeCategories',$listDetalizeOutcomeCategories);
		
		$tpl->assign('total_sum_income', $plan['total_income']);
		$tpl->assign('step', 4);
	break;
	
	case "5":				
		$tpl->assign('listUserAccounts',$_SESSION['user_account']);
		$tpl->assign('listPlanAccounts',$ps->accounts);
		
		$tpl->assign('step', 5);
	break;
	
	case "6":			
		$tpl->assign('step', 6);				
	break;
}

$ps->plan = $plan;
$ps->income = $income;
$ps->outcome = $outcome;
$ps->accounts = $accounts;
$ps->save();

$tpl->assign('plan',$plan);
?>