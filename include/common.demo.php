<?
/**
* file: common.php
* author: Roman Korostov
* date: 23/01/07	
**/

require_once("config.demo.php");
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

//$news = new News($db, $user);

if (!empty($_SESSION['user']['user_id']))
{
	$page = html($_GET['modules']);
	$action = html($_GET['action']);
	
	$sql = "INSERT INTO `statistic` 
						(`id`, `user_id`, `page`, `action`, `date_time`)
					VALUES
						('', '".$_SESSION['user']['user_id']."', '".$page."', '".$action."', '".date("Y-m-d H:i:s")."')
					";
			
	$result = $db->sql_query($sql);
	if ($_GET['et']=='on')
	{
		echo mysql_error();
	}
}

// вытаскиваем курсы валют из базы и обновляем системный массив валют
$sql = "select * from daily_currency where currency_date = '".date("Y-m-d")."'";
$result = $db->sql_query($sql);
$row_d = $db->sql_fetchrowset($result);

foreach ($sys_currency as $key=>$value)
{
	for($i=0; $i<count($row_d);$i++)
	{
		if ($row_d[$i]['currency_id'] == $key)
		{
			$sys_currency[$key] = $row_d[$i]['currency_sum'];
		}
	}
}

//$news_date = date("Y.m.d");
//$tpl->assign('last_news', $news->getTitleNews(5, $news_date));
//pre($_SESSION);
?>