<?php
/**
 * Индексный файл проекта
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
 
define('INDEX', true);

// Загружаем общие данные
require_once dirname(dirname(__FILE__)). "/include/common.php";

Core::getInstance()->authUser(); 
Core::getInstance()->parseUrl();

// Определяем информацию о пользователе
//@TODO Переместить это в другой блок ()
if (Core::getInstance()->user->getId()) {
    $uar = array(
        'user_id'=>Core::getInstance()->user->getId(),
        'user_name'=>$_SESSION['user']['user_name'],
        'user_type'=>$_SESSION['user']['user_type']);
    Core::getInstance()->tpl->assign('user_info', $uar);
}

//Выводим страницу в браузер
Core::getInstance()->tpl->display("index.html");