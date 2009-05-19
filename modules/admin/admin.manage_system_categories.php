<?php

if (!empty($_POST['save_group']))
{
	$name = html($_POST['group_name']);
	
	$sql = "insert into `system_categories_group` (`system_group_id`,`system_group_name`) value ('', '$name')";
	if ($db->sql_query($sql))
	{
		header("location: index.php?modules=admin&action=manage_system_categories");	
	}else{
		$tpl->assign("t_error", mysql_error());
	}
}

if (!empty($_POST['save_cat']))
{
	$name = html($_POST['cat_name']);
	$group = html($_POST['sys_category']);
	
	if (!empty($_POST['cat_id']))
	{
		$id = html($_POST['cat_id']);
		$sql = "update `system_categories` set `system_group_id` = '$group', `system_category_name` = '$name' 
				where `system_category_id` = $id";
		if ($db->sql_query($sql))
		{
			header("location: index.php?modules=admin&action=manage_system_categories");	
		}else{
			$tpl->assign("t_error", mysql_error());
		}
	}else{
		$sql = "insert into `system_categories` (`system_category_id`,`system_group_id`, `system_category_name`) value ('', '$group', '$name')";
		if ($db->sql_query($sql))
		{
			header("location: index.php?modules=admin&action=manage_system_categories");	
		}else{
			$tpl->assign("t_error", mysql_error());
		}
	}
}


$sql = "SELECT cc.*, cg.system_group_name from system_categories cc
		left join system_categories_group cg on cc.system_group_id = cg.system_group_id";

$db->sql_query($sql);
$row_c = $db->sql_fetchrowset($result);

$sql = "select * from system_categories_group";
$db->sql_query($sql);
$row_g = $db->sql_fetchrowset($result);

$tpl->assign("categories", $row_c);
$tpl->assign("groups", $row_g);

if (!empty($_GET['id']))
{
	$id = html($_GET['id']);
	$sql = "select * from system_categories where system_category_id = '".$id."'";
	$db->sql_query($sql);
	$tpl->assign("select_cat", $db->sql_fetchrow($result));
}
?>