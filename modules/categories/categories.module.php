<?php
/**
* file: categories.module.php
* author: Roman Korostov
* date: 30/11/08	
**/

// подключаем все необходимые библиотеки
require_once (SYS_DIR_LIBS . "external/DBSimple/Mysql.php");
require_once (SYS_DIR_LIBS . "categories.class.php");

// если пользователь не авторизован, фигачим его на главную страницу
if (empty($_SESSION['user'])) {
	if (!$_GET['ajax'])
	{
    	header("Location: index.php");
	}else{
		echo "Пожалуйста, перезайдите...";	
		exit;
	}
}

// Инициируем контроллер категорий. Всё в нём.
try {	
	// создаем объект класса DB_Simple
	$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
	$dbs->query("SET character_set_client = 'utf8', 
				character_set_connection = 'utf8', 
				character_set_results = 'utf8'");
	
	$cc = new CategoriesClass($dbs, $_SESSION['user']['user_id']);	
	//$cc->loadUserTree();
	
} catch (Exception $e) {
	message_error(CRITICAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
}


// блок фильтра
if (isset($_GET['filtr_visible']) and $_GET['filtr_visible'] != "")
{
	$visible = html($_GET['filtr_visible']);
	$filtr = "and 1=1";
	if ($visible == 0)
	{
		$filtr = "and c.visible=0";	
	}
	
	if ($visible == 1)
	{
		$filtr = "and c.visible=1";	
	}
	$_SESSION['categories_filtr'] = $filtr;
}

if (isset($_GET['filtr_type']) and $_GET['filtr_type'] != "")
{
	$type = html($_GET['filtr_type']);
	
	switch ($type)
	{
		case "0":
			$filtr = " and c.type = 0";
		break;
		case "1":
			$filtr = " and c.type = 1";
		break;
		case "2":
			$filtr = " and c.type = 2";
		break;
		default:
			$filtr = "";
		break;
	}
	$_SESSION['categories_filtr'] .= $filtr;
}

if (isset($_GET['filtr_period']) and $_GET['filtr_period'] != "")		
{

$period = html($_GET['filtr_period']);

switch ($period)
		{
			case "week":
				$week=date("W");
				$sun_day=7*$week-date("w",mktime(0,0,0,1,7*$week));
	
				$date['start'] = date("Y-m-d",mktime(0,0,0,1,$sun_day-6));
				$date['finish'] = date("Y-m-d",mktime(0,0,0,1,$sun_day));
				$param = " and m.date > '".$date['start']."' and m.date < '".$date['finish']."'";
			break;
			
			case "year":
				$date['start'] = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
				$date['finish'] = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")+1));
				$param = " and m.date > '".$date['start']."' and m.date < '".$date['finish']."'";
			break;
			
			case "all":
				$param = "";
			break;
			
			default:
				$date['start'] = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
				$date['finish'] = date("Y-m-d", mktime(0, 0, 0, date("m")+1, "01", date("Y")));
				$param = " and m.date > '".$date['start']."' and m.date < '".$date['finish']."'";
			break;
		}
}

// получаем действие
$action = html($g_action);

switch ($action) {
	
	case "create_new_category":
		$category['user_id'] = $_SESSION['user']['user_id'];
		$category['cat_name'] = @html($_GET['name']);
		$category['type'] = @html($_GET['type']);
		$category['cat_parent'] = @html($_GET['parent']);
		$category['system_category_id'] = @html($_GET['system']);
		$category['cat_id'] = @html($_GET['category_id']);
		$category['visible'] = 1;
		$category['cat_active'] = 1;
		$category['often'] = @html($_GET['often']);

		if (!empty($category['cat_id']))
		{
			$cc->updateCategory($category, &$dbs);
		}else{
			if (!$cc->createNewCategory($category, &$dbs))
			{
				$tpl->assign("error", "Категория не добавлена");
			}
		}
		$cc->loadUserTree();
		$cc->loadSumCategories($sys_currency);
				
		$tpl->assign("categories", $cc->tree);
		$tpl->assign("sys_categories", $cc->system_categories);
		echo $tpl->fetch("categories/categories.list.html");		
		exit;
	break;
	
	case "edit_category":
		$id = html($_GET['id']);
		
		$edit = $cc->selectCategoryId($id, &$dbs);

		$tpl->assign("edit", $edit[0]);
		$tpl->assign("categories", $cc->tree);
		$tpl->assign("sys_categories", $cc->system_categories);
		echo $tpl->fetch("categories/categories.block_create.html");
		exit;
	break;
	
	case "delete_category":
		$id = html($_GET['id']);
		if (!$cc->deleteCategory($id, &$dbs))
		{
			$tpl->assign("error", "Категория не удалена");
		}
		$cc->loadUserTree();
		$cc->loadSumCategories($sys_currency);
		
		$tpl->assign("categories", $cc->tree);
		echo $tpl->fetch("categories/categories.list.html");
		exit;
	break;
	
	case "visible_category":
		$id = html($_GET['id']);
		$visible = html($_GET['visible']);
		
		if (!$cc->visibleCategory($id, $visible, &$dbs))
		{
			$tpl->assign("error", "Категория не скрыта");
		}
		$cc->loadUserTree();
		$cc->loadSumCategories($sys_currency);
		
		$tpl->assign("categories", $cc->tree);
		echo $tpl->fetch("categories/categories.list.html");
		exit;
	break;
	
	/*case "change_type_categories":
		$tpl->assign("filtr_type", html($_GET['type']));
		
		$tpl->assign("categories", $cc->tree);
		$tpl->assign("sys_categories", $cc->system_categories);
		echo $tpl->fetch("categories/categories.filtr_list.html");		
		exit;
	break;*/
	
	case "change_filtr":
				
		$cc->loadUserTree();
		$cc->loadSumCategories($sys_currency, $param);
		
		$tpl->assign("categories", $cc->tree);
		$tpl->assign("sys_categories", $cc->system_categories);
		echo $tpl->fetch("categories/categories.list.html");		
		exit;
	break;
	
	/*case "change_period_fact":
		
		$period = html($_GET['period']);
		
		
		
		$cc->loadSumCategories($sys_currency, $param);
		
		$tpl->assign("categories", $cc->tree);
		$tpl->assign("sys_categories", $cc->system_categories);
		echo $tpl->fetch("categories/categories.list.html");		
		exit;
	break;*/
	
	case "reload_block_create":
		$tpl->assign("categories", $cc->tree);
		$tpl->assign("sys_categories", $cc->system_categories);
		echo $tpl->fetch("categories/categories.block_create.html");
		exit;
	break;
	
	default:
		$date['start'] = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
		$date['finish'] = date("Y-m-d", mktime(0, 0, 0, date("m")+1, "01", date("Y")));
		$param = "and m.date > '".$date['start']."' and m.date < '".$date['finish']."'";

		$cc->loadUserTree();
		$cc->loadSumCategories($sys_currency, $param);
		
		$tpl->assign("categories", $cc->tree);		
		$tpl->assign("sys_categories", $cc->system_categories);
        $tpl->assign("template", "default");
	break;
}

$tpl->assign('name_page', 'categories/categories');
?>
