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
switch ( $_SERVER['HTTP_HOST'].'/' ) {
    case URL_ROOT_IFRAME:
        if ( ( ! Core::getInstance()->user->getId() ) AND ($_SERVER['REQUEST_URI'] != "/login/" ) ) {
            header("Location: https://iframe." . URL_ROOT_MAIN . "login/");
        }
        Core::getInstance()->tpl->assign('template_view', 'iframe');
        Core::getInstance()->tpl->display("iframe/index.iframe.html");
        break;
    default:
        Core::getInstance()->tpl->assign('template_view', 'index');
        Core::getInstance()->tpl->display("index.html");
        break;
}

// Применение модификаций\удалений моделей
_Core_ObjectWatcher::getInstance()->performOperations();
