<?php
/**
 * Базовый класс Sync/In действий
 */

abstract class myBaseSyncInAction extends sfAction
{
    /**
     * SetUp
     */
    public function preExecute()
    {
        // Явно указать layout для всех форматов
        $this->setLayout('layout');
        // Явно указать шаблон
        $this->setTemplate('syncIn');

        $this->getContext()->getConfiguration()->loadHelpers('Sync', $this->getContext()->getModuleName());
        sfConfig::set('sf_escaping_method', 'ESC_XML');

        $this->setVar('type', $this->getModelName(), $noEscape = true);
    }


    /**
     * Делает из объекта SimpleXML массив
     *
     * @param  SimpleXMLElement $record
     * @return array
     */
    abstract protected function prepareArray(SimpleXMLElement $record);


    /**
     * Возвращает название модели
     *
     * @return string
     */
    abstract protected function getModelName();


    /**
     * Хук для конкретного действия
     *
     * @param  sfRequest   $request
     * @return const|void  @see sfView
     */
    abstract protected function executeLogic(sfRequest $request);


    /**
     * Execute: Проверяет входящие данные
     *
     * @param  sfRequest    $request
     * @see    executeLogic конкретная реализация логики
     */
    final public function execute($request)
    {
        try {
            if (null === ($userId = $this->getUser()->getId())) {
                $this->getResponse()->setHttpHeader('WWW_Authenticate', "Authentification required");
                $this->raiseError("Authentification required", 0, 401);
            }

            if (0 === strlen($rawXml = $request->getContent())) {
                $this->raiseError("Expected XML data");
            }

            if (false === strpos($rawXml, "<?xml")) {
                $this->raiseError("Expected valid text/xml");
            }

            try {
                $xml = simplexml_load_string($rawXml);
            } catch (Exception $e) {
                $this->raiseError("Expected valid text/xml: see xmlsoft.org");
                // $xml = new SimpleXMLElement('');
            }

            $this->setXML($xml);

            $count = (int) count($xml->recordset[0]);
            $limit = sfConfig::get('app_records_sync_limit', 100);

            if ($count <= 0) {
                $this->raiseError("Expected at least one record");
            } elseif ($count > $limit) {
                $this->raiseError("More than 'limit' ({$limit}) objects sent, {$count}");
            }

            $cids = $this->filterByXPath("//record/@cid", "cid");
            if (count($cids) != $count) {
                $this->raiseError("Request is NOT well-formed: no client ids");
            }

        } catch (sfStopException $e) {
            return $this->handleException($e);
        }

        try {
            return $this->executeLogic($request);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }


    /**
     * Обработка отображения глобальной ошибки
     *
     * @param  string      $message
     * @param  string|int  $errCode
     * @param  int         $code
     * @throws sfStopException
     */
    protected function raiseError($message = "Error", $errCode = 0, $code = 400)
    {
        $this->setTemplate('error', 'common');

        $this->getResponse()->setStatusCode($code);
        $this->setVar('code',    $errCode);
        $this->setVar('message', $message);

        throw new sfStopException($message);
    }


    /**
     * Фильтрует XML по выражению XPath, @see searchInXML
     *
     * @param  string $xpath Выражение для фильтрации
     * @param  string $key   Ключ для поиска/null
     * @return array
     */
    protected function filterByXPath($xpath, $key = null)
    {
        return $this->searchInXML($this->getXML()->xpath($xpath), $key);
    }


    /**
     * Ищет по SimpleXML набору значение атрибутов/содержимое элементов
     * TODO дописать возможность генерации не плоских массивов (hash-array)
     *      и убить отдельный метод prepareArray (с) Svel
     *
     * @param  array|SimpleXMLElement $xml Отфильтрованный xml
     * @param  string                 $key Ключ для поиска/null
     * @return array
     */
    private function searchInXML($xml, $key = null)
    {
        $data = array();
        foreach ($xml as $tmp) {
            if ($key && isset($tmp[$key])) {
                $tmp = (string) $tmp[$key];
            } elseif ($key && isset($tmp->$key)) {
                $tmp = (string) $tmp->$key;
            } else {
                $tmp = (string) $tmp;
            }

            if (!empty($tmp)) {
                $data[] = $tmp;
            }
        }

        return $data;
    }


    /**
     * Ставит подготовленный XML в переменную
     *
     * @param  SimpleXMLElement $xml
     * @return void
     */
    protected function setXML(SimpleXMLElement $xml)
    {
        $this->_xml = $xml;
    }


    /**
     * Получить подготовленный XML
     */
    protected function getXML()
    {
        if (null !== $this->_xml) {
            return $this->_xml;
        }

        throw new RuntimeException(__CLASS__.": Expected valid SimpleXMLElement, got null");
    }


    /**
     * Собирает в строку сообщения об ошибках
     *
     * @param  sfForm $form
     * @param  array  $errors
     * @return string
     */
    protected function formatErrorMessage(sfForm $form, Array $errors)
    {
        return (
                    ($errors
                        ? "[Invalid.] " . implode(" [Invalid.] ", $errors)
                        : ""
                    ) . (
                    strlen($form->getErrorSchema())
                        ? (($errors ? " " : "") . $form->getErrorSchema())
                        : ""
                    )
                );
    }


    /**
     * Обрабатывает произвольные исключения
     * @param Exception $e
     */
    protected function handleException(Exception $e)
    {
        $messageToLog = sprintf(
            "Exception %s thrown %s \n in %s:%s \n Trace: %s",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        $this->logMessage($messageToLog);
        $this->setVar('code', $e->getCode());
        $this->setVar('message', $e->getMessage());
        return sfView::ERROR;
    }

}
