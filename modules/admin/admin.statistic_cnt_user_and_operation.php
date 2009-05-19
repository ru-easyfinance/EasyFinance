<?php

$sql = "SELECT count(id) as cnt, DATE_FORMAT(date,'%m.%Y') as date_f, date FROM money 
			where date > '2008-01-01' and date < '2010-01-01' 
		group by  DATE_FORMAT(date,'%m.%Y') order by date";
$db->sql_query($sql);
$row_o = $db->sql_fetchrowset($result);

$sql = "SELECT count(user_id) as cnt, DATE_FORMAT(user_created,'%m.%Y') as date_f, user_created FROM users 
			where user_created > '2008-01-01' and user_created < '2010-01-01' and user_active=1 
		group by  DATE_FORMAT(user_created,'%m.%Y') order by user_created";
$db->sql_query($sql);
$row_u = $db->sql_fetchrowset($result);


$tpl->assign("operations", $row_o);
$tpl->assign("users", $row_u);

?>