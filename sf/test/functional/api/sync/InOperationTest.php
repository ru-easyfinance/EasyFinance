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
        $account = $this->helper->makeAccount($this->_user);
        $category = $this->helper->makeCategory($this->_user);

        return array(
            'id'          => null,
            'user_id'     => $this->_user->getId(),
            'account_id'  => $account->getId(),
            'category_id' => $category->getId(),
            'amount'      => 0,
            'type'        => 0,
            'date'        => $this->_makeDate(-10000),
            'comment'     => 'Операция',
            'accepted'    => 1,
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


    /**
     * Принять валидный xml
     */
    public function testPostOperationSingle()
    {
        $expectedData = array(
            'user_id'     => $this->_user->getId(),
            'comment'     => 'Просто операция',
            'created_at'  => $this->_makeDate(-10000),
            'updated_at'  => $this->_makeDate(-300),
            'cid'         => 2,
        );

        $xml = $this->getXMLHelper()->make($expectedData);

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset', 1)
                ->checkElement('resultset record', 1)
                ->checkElement('resultset[type="operation"] record[id][success="true"]', 'OK')
                ->checkElement(sprintf('resultset record[cid="%d"]', $expectedData['cid']), 'OK')
            ->end();

        unset($expectedData['cid']); // у записи нет такого поля
        $this->browser
            ->with('model')->check('Operation', $expectedData, 1);
    }


    /**
     * Отвергать чужие записи
     */
    public function testPostOperationForeignUserRecord()
    {
        $operation = $this->helper->makeOperation();

        $expectedData = array(
            'id'  => $operation->getId(),
            'cid' => 8,
        );

        $xml = $this->getXMLHelper()->make($expectedData);

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="operation"] record[id][success="false"][cid]', 1)
                ->checkElement(sprintf('record[id="%d"]', $expectedData['id']))
            ->end();

        $this->checkRecordError($expectedData['cid'], '[Invalid.] Foreign account');
    }

}
