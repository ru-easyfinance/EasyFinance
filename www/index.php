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

require_once dirname(dirname(__FILE__)). "/include/common.php";
Core::getInstance()->parseUrl();
// Всякое другое
session_start();

//XXX Разобраться с $_SESSION['user']
//if (!empty($_SESSION['user'])) {
//    $tpl->assign("user", $_SESSION['user']);
//    $tpl->display("index.hm.html");
//} else{
    $tpl->display("index.html");
//}