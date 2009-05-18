<?
/**
* file: welcam.php
* author: Roman Korostov
* date: 28/03/07	
**/

if (empty($_SESSION['user']))
{
	header("Location: index.php");
}

$tpl->assign('name', $_SESSION['user']['user_name']);
$tpl->assign('name_page', 'first_start');

?>