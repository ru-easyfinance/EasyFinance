<?php
/**
 * Индексный файл проекта
 * @copyright http://easyfinance.ru/
 * @version SVN $Id: index.php 1021 2009-10-29 14:27:15Z ukko $
 */

//include('kd_xmlrpc.php');
define('INDEX',1);
require_once "../../include/config.php";
require_once SYS_DIR_INC.'/functions.php';


if (DEBUG) {
    // В режиме DEBUG выводим отладочные сообщения в консоль firebug < http://getfirebug.com/ > через плагин firephp < http://www.firephp.org/ >
    require_once SYS_DIR_LIBS . 'external/FirePHPCore/FirePHP.class.php';
}

// Подгружаем внешние библиотеки
require_once SYS_DIR_LIBS . 'external/DBSimple/Mysql.php';

// Устанавливаем обработчик ошибок
set_error_handler("UserErrorHandler");

define ('HOWMUCH',12);

//$xmlrpc_methods['method_not_found'] = XMLRPC_method_not_found;
//$synchhh = new Sync();


//include ("../Account/Model.php");
include ("../../classes/Account/Model.php");
$xmlrpc_methods = array();
$xmlrpc_methods['sync.getAuth'] = 'sync_getAuth';
//$xmlrpc_methods['sync.getRecordsMap'] = sync.getRecordsMap;
sync_getAuth();
function sync_getAuth(){
    include ('../../classes/Sync/Sync.php');
    $a = new Sync();

}