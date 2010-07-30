<?php
/**
 * Хелпер для логина и регистрация iframe пользователей
 *
 * @copyright http://easyfinance.ru/
 */
class Helper_IframeLogin
{

    /**
     * Ссылка на движок для шаблонизатора
     * @var Smarty
     */
    protected $templateEngine;

    /**
     * Конструктор
     * @param Smarty $templateEngine
     */
    function  __construct($templateEngine) {
        $this->templateEngine = $templateEngine;
    }

    static function login($templateEngine)
    {
        // Получаем субдомен, если он есть
        $subdomain = array_shift(explode('.', _Core_Request::getCurrent()->host));
        $filename = dirname(__FILE__) . '/IframeLogin' . ucfirst(strtolower($subdomain)) . '.php';
        $classname = "Helper_IframeLogin" . ucfirst(strtolower($subdomain));

        if (file_exists($filename)) {
            $class = new $classname($templateEngine);
            $class->init();
        }
    }


    /**
     * Подготавливаемся к выводу iframe в браузер
     *
     * @return void
     */
    protected function _prepareDisplayIframe()
    {
        // Выводим заголовок для отображения iframe по безопасному соединению для IE
        // Без этого заголовка IE не работает с iframe через SSL
        header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
    }

    /**
     * Делает редирект на указанную страницу. Нужна для тестов
     *
     * @param string $url
     * @return void
     */
    protected function _redirect($url)
    {
        _Core_Router::redirect($url);
    }


    /**
     * Устанавливаем куки
     *
     * @param string $login
     * @param string $password Строка в формате SHA1
     */
    protected function _setCookie($login, $password)
    {
        $cook = encrypt(array($login, $password));
        setcookie(COOKIE_NAME, $cook, time() + COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
    }

}
