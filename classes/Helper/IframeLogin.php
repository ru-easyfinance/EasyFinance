<?php
/**
 * Хелпер для логина и регистрация iframe пользователей
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Helper_IframeLogin
{
    
    /**
     * Ссылка на движок для шаблонизатора
     * @var Smarty
     */
    static $templateEngine;

    public static function login ( $templateEngine )
    {

        self::$templateEngine = $templateEngine;

        // Выводим заголовки политики безопастности в IE для поддержки cookies в iframe
        if( _Core_Request::getCurrent()->host . '/' == URL_ROOT_IFRAME)
        {
            // Выводим заголовок для отображения iframe по безопасному соединению для IE
            header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

            // Пользователь авторизироваться от азбуки финансов
            if ( ( _Core_Request::getCurrent()->uri == "/login/")
                    && ( _Core_Request::getCurrent()->get['refer'] == 'azbuka' ) )
            {
        
                // Пользователь только пытается авторизироваться, мы забираем у него id у нас
                // и его ключ сессии, затем по этому ключу мы делаем курлом гет запрос
                if ( isset(_Core_Request::getCurrent()->get['id_ef'])
                        && isset( _Core_Request::getCurrent()->get['session_key'] ) ) {

                    self::_azbuka_login();

                // Новый пользователь регистрируется с азбуки
                } elseif ( isset( _Core_Request::getCurrent()->get['login'] )
                        &&  isset( _Core_Request::getCurrent()->get['mail'] ) ) {

                    return self::_azbuka_registration();

                }
            }
        
            if ( ( ! Core::getInstance()->user->getId() ) AND ($_SERVER['REQUEST_URI'] != "/login/" ) ) {
                if ( $_SERVER['REQUEST_URI'] != '/registration/' &&  $_SERVER['REQUEST_URI'] != '/restore/') {
                    header("Location: https://" . URL_ROOT_IFRAME . "login/");
                }
            }
            self::$templateEngine->assign('template_view', 'iframe');
            self::$templateEngine->display("iframe/index.iframe.html");
        }
    }

    /**
     * Функция устанавливает на пользователя куку
     */
    private static function _goToInfo ()
    {
        // Авторизируем пользователя
        setcookie( COOKIE_NAME, encrypt( array( $row_user['user_login'], $row_user['user_pass'] ) ),
                time() + COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS );

        // После авторизации редиректим на инфо
        _Core_Router::redirect("https://" . URL_ROOT_IFRAME .  "info/");
    }

    /**
     * Логини для ресурса азбука финансов
     */
    private static function _azbuka_login ()
    {
            $curl = curl_init();

            $id      = _Core_Request::getCurrent()->get['id_ef'];
            $sessKey = _Core_Request::getCurrent()->get['session_key'];
            $url     = "http://www.azbukafinansov.ru/ef/confirmmail.php?session_key=".$sessKey;

            curl_setopt( $curl, CURLOPT_URL, $url );

            // Тут мы должны получить почту пользователя
            $azbukaMail = curl_exec($curl); 

            curl_close($curl);

            // Массив с данными пользователя
            $row_user = Login_Model::getUserDataByID( $id );

            // Разрешаем доступ пользователям зарегистрированным только на азбуке финансов
            // @TODO Возможно эту проверку стоит удалить
            if ( substr( $row_user['user_login'] , 0, 6 ) != 'azbuka' ) {
                _Core_Router::redirect('/notfound', false, 404);
            }

            // Проверяем почту клиента
            // @FIXME Почта пользователя всегда может смениться.
            // Мы не можем просто так сбрасывать пользователся
            // Необходимо поправить
            if ( $azbukaMail != $row_user['user_mail'] ) {
                die('Unknown mail format');
            }

            $uar = array(
                'user_id'=>$id,
                'user_name'=>$row_user['user_login'],
                'user_type'=>0);

            self::$templateEngine->assign('user_info', $uar);
            self::$templateEngine->assign('template_view', 'iframe');
            setcookie( COOKIE_NAME, encrypt( array( $row_user['user_login'], $row_user['user_pass'] ) ),
                    time() + COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS );

            header("Location: https://" . URL_ROOT_IFRAME . "info/");

            //self::_goToInfo( $row_user );

    }

    /**
     * Регистрируем нового пользователя с азбуки финансов
     */
    private static function _azbuka_registration ()
    {
            $login = _Core_Request::getCurrent()->get['login'];
            $mail  = _Core_Request::getCurrent()->get['mail'];

            // Генерируем нового пользователя на основе логина и его почты
            $newId = Login_Model::generateUserByAzbukaLogin( $login , $mail );

            $row_user = Login_Model::getUserDataByID( $newId );

            // @FIXME Непонятно что тут делает этот блок..
            // Если мы только что сделали сами пользователя, то мы и знаем его логин, нет?
            if ( substr( $row_user['user_login'] , 0, 6 ) != 'azbuka' ) {
                _Core_Router::redirect('/notfound', false, 404);
            }

            //self::_goToInfo( $row_user );

            $uar = array(
                'user_id'=>$newId,
                'user_name'=>$row_user['user_login'],
                'user_type'=>0);
            self::$templateEngine->assign('user_info', $uar);
            self::$templateEngine->assign('template_view', 'iframe');
            setcookie( COOKIE_NAME, encrypt( array( $row_user['user_login'], $row_user['user_pass'] ) ),
                    time() + COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS );

            header("Location: https://" . URL_ROOT_IFRAME . "info/");

            return $newId;
//            break;
    }

}