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
        if (( substr($_SERVER['REQUEST_URI'], 0, 14) == "/login/azbuka/") && ( substr($_SERVER['QUERY_STRING'],0,5) == 'id_ef')){
            $select = Login_Model::getUserDataByID( substr($_SERVER[argv][0], 6) );
            $uar = array(
                'user_id'=>substr($_SERVER[argv][0], 6),
                'user_name'=>$select[0]['user_login'],
                'user_type'=>0);
            Core::getInstance()->tpl->assign('user_info', $uar);
            Core::getInstance()->tpl->assign('template_view', 'iframe');
            setcookie(COOKIE_NAME, encrypt(array($select[0]['user_login'],$select[0]['user_pass'])), time() + COOKIE_EXPIRE, COOKIE_PATH, 'iframe'.COOKIE_DOMEN, COOKIE_HTTPS);
            header("Location: https://iframe." . URL_ROOT_MAIN . "info/");
            break;
        }
        if (( substr($_SERVER['REQUEST_URI'], 0, 14) == "/login/azbuka/") && ( substr($_SERVER['QUERY_STRING'],0,5) == 'login')){
            $newId = Login_Model::generateUserByAzbukaLogin( substr($_SERVER[argv][0], 6) );
            return $newId;
            //break;
        }
        if ( ( ! Core::getInstance()->user->getId() ) AND ($_SERVER['REQUEST_URI'] != "/login/" ) ) {
	    if ( $_SERVER['REQUEST_URI'] != '/registration/' &&  $_SERVER['REQUEST_URI'] != '/restore/') {
	            header("Location: https://iframe." . URL_ROOT_MAIN . "login/");
	    }
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
