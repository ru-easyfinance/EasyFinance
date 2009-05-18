<?php

$tpl->assign('name_page', 'article');

$action = html($g_action);

$tpl->assign("page_title","news");			
			
$id = html($g_id);

$sql = "select * from articles where id = '".$id."'";
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);

$tpl->assign("article",$row);
?>