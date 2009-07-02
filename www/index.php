<?php
/**
 * Индексный файл проекта
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */

//XXX WTF???
//if (isset($_GET['et']) && $_GET['et'] == 'on') {
//    //FIXME Редиректить на красивую страничку
//    trigger_error("На сайте проводятся технические работы. Зайдите позже.", E_USER_ERROR);
//}

define('INDEX',true);

// @FIXME не создавать сессию, если на странице случайный посетитель
session_start();

// Загружаем общие данные
require_once dirname(dirname(__FILE__)). "/include/common.php";

Core::getInstance()->authUser();
Core::getInstance()->parseUrl();

if (!empty($_SESSION['user'])) {
    $tpl->assign("user", $_SESSION['user']);
}
$tpl->display("index.html");