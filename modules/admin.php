<?php
/**
 * Отдельная панель для администратора
 */

// подключаем все необходимые библиотеки
require_once (SYS_DIR_LIBS . "classes/hmExpertSystem.class.php");
require_once (SYS_DIR_LIBS . "external/DBSimple/Mysql.php");

// если пользователь не авторизован, фигачим его на главную страницу
if (empty($_SESSION['user'])) {
    header("Location: index.php");
}

$access = array('Etwas',
				'demo');

if (!in_array($_SESSION['user']['user_login'],$access))
{
	header("Location: index.php");	
}

// получаем действие
$action = html($g_action);

$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);

switch ($action) {
	case "subscribe":
		$tpl->assign('name_page', 'admin/admin.subscribe');
		require_once (SYS_DIR_MOD . "admin/admin.subscribe.php");
	break;
	
	case "statistic_cnt_user_and_operation":
		$tpl->assign('name_page', 'admin/admin.statistic_cnt_user_and_operation');
		require_once (SYS_DIR_MOD . "admin/admin.statistic_cnt_user_and_operation.php");
	break;
	
	case "manage_system_categories":
		$tpl->assign('name_page', 'admin/admin.manage_system_categories');
		require_once (SYS_DIR_MOD . "admin/admin.manage_system_categories.php");
	break;
	
	case "manage_daily_currency":
		require_once (SYS_DIR_MOD . "admin/admin.manage_daily_currency.php");
	break;
	
	case "get_list_experts":
		// список всех экспертов
		$tpl->assign('name_page', 'admin/admin.list_experts');		
		
		$exps = new hmExpertSystem(&$dbs);
		$tpl->assign("listExperts", $exps->loadListUnactiveExperts());
		break;
	case "active_expert":
		$exps = new hmExpertSystem(&$dbs);
		
		if ($exps->unblockExpert(html($_GET['id']), 1))	{
			header("location: index.php?modules=admin&action=get_list_experts");
		}
		break;
	case "blocked_expert":
		$exps = new hmExpertSystem(&$dbs);
		
		if ($exps->unblockExpert(html($_GET['id']), 0))	{
			header("location: index.php?modules=admin&action=get_list_experts");
		}
		break;
	
	// Список системных категорий для эксперта
	case "get_list_category_for_experts":
		$tpl->assign('name_page', 'admin/admin.list_system_categories');
		
		$exps = new hmExpertSystem(&$dbs);
		$listCategories = $exps->getSystemExpertCategories();
		$id = html($g_id);
		$d_id = html($g_d_id);
		
		$cat_name = html($p_cat_name);
		$cat_id = html($p_cat_id);
		
		if (!empty($cat_name))
		{
			if (!empty($cat_id))
			{
				if ($exps->saveSystemExpertCategories($cat_id, $cat_name, "update"))
				{
					header("location: index.php?modules=admin&action=get_list_category_for_experts");
				}
			}else{
				if ($exps->saveSystemExpertCategories(0, $cat_name, "insert"))
				{
					header("location: index.php?modules=admin&action=get_list_category_for_experts");
				}
			}
		}
		
		if (!empty($d_id))
		{
			if ($exps->deleteSystemExpertCategories($d_id))
			{
				header("location: index.php?modules=admin&action=get_list_category_for_experts");
			}
		}
		
		if (!empty($id))
		{
			for ($i=0; $i<count($listCategories); $i++)
			{
				if ($listCategories[$i]['category_id'] == $id)
				{
					$tpl->assign("category", $listCategories[$i]);
				}
			}
		}
		$tpl->assign("listCategories", $listCategories);
		break;
	// Список системных услуг для эксперта
	case "get_list_cost_for_experts":
		$tpl->assign('name_page', 'admin/admin.list_system_cost');
		
		$exps = new hmExpertSystem(&$dbs);
		$listCosts = $exps->getSystemExpertCost();
		$id = html($g_id);
		$d_id = html($g_d_id);
		
		$cost_name = html($p_cost_name);
		$cost_id = html($p_cost_id);
		
		if (!empty($cost_name))
		{
			if (!empty($cost_id))
			{
				if ($exps->saveSystemExpertCost($cost_id, $cost_name, "update"))
				{
					header("location: index.php?modules=admin&action=get_list_cost_for_experts");
				}
			}else{
				if ($exps->saveSystemExpertCost(0, $cost_name, "insert"))
				{
					header("location: index.php?modules=admin&action=get_list_cost_for_experts");
				}
			}
		}
		
		if (!empty($d_id))
		{
			if ($exps->deleteSystemExpertCost($d_id))
			{
				header("location: index.php?modules=admin&action=get_list_cost_for_experts");
			}
		}
		
		if (!empty($id))
		{
			for ($i=0; $i<count($listCosts); $i++)
			{
				if ($listCosts[$i]['cost_id'] == $id)
				{
					$tpl->assign("cost", $listCosts[$i]);
				}
			}
		}
		$tpl->assign("listCosts", $listCosts);
		break;
	default:
		$tpl->assign('name_page', 'admin/admin');
		break;
}
?>