<?php
/**
 * Индексный файл проекта
 * @copyright http://easyfinance.ru/
 * @version SVN $Id: index.php 1021 2009-10-29 14:27:15Z ukko $
 */

//include('kd_xmlrpc.php');
define('INDEX',1);
require_once "../../include/config.php";
//require_once SYS_DIR_INC.'/functions.php';


if (DEBUG) {
    // В режиме DEBUG выводим отладочные сообщения в консоль firebug < http://getfirebug.com/ >
    //через плагин firephp < http://www.firephp.org/ >
    require_once SYS_DIR_LIBS . 'external/FirePHPCore/FirePHP.class.php';
}

// Подгружаем внешние библиотеки
require_once SYS_DIR_LIBS . 'external/DBSimple/Mysql.php';

// Устанавливаем обработчик ошибок
//set_error_handler("UserErrorHandler");

define ('HOWMUCH',12);

// Регистрация автолоадера
spl_autoload_register('__autoload');
function __autoload($class_name){
    //echo ($class_name);
    //$array = explode("_",$class_name);
    // Грузим контроллеры
    //echo (SYS_DIR_ROOT .'/classes/Sync/' . $class_name . '.php');
    if ( file_exists(SYS_DIR_ROOT .'/classes/Sync/' . $class_name . '.php' ) ) {
            require_once SYS_DIR_ROOT .'/classes/Sync/' . $class_name . '.php';
    }
    $pos = strripos($class_name, 'Model');
    $str = substr($class_name, 0, $pos-1);
    //echo(SYS_DIR_ROOT .'/classes/Sync/'. $str. 'Model' . '.php');
    if ( file_exists (SYS_DIR_ROOT .'/classes/Sync/'. $str. 'Model' . '.php') )
        require_once SYS_DIR_ROOT .'/classes/Sync/'. $str. 'Model' . '.php';
}


include (SYS_DIR_ROOT . "/classes/Sync/RecordsMap/RecordsMapModel.php");

/**
 * Форматирование даты
 * @param string $str
 * @return string
 */
function formatIsoDateToNormal($str)
{
    /*if ( substr($str, 8) !== 'T' ) {
        return substr($str, 0, 4).'-'.substr($str, 4, 2).'-'.substr($str, 6, 2);
    } else {
        return substr($str, 0, 4) . '-' . substr($str, 4, 2) . '-' . substr($str, 6, 2) . ' '
            . substr($str, 9,2) . ':' . substr($str, 12,2).':' . substr($str,15,2);
    }*/
    return substr($str, 6, 4).'-'.substr($str, 3, 2).'-'.substr($str, 0, 2);
}


//sync_getAuth($xmlRequest);
 DEFINE(INDEX, 1);
 include ('../../include/config.php');
include(SYS_DIR_LIBS."external/php_xmlrpc/lib/xmlrpc.inc");
include(SYS_DIR_LIBS."external/php_xmlrpc/lib/xmlrpcs.inc");
include(SYS_DIR_LIBS."external/php_xmlrpc/lib/xmlrpc_wrappers.inc");
$GLOBALS['xmlrpc_internalencoding']='UTF-8';

function sync_getAuth($xmlRequest){
    $a = New Sync($xmlRequest, $xmlAnswer);
    $answer = $a->qwe($xmlRequest, $xmlAnswer, 0);
    return $answer;

}
function sync_getAuthWithTestData($xmlRequest){
    include ("../../classes/Sync/zaglushka.php");
    $a = New Sync($xmlRequest, $xmlAnswer);
    $answer = $a->qwe($xmlRequest, $xmlAnswer, 1);
    return $answer;
}
function sync_clearAcc($xmlRequest){
    $a = New Sync($xmlRequest, $xmlAnswer);
    $answer = $a->deleteAllByUser($xmlRequest);
    return $answer;
}
function sync_clearRecMap($xmlRequest){
    $a = New Sync($xmlRequest, $xmlAnswer);
    $answer = $a->deleteRecMapByUser($xmlRequest);
    return $answer;
}
function sync_writeData($xmlRequest){
    $a = New Sync($xmlRequest, $xmlAnswer);
    $answer = $a->writeDataAndAnswerRec($xmlRequest);
    return $answer;
}
$a=array(
    "sync.getAuth" => array(
            "function" => "sync_getAuth",
    ),
    "sync.getAuthWithTestData" => array(
            "function" => "sync_getAuthWithTestData",
    ),
    "sync.clearAcc" => array(
            "function" => "sync_clearAcc",
    ),
    "sync.clearRecMap" => array(
            "function" => "sync_clearRecMap",
    ),
    "sync.writeData" => array(
            "function" => "sync_writeData",
    )
);

$s=new xmlrpc_server($a, false);
$s->setdebug(3);
$s->response_charset_encoding='UTF-8';
$s->compress_response = true;
$s->service();
