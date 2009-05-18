<?php
/**
 * Индексный файл проекта
 * SVN $Id$
 */

if (isset($_GET['et']) && $_GET['et'] == 'on') {
    //FIXME Редиректить на красивую страничку
    die("На сайте проводятся технические работы. Зайдите позже.");
}

define('INDEX',true);

global $module;
$module = $_GET['modules'];

require_once dirname(dirname(__FILE__)). "/include/common.hm.php";

//если в гете пришел модуль
if (!empty($module)) {
    if (file_exists(SYS_DIR_MOD . "/{$module}.php")) {
        require_once SYS_DIR_MOD . "/{$module}.php";
    } else {
        trigger_error("ModFile '{$module}' is not exists!", E_USER_ERROR);
    }
} else {
    require_once (SYS_DIR_MOD."/".DEFAULT_MODULE.".php");
}

//XXX Разобраться с $_SESSION['user']
if (!empty($_SESSION['user'])) {
    $tpl->assign("user", $_SESSION['user']);
    $tpl->display("index.hm.html");
} else{
    $tpl->display("index.hm.html");
}