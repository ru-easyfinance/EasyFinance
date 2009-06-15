<?php
/**
 * Индексный файл проекта
 * SVN $Id$
 * @copyright http://home-money.ru/
 */

//XXX WTF???
//if (isset($_GET['et']) && $_GET['et'] == 'on') {
//    //FIXME Редиректить на красивую страничку
//    trigger_error("На сайте проводятся технические работы. Зайдите позже.", E_USER_ERROR);
//}

define('INDEX',true);

session_start();

//print '<pre>';
//print_r($_SESSION);
//print '</pre>';

require_once dirname(dirname(__FILE__)). "/include/common.php";

Core::getInstance()->authUser();
Core::getInstance()->parseUrl();

if (!empty($_SESSION['user'])) {
    $tpl->assign("user", $_SESSION['user']);
}
$tpl->display("index.html");