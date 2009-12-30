<?php
/**
 * Индексный файл проекта
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
 
define('INDEX', true);

// Загружаем общие данные
require_once dirname(dirname(__FILE__)). "/include/common.php";

// Выводим заголовки политики безопастности в IE для поддержки cookies в iframe
if( $_SERVER['HTTP_HOST'].'/' == URL_ROOT_IFRAME)
{
	header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
}

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
        //
        //die(substr($_SERVER['REQUEST_URI'],15,5));
        if (( substr($_SERVER['REQUEST_URI'], 0, 14) == "/login/azbuka/") && ( substr($_SERVER['REQUEST_URI'],15,5) == 'id_ef')){
            $select = Login_Model::getUserDataByID( substr($_SERVER[argv][0], 20) );
            //echo('<pre>');
            $uar = array(
                'user_id'=>substr($_SERVER[argv][0], 20),
                'user_name'=>$select[0]['user_login'],
                'user_type'=>0);
            Core::getInstance()->tpl->assign('user_info', $uar);
            Core::getInstance()->tpl->assign('template_view', 'iframe');
            //die(COOKIE_NAME.' пасс '.encrypt(array($select[0]['user_login'],$select[0]['user_pass'])).' время '.time() + COOKIE_EXPIRE.' путь '.COOKIE_PATH.' домен '.'iframe.'.COOKIE_DOMEN.' хттп '.COOKIE_HTTPS.' всё'.COOKIE_NAME);
            setcookie(COOKIE_NAME, encrypt(array($select[0]['user_login'],$select[0]['user_pass'])), time() + COOKIE_EXPIRE, COOKIE_PATH, 'iframe.'.COOKIE_DOMEN, COOKIE_HTTPS);
            header("Location: https://iframe." . URL_ROOT_MAIN . "info/");
            break;
        }
        if (( substr($_SERVER['REQUEST_URI'], 0, 14) == "/login/azbuka/") && ( substr($_SERVER['REQUEST_URI'],15,5) == 'login')){
            $newId = Login_Model::generateUserByAzbukaLogin( substr($_SERVER[argv][0], 20) );
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
        /*$ch = curl_init('https://test.easyfinance.ru/login/azbuka/?login=biiii');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resp = curl_exec($ch);
        //$resp = 2;
        $a = curl_multi_getcontent($ch);
        //$e = curl_error($ch);
        //die ($e);
        //die ('parampampam'.print_r($resp));
        curl_close($ch);*/
        //header("Location: https://iframe." . URL_ROOT_MAIN . "login/" . (string)$a);
        //die($a);
        Core::getInstance()->tpl->assign('template_view', 'index');
        Core::getInstance()->tpl->display("index.html");
        break;
}

// Применение модификаций\удалений моделей
_Core_ObjectWatcher::getInstance()->performOperations();
