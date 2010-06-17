<?php
require_once dirname(__FILE__).'/BaseIn.php';


/**
 * Синхронизация: получить список объектов
 */
class api_sync_InOperationTest extends api_sync_in
{
    /**
     * Возвращает стандартный валидный набор полей и значений объекта
     *
     * @return array
     */
    protected function getDefaultModelData()
    {
        return array(
            'id'          => null,
            'user_id'     => $this->_user->getId(),
            'account_id'  => null,
            'category_id' => 1,
            'amount'      => 0,
            'type'        => 0,
            'date'        => null,
            'time'        => null,
            'comment'     => 'Операция',
            'created_at'  => $this->_makeDate(-1000),
            'updated_at'  => $this->_makeDate(0),
            'deleted_at'  => null,
        );
    }


    /**
     * Вернуть название модели
     *
     * @return string
     */
    protected function getModelName()
    {
        return 'operation';
    }


    /**
     * Отправляет XML
     *
     * @param  string $xml      XML-строка
     * @param  int    $code     Код ответа сервера
     * @return sfTestFunctional Возвращает браузер @see sfTestBrowser
     */
    protected function myXMLPost($xml = null, $code = 200)
    {
        return $this
            ->postAndCheckXML("sync", "syncInOperation", $xml, "sync_in_operation", $code);
    }

}
