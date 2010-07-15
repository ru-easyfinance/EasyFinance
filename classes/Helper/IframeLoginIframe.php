<?php
class Helper_IframeLoginIframe extends Helper_IframeLogin
{
    /**
     * @return void
     */
    public function init()
    {
        // Пользователь пытается авторизироваться от азбуки финансов
        if ($_SERVER['REQUEST_URI'] == "/login/" && (isset($_GET['refer'])) && ($_GET['refer'] == 'azbuka')) {

            // Пользователь только пытается авторизироваться, мы забираем у него id у нас
            // и его ключ сессии, затем по этому ключу мы делаем курлом гет запрос
            if (isset($_GET['id_ef']) && isset($_GET['session_key'])) {

                $this->_azbuka_login();

            // Новый пользователь регистрируется с азбуки
            } elseif (isset($_GET['login']) &&  isset($_GET['mail'])) {

                $this->_azbuka_registration();

            }
        }

        // Пользователь не авторизирован на странице логина
        if (!Core::getInstance()->user->getId() && ($_SERVER['REQUEST_URI'] != "/login/")) {

            if ($_SERVER['REQUEST_URI'] != '/registration/' &&  $_SERVER['REQUEST_URI'] != '/restore/') {

                $this->_redirect("Location: https://" . URL_ROOT_IFRAME . "login/");

            }

        }

        $this->_prepareDisplayIframe();
        $this->templateEngine->assign('template_view', 'iframe');
    }

    /**
     * Логиним для ресурса азбука финансов
     */
    private function _azbuka_login()
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
                $this->_redirect('/notfound', false, 404);
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

            $this->templateEngine->assign('user_info', $uar);
            $this->templateEngine->assign('template_view', 'iframe');
            $this->_setCookie($row_user['user_login'], $row_user['user_pass']);
            $this->_redirect("Location: https://" . URL_ROOT_IFRAME . "info/");

    }


    /**
     * Регистрируем нового пользователя с азбуки финансов
     */
    private function _azbuka_registration ()
    {
            $login = _Core_Request::getCurrent()->get['login'];
            $mail  = _Core_Request::getCurrent()->get['mail'];

            // Генерируем нового пользователя на основе логина и его почты
            $newId = Login_Model::generateUserByAzbukaLogin( $login , $mail );

            $row_user = Login_Model::getUserDataByID( $newId );

            // @FIXME Непонятно что тут делает этот блок..
            // Если мы только что сделали сами пользователя, то мы и знаем его логин, нет?
            if ( substr( $row_user['user_login'] , 0, 6 ) != 'azbuka' ) {
                $this->_redirect('/notfound', false, 404);
            }

            $uar = array(
                'user_id'=>$newId,
                'user_name'=>$row_user['user_login'],
                'user_type'=>0);
            $this->templateEngine->assign('user_info', $uar);
            $this->templateEngine->assign('template_view', 'iframe');
            $this->_setCookie($row_user['user_login'], $row_user['user_pass']);
            $this->_redirect("Location: https://" . URL_ROOT_IFRAME . "info/");
            return $newId;
    }
}