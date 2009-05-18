<?
/**
* file: orders.php
* author: Roman Korostov
* date: 7/03/07	
**/

require_once SYS_DIR_LIBS.'/orders.class.php';

$orders = new Orders($db, $user);

	$tpl->assign('name_page', 'orders');
?>