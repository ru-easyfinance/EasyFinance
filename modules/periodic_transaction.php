<?

if (empty($_SESSION['user']))
{
	header("Location: index.php");
	exit;
}

$tpl->assign('name_page', 'periodic_transaction');

$action = html($g_action);

switch( $action )
{
	case "add":
		$tpl->assign("page_title","periodic add");
		$categories_select = get_three_select($_SESSION['user_category']);
		$tpl->assign('categories_select', $categories_select, 0, 0);
		$tpl->assign('bills_select', $_SESSION['user_account']);		
		
		if (!empty($p_periodic))
		{
			$p_periodic['comment'] = html($p_periodic['comment']);
			
			list($day,$month,$year) = explode(".", $p_periodic['date_from']);
			$p_periodic['date_from'] = $year.".".$month.".".$day;
			
			if ($p_periodic['drain'] == '1')
			{
				$p_periodic['money'] = "-".$p_periodic['money'];
			}
			
			if($prt->savePeriodic($p_periodic))
			{				
				$_SESSION['good_text'] = "Регулярная транзакция добавлена!";
				header("Location: index.php?modules=periodic_transaction");
			}
		}
		break;
	case "edit":
		$tpl->assign("page_title","periodic edit");
		
		$tpl->assign('bills_select', $_SESSION['user_account']);
		
		if (isset($g_id) && is_numeric($g_id))
		{
			$getPeriodic = $prt->getSelectPeriodic(html($g_id));
			$pos = strpos($getPeriodic['money'], "-");
			if ($pos !== false)
			{
				$getPeriodic['money'] = substr($getPeriodic['money'],1);
			}
			
			$tpl->assign('periodic', $getPeriodic);
			$categories_select = get_three_select($_SESSION['user_category'], 0, $getPeriodic['cat_id']);
			$tpl->assign('categories_select', $categories_select, 0, 0);
		}
		if (!empty($p_periodic))
		{
			$p_periodic['comment'] = html($p_periodic['comment']);
			
			list($day,$month,$year) = explode(".", $p_periodic['date_from']);
			$p_periodic['date_from'] = $year.".".$month.".".$day;
			
			if ($p_periodic['povtor'] == '-1')
			{
				$p_periodic['povtor_num'] = '0';
			}
			
			if ($p_periodic['drain'] == '1')
			{
				$p_periodic['money'] = "-".$p_periodic['money'];
			}
			
			if($prt->updatePeriodic($p_periodic))
			{				
				$_SESSION['good_text'] = "Регулярная транзакция изменена!";
				header("Location: index.php?modules=periodic_transaction");
			}
		}		
		
		break;
	case "del":	
		$tpl->assign("page_title","periodic del");

		if (isset($p_periodic['id']) && is_numeric($p_periodic['id']))
		{			
			if($prt->deletePeriodic(html($p_periodic['id'])))
			{
				$_SESSION['good_text'] = "Транзакция удалена!";
				header("Location: index.php?modules=periodic_transaction");
			}	
		}
		else
		{
			message_error(GENERAL_ERROR, "Получен неверный параметр!");
		}
		
		break;
	default:
		$tpl->assign('periodic', $prt->getAllPeriodic());
		
		if ($_SESSION['good_text'])
		{
			$tpl->assign('good_text', $_SESSION['good_text']);
			$_SESSION['good_text'] = false;
		}
		
		break;
}

?>