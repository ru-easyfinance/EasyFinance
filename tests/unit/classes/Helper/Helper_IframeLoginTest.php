<?php
ob_start();
require_once dirname(__FILE__) . '/../../bootstrap.php';

class TestHelper_IframeLoginTest extends Helper_IframeLoginRambler
{
    function  __construct($templateEngine) {
        $this->templateEngine = $templateEngine;
    }

    protected function _redirect($url)
    {
        return $url;
    }

    protected function _prepareDisplayIframe()
    {
        // Ничего не делать
    }

    function _initUser($login, $password)
    {

    }
}

class Helper_IframeLoginTest extends PHPUnit_Framework_TestCase {

    public function testLogin() {
        // Подготавливаем данные
        $_SERVER["REQUEST_METHOD"] = 'GET';
        $_SERVER["SERVER_PORT"] = '443';
        $_SERVER["HTTP_HOST"] = 'rambler.easyfinance.ru';
        $_SERVER["REQUEST_URI"]    = '/login/ef-ru-' . rand(10000, 9999999);

        // Формируем обьект с параметрами запроса.
        $request = _Core_Request::getCurrent();

        // Получаем текущий шаблонизатор на основании запроса
        $templateEngine = _Core_TemplateEngine::getPrepared( $request );

        $class = new TestHelper_IframeLoginTest($templateEngine);
        $class->init();

        $userId = Core::getInstance()->user->getId();
        $this->assertNotNull($userId, 'User Id mustn`t be null');
    }

}

