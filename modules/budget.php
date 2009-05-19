<?
/**
* file: account.php
* author: Roman Korostov
* date: 7/03/07	
**/

if (empty($_SESSION['user']))
{
	header("Location: index.php");
	exit;
}

$tpl->assign('name_page', 'budget');

$action = html($g_action);

switch( $action )
{
	case "add":
		$tpl->assign("page_title","budget add");	
		$tpl->assign('currency', $_SESSION['user_currency']);		
		$categories_select = get_three_select($_SESSION['user_category']);
		$tpl->assign('categories_select', $categories_select, 0, 0);
		$tpl->assign('bills_select', $_SESSION['user_account']);
		//$budget['date_from'] = '01.09.2007';
		//$budget['date_to'] = '30.09.2007';
		
		$budget['date_from'] = $_SESSION['budgetCurMonth'].'.01';
		$budget['date_to'] = $_SESSION['budgetNextMonth'].'.01';
			
		list($year,$month,$day) = explode(".", $budget['date_from']);			
		$budget['period'] = $sys_month[$month];
		
		$tpl->assign('period', $budget['period']."&nbsp;".$year);

		if (!empty($p_budget))
		{
			$budget['drain'] = $p_budget['drain'];
			//$budget['bill_id'] = $p_budget['bill_id'];
			$budget['cat_id'] = $p_budget['cat_id'];
			$budget['comment'] = html($p_budget['comment']);
			$budget['user_id'] = $user->getId();
			
			if (!empty($p_budget['money']))
			{
				if (preg_match('/^[0-9.]+$/', $p_budget['money']))
				{					
					$budget['money'] = $p_budget['money'];
				}
				else
				{
					$error_text['money'] = "Неверные данные! Вы можете вводить только цифры!";
				}								
			}else{
				$error_text['money'] = "Сумма не должна быть пустой!";
			}
			
			/*
			if (!empty($p_budget['date_from']) && !empty($p_budget['date_to']))
			{
				if (empty($error_text))
				{
					list($day,$month,$year) = explode(".", $p_budget['date_from']);
					$budget['date_from'] = $year.".".$month.".".$day;
				
					list($day,$month,$year) = explode(".", $p_budget['date_to']);
					$budget['date_to'] = $year.".".$month.".".$day;
				}else{
					$budget['date_from'] = $p_budget['date_from'];
					$budget['date_to'] = $p_budget['date_to'];
				}
			}else{
				$error_text['date_from'] = "Период не должен быть пустой!";
			}*/		
			
			if (empty($error_text))
			{			
				if($bdg->saveBudget($budget))
				{
					//$tpl->assign('good_text', "Счет добавлен!");					
					$_SESSION['good_text'] = "План бюджета добавлен!";
					header("Location: index.php?modules=budget");
				}				
			}
			else
			{
				$tpl->assign('error_text', $error_text);
				
				$tpl->assign('budget', $budget);
			}			
		}
		
		break;
	case "edit":	
		$tpl->assign("page_title","budget edit");
		$tpl->assign('currency', $_SESSION['user_currency']);
		
		$budget['date_from'] = $_SESSION['budgetCurMonth'].'.01';
		$budget['date_to'] = $_SESSION['budgetNextMonth'].'.01';
			
		list($year,$month,$day) = explode(".", $budget['date_from']);			
		$budget['period'] = $sys_month[$month];
		
		$tpl->assign('period', $budget['period']."&nbsp;".$year);

		if (!empty($p_budget))
		{
			$budget['id'] = $p_budget['id'];
			$budget['drain'] = $p_budget['drain'];
			$budget['bill_id'] = 0; //$p_budget['bill_id'];
			$budget['cat_id'] = $p_budget['cat_id'];
			$budget['comment'] = html($p_budget['comment']);
			$budget['user_id'] = $user->getId();
			
			if (!empty($p_budget['money']))
			{
				if (preg_match('/^[0-9.]+$/', $p_budget['money']))
				{					
					$budget['money'] = $p_budget['money'];
				}
				else
				{
					$error_text['money'] = "Неверные данные! Вы можете вводить только цифры!";
				}								
			}else{
				$error_text['money'] = "Сумма не должна быть пустой!";
			}
			/*
			if (!empty($p_budget['date_from']) && !empty($p_budget['date_to']))
			{
				if (empty($error_text))
				{
					list($day,$month,$year) = explode(".", $p_budget['date_from']);
					$budget['date_from'] = $year.".".$month.".".$day;
				
					list($day,$month,$year) = explode(".", $p_budget['date_to']);
					$budget['date_to'] = $year.".".$month.".".$day;
				}else{
					$budget['date_from'] = $p_budget['date_from'];
					$budget['date_to'] = $p_budget['date_to'];
				}
			}else{
				$error_text['date_from'] = "Период не должен быть пустой!";
			}*/		

			if (empty($error_text))
			{
				if($bdg->updateBudget($budget))
				{
					$_SESSION['good_text'] = "Планирование бюджета изменено!";
					header("Location: index.php?modules=budget");
				}
			}
			else
			{
				$tpl->assign('error_text', $error_text);				
			}	
		}
		else
		{		
			if (isset($g_id) && is_numeric($g_id))
			{
				$budget = $bdg->selectBudget(html($g_id));
				$categories_select = get_three_select($_SESSION['user_category'], 0, $budget[0]['cat_id']);
				$tpl->assign('categories_select', $categories_select);
				$tpl->assign('bills_select', $_SESSION['user_account']);
				
				if(count($budget)>0)
				{
					$tpl->assign('budget', $budget[0]);
				}
				else
				{
					$error_text['account'] = "Такой записи не существует!";
					$tpl->assign('error_text', $error_text);
				}
			}				
		}
		
		break;
	case "del":	
		$tpl->assign("page_title","budget del");

		if (isset($p_budget['id']) && is_numeric($p_budget['id']))
		{			
			if($bdg->deleteBudget(html($p_budget['id'])))
			{
				$_SESSION['good_text'] = "Запись из бюджета удалена!";
				header("Location: index.php?modules=budget");
				exit;
			}	
		}
		else
		{
			message_error(GENERAL_ERROR, "Получен неверный параметр!");
		}
		
		break;
	case "copy":
					
		list($year,$month) = explode(".", $_SESSION['budgetCurMonth']);
		$ml = date("m",mktime(0, 0, 0, $month-1, date("d"), $year));
		$mn = date("m",mktime(0, 0, 0, $month+1, date("d"), $year));

		$copyBudget = $bdg->copyBudget($user->getId(), $ml, $mn, $month, $year);		
		
		break;
		
	default:
		$tpl->assign("page_title","budget all");
		
		$filter['drain'] = '1';
		$linkMonth = "index.php?modules=budget";
		
		if ($g_order == 'notdrain')
		{
			$filter['drain'] = '0';
			$linkMonth = "index.php?modules=budget&order=notdrain";
		}
		
		if (empty($g_month) && empty($_SESSION['budgetCurMonth']))
		{
			$curMonth = date("Y.m");
		}else{
			if (!empty($g_month))
			{
				$curMonth = html($g_month);		
			}else{
				$curMonth = $_SESSION['budgetCurMonth'];
			}
		}
		
		list($year,$month) = explode(".", $curMonth);
		
		$linkLastMonth = $linkMonth."&month=".date("Y.m",mktime(0, 0, 0, $month-1, date("d"), $year));
		$linkNextMonth = $linkMonth."&month=".date("Y.m",mktime(0, 0, 0, $month+1, date("d"), $year));
		
		$allBudget = $bdg->getBudgets($filter, $month, $year);
		
		//list($year,$month,$day) = explode(".", $budget['date_from']);			
		//$budget['period'] = $sys_month[$month];
		
		$tpl->assign('curMonth', $sys_month[$month]."&nbsp;".$year);
		
		if (empty($allBudget))
		{
			$checkBudget = $bdg->checkBudget($user->getId(), $month, $year);
			if (!empty($checkBudget))
			{
				$tpl->assign('copyBudget', "Скопировать план бюджета с прошлого месяца");		
			}
		}
		if ($filter['drain'] == 1)
		{
			for ($i=0; $i<count($allBudget); $i++)
			{
				$allBudget[$i]['delta'] = round(($allBudget[$i]['money']-$allBudget[$i]['fact'])/$allBudget[$i]['money']*100,2);
				if ($allBudget[$i]['delta'] > 0)
				{
					$allBudget[$i]['delta'] = "<font color=green>&nbsp;".$allBudget[$i]['delta']."%</font>";
				}else{
					$allBudget[$i]['delta'] = "<font color=#bc5f5f>".$allBudget[$i]['delta']."%</font>";
				}
			}
		}
		if ($filter['drain'] == 0)
		{
			for ($i=0; $i<count($allBudget); $i++)
			{
				$allBudget[$i]['delta'] = round(($allBudget[$i]['fact']-$allBudget[$i]['money'])/$allBudget[$i]['money']*100,2);
				if ($allBudget[$i]['delta'] > 0)
				{
					$allBudget[$i]['delta'] = "<font color=green>&nbsp;".$allBudget[$i]['delta']."%</font>";
				}else{
					$allBudget[$i]['delta'] = "<font color=#bc5f5f>".$allBudget[$i]['delta']."%</font>";
				}
			}
		}
		//pre($allBudget);
		$tpl->assign('budgets', $allBudget);
		$tpl->assign('filterDrain', $filter['drain']);
		$tpl->assign('linkLastMonth', $linkLastMonth);
		$tpl->assign('linkNextMonth', $linkNextMonth);
		
		if ($_SESSION['good_text'])
		{
			$tpl->assign('good_text', $_SESSION['good_text']);
			$_SESSION['good_text'] = false;
		}
		
		//pre($total);
		break;
}
?>