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
        if (( substr($_SERVER['REQUEST_URI'],0,7) == "/login/") && ( $_GET['refer'] == 'azbuka' ) && ( isset($_GET['id_ef']) && ( isset($_GET['session_key'])) )){
            $ch = curl_init();
            $id = $_GET['id_ef'];
            $sessKey = $_GET['session_key'];
            curl_setopt($ch, CURLOPT_URL, "http://www.azbukafinansov.ru/ef/confirmmail.php?session_key=".$sessKey);

            $azbukaMail = curl_exec($ch);//запрашиваем почту юзера если он залогинен

            curl_close($ch);

            $select = Login_Model::getUserDataByID( $id );
            if ( substr( $select[0]['user_login'] , 0, 6 ) != 'azbuka' )
                die('Аллес!!! Доступ запрещён');
            if ( $azbukaMail != $select[0]['user_mail'] )
                die('Неверная почта');

            $uar = array(
                'user_id'=>$id,
                'user_name'=>$select[0]['user_login'],
                'user_type'=>0);
            Core::getInstance()->tpl->assign('user_info', $uar);
            Core::getInstance()->tpl->assign('template_view', 'iframe');
            setcookie(COOKIE_NAME, encrypt(array($select[0]['user_login'],$select[0]['user_pass'])), time() + COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
            header("Location: https://" . URL_ROOT_IFRAME .  "info/");
            break;
        }
        if (( substr($_SERVER['REQUEST_URI'],0,7) == "/login/") && ( $_GET['refer'] == 'azbuka' ) && ( isset($_GET['login'] ) && ( isset($_GET['mail']) )) ){
            $log = new Login_Model();
            //$requeststring = substr($_SERVER[argv][0], 20);
            //$array = explode("&", $requeststring);
            $login = $_GET['login'];
            $mail = $_GET['mail'];
            $newId = $log->generateUserByAzbukaLogin( $login , $mail );

            $select = Login_Model::getUserDataByID( $newId );
            if ( substr( $select[0]['user_login'] , 0, 6 ) != 'azbuka' )
                die('Аллес!!! Доступ запрещён');

            $uar = array(
                'user_id'=>$newId,
                'user_name'=>$select[0]['user_login'],
                'user_type'=>0);
            Core::getInstance()->tpl->assign('user_info', $uar);
            Core::getInstance()->tpl->assign('template_view', 'iframe');
            setcookie(COOKIE_NAME, encrypt(array($select[0]['user_login'],$select[0]['user_pass'])), time() + COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
            header("Location: https://" . URL_ROOT_IFRAME . "info/");

            return $newId;
            break;
        }
        if ( ( ! Core::getInstance()->user->getId() ) AND ($_SERVER['REQUEST_URI'] != "/login/" ) ) {
            if ( $_SERVER['REQUEST_URI'] != '/registration/' &&  $_SERVER['REQUEST_URI'] != '/restore/') {
                header("Location: https://" . URL_ROOT_IFRAME . "login/");
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
