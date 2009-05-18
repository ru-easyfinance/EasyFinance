<?
/*if (!isset($_GET['ajax']) || !$_GET['ajax']) {
	include("../core/modules/category.module.php");
} else {
	include("../core/modules/ajax/category.ajax.php");
}*/
/**
* file: category.php
* author: Roman Korostov
* date: 9/03/07	
**/

if (empty($_SESSION['user']))
{
	header("Location: index.php");
}

$tpl->assign('name_page', 'category');

$action = html($g_action);

switch( $action )
{
	case "add":
		$tpl->assign("page_title","category add");			
		$tpl->assign('categories', $_SESSION['user_category']);

		if (!empty($p_cat))
		{
			if(isset($p_cat['parent']))
			{
				$category['parent'] = $p_cat['parent'];
			}
			
			if (!empty($p_cat['name']))
			{
				$category['name'] = html($p_cat['name']);
			}
			else
			{
				$error_text['name'] = "Название категории не должно быть пустым!";
			}
				
			$category['user_id'] = $user->getId();
			
			if (empty($error_text))
			{
				if($cat->saveCategory($p_cat['parent'], $category['name']))
				{
					//$tpl->assign('good_text', "Запись сохранена!");
					$_SESSION['good_text'] = "Категория сохранена!";
					header("Location: index.php?modules=category");
				}
			}
			else
			{
				$tpl->assign('error_text', $error_text);
				$tpl->assign('category', $category);
			}			
		}
		
		break;
	case "edit":	
		$tpl->assign("page_title","category edit");			
		$tpl->assign('categories', $_SESSION['user_category']);

		if (!empty($p_cat))
		{
			if(isset($p_cat['parent']))
			{
				$category['parent'] = $p_cat['parent'];
			}
			
			if (!empty($p_cat['name']))
			{
				$category['name'] = html($p_cat['name']);
			}
			else
			{
				$error_text['name'] = "Название категории не должно быть пустым!";
			}
			

			if (empty($error_text))
			{
				if($cat->updateCategory($p_cat['id'],$category['parent'], $category['name']))
				{
					//$tpl->assign('good_text', "Запись сохранена!");
					$_SESSION['good_text'] = "Категория изменена!";
					header("Location: index.php?modules=category");
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
				$category = $cat->selectCategory(html($g_id));
			}
			$tpl->assign('in_category', html($g_id));	
			$tpl->assign('category', $category[0]);	
		}
		
		break;
	case "del":	
		$tpl->assign("page_title","category del");

		if (isset($p_cat['id']) && is_numeric($p_cat['id']))
		{			
			if($cat->deleteCategory(html($p_cat['id'])))
			{
				$_SESSION['good_text'] = "Категория удалена!";
				header("Location: index.php?modules=category");
				exit;
				//$tpl->assign('good_text', "Запись удалена!");
			}	
		}
		else
		{
			message_error(GENERAL_ERROR, "Получен неверный параметр!");
		}
		
		break;
	case "get":	
		if (empty($_SESSION['user_category']))
		{		
			if($user->getCategory($user->getId()))
			{
				header("Location: index.php?modules=category");
			}	
			else
			{
				message_error(GENERAL_ERROR, "Справочник не загружен!");
			}
		}else{
			header("Location: index.php?modules=category");
		}
		
		break;
	default:
		//header("Location: index.php?modules=category");
		if (!empty($_SESSION['user_category']))
		{
			$three = get_three($_SESSION['user_category']);			
			$tpl->assign('categories', $three);			
		}
		else
		{
			$tpl->assign('categories', false);
		}
		
		if ($_SESSION['good_text'])
		{
			$tpl->assign('good_text', $_SESSION['good_text']);
			$_SESSION['good_text'] = false;
		}
			
		break;
}
?>