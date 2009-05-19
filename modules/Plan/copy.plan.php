<?php

$step = html($_GET['step']);
$id = html($_GET['id']);

$tpl->assign('plan_copy', 1);

switch($step)
{
	case 2:
		$tpl->assign('current_month', date("m"));
		$tpl->assign('current_year', date("Y"));
		$tpl->assign('step', 2);
		$tpl->assign('step', 2);
		$tpl->assign('id', $id);
	break;
	
	case 3:
		$tpl->assign('planning_month', html($_POST['planning_month']));
		$tpl->assign('planning_year', html($_POST['planning_year']));
		$tpl->assign('step', 3);
		$tpl->assign('id', $id);
	break;
	
	default:
		$plan_copy = $ph->getListCopyPlan(&$dbs);
		$cnt = count($plan_copy);
		
		for($i=0; $i<$cnt; $i++)
		{
			list($year, $month, $day) = explode("-", $plan_copy[$i]['date_start_plan']);
			$plan_copy[$i]['replace_date_start'] = $sys_month[$month]." ".$year;
		}
		$tpl->assign('plan_copy', $plan_copy);
		$tpl->assign('step', 1);
	break;
}

?>