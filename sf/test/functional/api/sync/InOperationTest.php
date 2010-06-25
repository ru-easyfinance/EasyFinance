<?php
require_once dirname(__FILE__).'/BaseIn.php';


/**
 * Синхронизация: получить список объектов
 */
class api_sync_InOperationTest extends api_sync_in
{
    /**
     * Получить список полей полей объекта, которые принимаются из XML
     */
    protected function getFields()
    {
        return array(
            'account_id',
            'category_id',
            'amount',
            'type',
            'date',
            'comment',
            'accepted',
            'created_at',
            'updated_at',
            'transfer_account_id',
            'transfer_amount',
        );
    }


    /**
     * Возвращает стандартный валидный набор полей и значений объекта
     *
     * @return array
     */
    protected function getDefaultModelData()
    {
        $account = $this->helper->makeAccount($this->_user);

        return array(
            'id'          => null,
            'user_id'     => $this->_user->getId(),
            'account_id'  => $account->getId(),
            'category_id' => null,
            'amount'      => 0,
            'type'        => 0,
            'date'        => $this->_makeDate(-10000),
            'comment'     => 'Операция',
            'accepted'    => 1,
            'created_at'  => $this->_makeDate(-1000),
            'updated_at'  => $this->_makeDate(0),
            'deleted_at'  => null,
            'transfer_account_id' => null,
            'transfer_amount'     => null,
        );
    }


    /**
     * Вернуть название модели
     *
     * @return string
     */
    protected function getModelName()
    {
        return 'Operation';
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
     * Принять новую операцию
     */
    public function testNewOperation()
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
                ->checkElement('resultset[type="Operation"] record[id][success="true"]', 'OK')
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
                ->checkElement('resultset[type="Operation"] record[id][success="false"][cid]', 1)
                ->checkElement(sprintf('record[id="%d"]', $expectedData['id']))
            ->end();

        $this->checkRecordError($expectedData['cid'], '[Invalid.] Foreign operation');
    }


    /**
     * Отвергать: Несуществующий id счета у операции
     */
    public function testOperationAccountFK()
    {
        $xml = $this->getXMLHelper()->make(array('account_id' => 9999, 'cid' => 4,));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="Operation"] record[id][success="false"][cid]', 1)
            ->end();

        $this->checkRecordError(4, '[Invalid.] No such account');
    }


    /**
     * Отвергать: Счет другого пользователя
     */
    public function testOperationAccountForeign()
    {
        $acc = $this->helper->makeAccount();
        $xml = $this->getXMLHelper()->make(array('account_id' => $acc->getId(), 'cid' => 4,));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="Operation"] record[id][success="false"][cid]', 1)
            ->end();

        $this->checkRecordError(4, '[Invalid.] No such account');
    }


    /**
     * Отвергать: Несуществующий id категории
     */
    public function testOperationCategoryFK()
    {
        $xml = $this->getXMLHelper()->make(array('category_id' => 99999, 'cid' => 4,));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="Operation"] record[id][success="false"][cid]', 1)
            ->end();

        $this->checkRecordError(4, '[Invalid.] No such category');
    }


    /**
     * Отвергать: Категория другого пользователя
     */
    public function testOperationCategoryForeign()
    {
        $cat = $this->helper->makeCategory();
        $xml = $this->getXMLHelper()->make(array('category_id' => $cat->getId(), 'cid' => 4,));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="Operation"] record[id][success="false"][cid]', 1)
            ->end();

        $this->checkRecordError(4, '[Invalid.] No such category');
    }


    /**
     * Принять балансовую операцию
     */
    public function testBalanceOperation()
    {
        $expectedData = array(
            'amount'      => 100,
            'type'        => 3, // тип - балансовая операция
            'cid'         => 2,
            'date'        => "0000-00-00",
            'category_id' => null,
            'drain'       => 0,
        );

        $xml = $this->getXMLHelper()->make($expectedData);

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement(sprintf(
                    'resultset[type="Operation"] record[id][cid="%d"][success="true"]',
                        $expectedData['cid']
                ), 'OK')
            ->end();

        unset($expectedData['cid']); // у записи нет такого поля
        $this->browser
            ->with('model')->check('Operation', $expectedData, 1);
    }


    /**
     * Принять перевод между счетами
     */
    public function testTransferNew()
    {
        $acc_from = $this->helper->makeAccount($this->_user);
        $acc_to   = $this->helper->makeAccount($this->_user);

        $expectedFromData = array(
            'type'                => 2,
            'user_id'             => $this->_user->getId(),
            'amount'              => 100,
            'account_id'          => $acc_from->getId(),
            'transfer_account_id' => $acc_to->getId(),
            'transfer_amount'     => 500, // другая валюта
            'cid'                 => 3,
            'drain'               => 1,
        );

        $expectedToData = array_merge($expectedFromData, array(
            'amount'              => 500,
            'transfer_amount'     => 100,
            'transfer_account_id' => $acc_from->getId(),
            'account_id'          => $acc_to->getId(),
        ));

        $xml = $this->getXMLHelper()->make($expectedFromData);

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement(sprintf(
                    'resultset[type="Operation"] record[id][cid="%d"][success="true"]',
                        $expectedFromData['cid']
                ), 'OK')
            ->end();

        unset($expectedFromData['cid'], $expectedToData['cid']);

        $this->browser
            ->with('model')->check('Operation', $expectedFromData, 1, $found);
    }


    /**
     * Отвергать: Несуществующий id счета у операции перевода
     */
    public function testTransferAccountFK()
    {
        $xml = $this->getXMLHelper()->make(array('transfer_account_id' => 9999, 'cid' => 4,));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="Operation"] record[id][success="false"][cid]', 1)
            ->end();

        $this->checkRecordError(4, '[Invalid.] No such account for transfer');
    }


    /**
     * Отвергать: Счет у перевода - другого пользователя
     */
    public function testTransferAccountForeign()
    {
        $acc = $this->helper->makeAccount();
        $xml = $this->getXMLHelper()->make(array('transfer_account_id' => $acc->getId(), 'cid' => 4,));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="Operation"] record[id][success="false"][cid]', 1)
            ->end();

        $this->checkRecordError(4, '[Invalid.] No such account for transfer');
    }

}
