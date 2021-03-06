<?php

abstract class mySyncInFunctionalTestCase extends myFunctionalTestCase
{
    protected $xmlHelper;


    /**
     * Должна принять XML строку и код ответа сервера
     * передавать методу postAndCheckXML модуль, действие и урл
     *
     * @see    mySyncInFunctionalTestCase::postAndCheckXML
     * @param  string $xml      XML-строка
     * @param  int    $code     Код ответа сервера
     * @return sfTestFunctional Возвращает браузер @see sfTestBrowser
     */
    abstract protected function myXMLPost($xml = null, $code = 200);


    /**
     * Вернуть набор полей и валидных значений объекта
     */
    abstract protected function getDefaultModelData();


    /**
     * Вернуть название класса модели
     */
    abstract protected function getModelName();


    /**
     * Получить список полей полей объекта, которые принимаются из XML
     */
    abstract protected function getFields();


    /**
     * Before Execute
     */
    protected function _start()
    {
        parent::_start();

        $this->_user = $this->helper->makeUser();
        $this->authenticateUser($this->_user);
        $this->xmlHelper = new mySyncInXMLHelper($this->getModelName(), $this->getDefaultModelData(), $this->getFields());
    }


    /**
     * Выполняет POST-запрос и выполняет базовые проверки
     *
     * @param  string $module        Название модуля
     * @param  string $action        Название действия
     * @param  array  $parameters    Параметры POST запроса
     * @param  mixed  $uri           Роутинг @see sfRoute
     * @param  array  $getParameters Query string параметры
     * @param  int    $code          Код ответа сервера
     * @return sfTestFunctional      Возвращает браузер @see sfTestBrowser
     */
    public function postAndCheck($module, $action, $parameters = array(), $uri = null, $getParameters = array(), $code = 200)
    {
        if (null === $uri) {
            $uri = sprintf('/%s/%s', $module, $action);
        } else {
            $uri = $this->generateUrl(trim($uri), $getParameters);
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
     * Отправляет XML
     *
     * @param  string $xml      XML-строка
     * @param  int    $code     Код ответа сервера
     * @return sfTestFunctional Возвращает браузер @see sfTestBrowser
     */
    protected function postAndCheckXML($module, $action, $xml = null, $uri = null, $code = 200)
    {
        $params = array();
        if (null !== $xml) {
            $params['body'] = $xml;
        }

        return $this
            ->postAndCheck($module, $action, $params, $uri, array(), $code)
            ->with('response')->begin()
                ->isHeader("content-type", "/^text\/xml/")
                ->isValid()
            ->end();
    }


    /**
     * Проверяет сообщения ошибок
     *
     * @param  string $xml      XML-строка
     * @param  int    $code     Код ответа сервера
     * @param  string $message  Сообщение ошибки
     * @param  string $errCode  Код ошибки
     * @return sfTestFunctional Возвращает браузер @see sfTestBrowser
     */
    protected function checkSyncInError($xml, $code, $message, $errCode = 0)
    {

        return $this
            ->myXMLPost($xml, $code)
            ->with('response')->begin()
                ->checkElement(sprintf('error[code="%d"]', $errCode))
                ->checkElement("error", $message)
            ->end();
    }


    /**
     * Проверяет ошибки обработки записей
     *
     * @param  int    $id       Идентификатор записи клиента
     * @param  string $message  Сообщение ошибки   @see mySincInAccounForm
     * @return sfTestFunctional Возвращает браузер @see sfTestBrowser
     */
    protected function checkRecordError($id, $message)
    {
        return $this->browser
            ->with('response')
            ->checkElement(sprintf('resultset record[cid="%d"]', $id), (string) $message);
    }


    /**
     * @return mySyncInXMLHelper
     */
    protected function getXMLHelper()
    {
        return $this->xmlHelper;
    }

}
