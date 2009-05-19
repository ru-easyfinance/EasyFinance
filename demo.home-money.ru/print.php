<?
/**
* file: print.php
* author: Roman Korostov
* date: 18/06/07	
**/

require_once ("../include/common.php");

if (empty($_SESSION['user']))
{
	header("Location: index.php");
	exit;
}

$tpl->assign("print_report", $_SESSION['print_report']);

$tpl->display("print.html");

?>