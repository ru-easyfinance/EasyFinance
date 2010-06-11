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
     * Обработка отображения глобальной ошибки
     *
     * @param  string      $message
     * @param  string|int  $errCode
     * @param  int         $code
     * @return const       sfView::ERROR
     */
    protected function raiseError($message = "Error", $errCode = 0, $code = 400)
    {
        $this->getResponse()->setStatusCode($code);
        $this->setVar('error', array(
            'message' => $message,
            'code'    => $errCode,
        ), $noEscape = false);
        return sfView::ERROR;
    }


    /**
     * Ищет по SimpleXML набору значение атрибутов/содержимое элементов
     *
     * @param  array|SimpleXMLElement $xml Отфильтрованный xml
     * @param  string                 $key Ключ для поиска/null
     * @return array
     */
    protected function searchInXML($xml, $key = null)
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

}
