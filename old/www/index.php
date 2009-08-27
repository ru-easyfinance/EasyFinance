<?php
/**
 * Индексный файл проекта
 * @copyright http://home-money.ru/
 * @version SVN $Id: index.php 287 2009-08-26 08:01:52Z rewle $
 */
 
define('INDEX', true);

// @FIXME не создавать сессию, если на странице случайный посетитель
session_start();

// Загружаем общие данные
require_once dirname(dirname(__FILE__)). "/include/common.php";

Core::getInstance()->authUser();
Core::getInstance()->parseUrl();

$uar = array('user_name'=>$_SESSION['user']['user_name'],'user_type'=>$_SESSION['user']['user_type']);
Core::getInstance()->tpl->assign('user_info', $uar);
Core::getInstance()->tpl->display("index.html");