<?php

abstract class mySyncInFunctionalTestCase extends myFunctionalTestCase
{
    protected $xmlHelper;

    /**
     * Custiom setup initialization
     *
     * @see sfPHPUnitFunctionalTestCase::setUp
     */
    protected function _initialize()
    {
        parent::_initialize();

        $this->browser->setTester('doctrine', 'sfTesterDoctrine');
    }


    /**
     * Выполняет POST-запрос и выполняет базовые проверки
     *
     * @param  string $module     Имя модуля
     * @param  string $action     Имя ?экшена
     * @param  array  $parameters Параметры запроса
     * @param  mixed  $uri        Роутинг @see sfRoute
     * @param  int    $code       Код ответа сервера
     * @return sfTestFunctional   Возвращает браузер @see sfTestBrowser
     */
    public function postAndCheck($module, $action, $parameters = array(), $uri = null, $code = 200)
    {
        if (null === $uri) {
            $uri = sprintf('/%s/%s', $module, $action);
        } else {
            $uri = $this->generateUrl(trim($uri));
        }

        return $this->browser
            ->post($uri, $parameters, true)
            ->with('request')->begin()
                ->isParameter('module', $module)
                ->isParameter('action', $action)
            ->end()
            ->with('response')->isStatusCode($code);
    }


    /**
     * Создать дату с указанным смещением от текущей
     *
     * @param  int    $shift - Смещение в секундах
     * @return string
     */
    protected function _makeDate($shift)
    {
        return date(DATE_ISO8601, time()+$shift);
    }


    /**
     * @return mySyncInXMLHelper
     */
    protected function getXMLHelper()
    {
        return $this->xmlHelper;
    }


    /**
     * Дергает методы браузера, если своих нет
     */
    public function __call($method, $arguments)
    {
        try {
            $retval = call_user_func_array(array($this->browser, $method), $arguments);
        } catch (Exception $e) {
            //return parent::__call($method, $arguments);
            return $e;
        }

        return $retval === $this->browser ? $this : $retval;
    }

}
