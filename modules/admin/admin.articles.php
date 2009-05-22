<?php

	if (!empty($_POST['title']))
	{
		$title = html($_POST['title']);	
		$description = html($_POST['description']);
		$date = html($_POST['date']);
		$article = html($_POST['article']);
		
		$sql = "insert into `articles` (`id`, `title`, `description`, `article`, `date`) value ('', '".$title."', '".$description."', '".$article."', '".$date."')";
		$db->sql_query($sql);
		header("location: index.php?modules=admin&action=manage_articles");
		exit;
	}
	
	if (!empty($_GET['del']))
	{
		$sql = "delete from `articles` where id = '".html($_GET['del'])."'";
		$db->sql_query($sql);
		header("location: index.php?modules=admin&action=manage_articles");
		exit;
	}
	
	$sql = "select *, DATE_FORMAT(date,'%d.%m.%Y') as date_f from articles order by `date` desc limit 0,20";
	$db->sql_query($sql);
	$row = $db->sql_fetchrowset($result);
	
	$tpl->assign('articles', $row);
	$tpl->assign('date', date("Y-m-d"));
	$tpl->assign('name_page', 'admin/admin.articles');
?>