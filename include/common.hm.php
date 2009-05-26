<?
/**
* file: common.php
* author: Roman Korostov
* date: 23/01/07
**/

require_once("config.hm.php");
require_once SYS_DIR_LIBS.'/db.class.php';
require_once SYS_DIR_LIBS.'/user.class.php';
require_once SYS_DIR_LIBS.'/category.class.php';
require_once SYS_DIR_LIBS.'/account.class.php';
require_once SYS_DIR_LIBS.'/budget.class.php';
require_once SYS_DIR_LIBS.'/periodic_transaction.class.php';
//require_once SYS_DIR_LIBS.'/news.class.php';
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

global $sql_db;
$sql_db = $db = new sql_db(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PASS, SYS_DB_BASE);

if(!$db->db_connect_id)
{
	trigger_error("Could not connect to the database", E_USER_ERROR);
}

$user = new User($db);

$cat = new Category($db, $user);

$acc = new Account($db, $user);

$bdg = new Budget($db, $user);

$prt = new Periodic($db, $user);



// вытаскиваем курсы валют из базы и обновляем системный массив валют
$sql = "select * from daily_currency where currency_date = '".date("Y-m-d")."'";
$db->sql_query($sql);
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

?>