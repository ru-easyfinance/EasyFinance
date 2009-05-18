<?
/**
* file: common.php
* author: Roman Korostov
* date: 23/01/07	
**/

require_once("config.php");
require_once SYS_DIR_LIBS.'/db.class.php';
require_once SYS_DIR_LIBS.'/user.class.php';
require_once SYS_DIR_LIBS.'/category.class.php';
require_once SYS_DIR_LIBS.'/account.class.php';
require_once SYS_DIR_LIBS.'/budget.class.php';
require_once SYS_DIR_LIBS.'/periodic_transaction.class.php';
require_once SYS_DIR_LIBS.'/news.class.php';
require_once SYS_DIR_INC.'/functions.php';
require_once SYS_DIR_INC.'/smarty/Smarty.class.php';
require_once SYS_DIR_INC.'/smarty/Smarty_Compiler.class.php';
require_once SYS_DIR_INC.'/smarty/Config_File.class.php';

// преобразуем гет и пост и подставляем префикс g или p
if (isset($_GET)) extract($_GET,EXTR_PREFIX_ALL,'g');
if (isset($_POST)) extract($_POST,EXTR_PREFIX_ALL,'p');

session_start();

$tpl = new Smarty();


if ($_SESSION['pda'] == 'on')
{
	$tpl->template_dir    =  SYS_DIR_ROOT.'/templates_pda';
	$tpl->compile_dir     =  SYS_DIR_ROOT.'/cache_pda';
}else{
		$tpl->template_dir    =  SYS_DIR_ROOT.'/templates';
		$tpl->compile_dir     =  SYS_DIR_ROOT.'/cache';
}

$tpl->plugins_dir     =  array(SYS_DIR_INC.'/smarty/plugins');
$tpl->compile_check   =  true;
$tpl->force_compile   =  false;


$db = new sql_db(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PASS, SYS_DB_BASE);

if(!$db->db_connect_id)
{
	message_error(CRITICAL_ERROR, "Could not connect to the database");
}

$user = new User($db);

$cat = new Category($db, $user);

$acc = new Account($db, $user);

$bdg = new Budget($db, $user);

$prt = new Periodic($db, $user);

$news = new News($db, $user);

$news_date = date("Y.m.d");
$tpl->assign('last_news', $news->getTitleNews(10, $news_date));
//pre($_SESSION);
?>