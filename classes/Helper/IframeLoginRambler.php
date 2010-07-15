<?php
class Helper_IframeLoginRambler extends Helper_IframeLogin
{
    function  __construct($templateEngine) {
        $this->templateEngine = $templateEngine;
    }

    /**
     * @return void
     */
    public function init()
    {
        // Если запрос идёт от Рамблера
        $request = explode('/', $_SERVER['REQUEST_URI']);

        // Если пользователь не авторизирован
        if (!Core::getInstance()->user->getId() && isset($request[1])
                && $request[1] == 'login' && isset($request[2])) {

                $this->_rambler_login($request[2]);

        }

        $this->_prepareDisplayIframe();
        $this->templateEngine->assign('template_view', 'iframe');
    }

    /**
     * Логиним пользователя рамблера
     * @param string $ramblerKey
     * @return bool
     */
    private function _rambler_login($ramblerKey)
    {
        $ramblerLogin = 'rambler_' . $ramblerKey;

        // Пытаемся инициализировать пользователя
        $this->_initUser($ramblerLogin, sha1($ramblerLogin));

        // Устанавливаем пользователю куку
        $this->_setCookie($ramblerLogin, sha1($ramblerLogin));

        if (Core::getInstance()->user->getId()) {

            $this->_redirect("Location: https://rambler." . URL_ROOT_MAIN . "info/");

        } else {

            Login_Model::generateUserByRamblerLogin('rambler_' . $ramblerKey);

        }
    }


    /**
     * Инициализируем пользователя
     *
     * @param string $login
     * @param string $password В формате SHA11
     * @return void
     */
    private function _initUser($login, $password)
    {
        Core::getInstance()->user->initUser($login, $password);
    }
}
