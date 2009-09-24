<?php
/**
 * Индексный файл проекта
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
 
define('INDEX', true);

// Загружаем общие данные
require_once dirname(dirname(__FILE__)). "/include/common.php";

Core::getInstance()->authUser(); 
Core::getInstance()->parseUrl();

// Определяем информацию о пользователе
//@TODO Переместить это в другой блок ()
$uar = array(
    'user_id'=>Core::getInstance()->user->getId(),
    'user_name'=>$_SESSION['user']['user_name'],
    'user_type'=>$_SESSION['user']['user_type']);

Core::getInstance()->tpl->assign('user_info', $uar);

//Выводим страницу в браузер
//@TODO Тут можно организовать отдачу данных в gzip, если поддерживается сжатие на клиенте
Core::getInstance()->tpl->display("index.html");