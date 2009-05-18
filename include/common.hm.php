<?
/**
 * file: common.php
 * author: Roman Korostov
 * date: 23/01/07
 **/

require_once dirname(__FILE__)."/config.hm.php";

require_once SYS_DIR_LIBS.'external/DBSimple/Mysql.php';
//require_once SYS_DIR_LIBS.'/db.class.php';
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

//FIXME
// преобразуем гет и пост и подставляем префикс g или p
if (isset($_GET)) extract($_GET,EXTR_PREFIX_ALL,'g');
if (isset($_POST)) extract($_POST,EXTR_PREFIX_ALL,'p');

session_start();

$tpl = new Smarty();

if ($_SESSION['pda'] == 'on') 
{
    $tpl->template_dir =  SYS_DIR_ROOT.'/templates_pda';
    $tpl->compile_dir  =  SYS_DIR_ROOT.'/cache_pda';
} else {
    $tpl->template_dir =  SYS_DIR_ROOT.'/templates';
    $tpl->compile_dir  =  SYS_DIR_ROOT.'/cache';
}

$tpl->plugins_dir      =  array(SYS_DIR_INC.'/smarty/plugins');
$tpl->compile_check    =  true;
$tpl->force_compile    =  false;

$db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
$db->query("SET character_set_client = 'utf8', 
            character_set_connection = 'utf8', 
            character_set_results = 'utf8'");

$user = new User($db);

$cat = new Category($db, $user);

$acc = new Account($db, $user);

$bdg = new Budget($db, $user);

$prt = new Periodic($db, $user);

// вытаскиваем курсы валют из базы и обновляем системный массив валют 
//$sys_currency = $db->select("SELECT * FROM daily_currency WHERE currency_date=NOW()"); 
$sys_currency = $db->select("SELECT * FROM daily_currency WHERE currency_date='2009-05-10'"); //FIXME
foreach ($sys_currency as $key => $value) {
    for($i= 0; $i < count($row_d); $i++) {
        if ($row_d[$i]['currency_id'] == $key) {
            $sys_currency[$key] = $row_d[$i]['currency_sum'];
        }
    }
}