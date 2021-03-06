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
            'comment'     => '<![CDATA[Просто операция + коммент с & спецсимволом]]>',
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
        $expectedData['comment'] = "Просто операция + коммент с & спецсимволом";
        $this->browser
            ->with('model')->check('Operation', $expectedData, 1);
    }


    /**
     * Принять новую полупустую операцию
     */
    public function testNewHalfEmptyOperation()
    {
        $expectedData = array(
            'user_id'     => $this->_user->getId(),
            'date'        => $this->_makeDate(10000),
            'cid'         => 2,
            'category_id' => null,
            'account_id'  => null,
            'amount'      => null,
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

        $expectedData = array(
            'user_id'     => $this->_user->getId(),
            'accepted'    => 0,
        );

        $result = $this->browser
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
        foreach (array(0, 99999) as $categoryId) {
            $xml = $this->getXMLHelper()->make(array('category_id' => $categoryId, 'cid' => 4,));

            $this
                ->myXMLPost($xml, 200)
                ->with('response')->begin()
                    ->checkElement('resultset[type="Operation"] record[id][success="false"][cid]', 1)
                ->end();

            $this->checkRecordError(4, "category_id [No such category $categoryId]");
        }
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

        $this->checkRecordError(4, "category_id [No such category {$cat->getId()}]");
    }


    /**
     * Принять: пустая категория
     */
    public function testOperationCategoryEmpty()
    {
        $xml = $this->getXMLHelper()->make(array(
            'category_id' => null,
            'cid' => 4,
        ));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="Operation"] record[id][success="true"]', 'OK')
            ->end();
    }


    /**
     * Принять перевод между счетами
     */
    public function testTransferNew()
    {
        $acc_from = $this->helper->makeAccount($this->_user);
        $acc_to   = $this->helper->makeAccount($this->_user);

        $expectedData = array(
            'type'                => 2,
            'user_id'             => $this->_user->getId(),
            'amount'              => 100.75,
            'account_id'          => $acc_from->getId(),
            'transfer_account_id' => $acc_to->getId(),
            'transfer_amount'     => -500.21,
            'cid'                 => 3,
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

        unset($expectedData['cid']);
        $expectedData['amount'] = -abs($expectedData['amount']);
        $expectedData['transfer_amount'] = abs($expectedData['transfer_amount']);

        $this->browser
            ->with('model')->check('Operation', $expectedData, 1, $found);
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
     * Отвергать: Несуществующий id счета у операции перевода в пакете с другой операцией
     */
    public function testTransferAccountNotDoublesErrorMessage()
    {
        $xml = $this->getXMLHelper()->makeCollection(
            2,
            array(
                array('cid' => 1, 'id' => 123),
                array('transfer_account_id' => 9999, 'cid' => $errCid = 4),
            )
        );

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="Operation"] record[id][success="true"][cid=1]', 'OK')
                ->checkElement('resultset[type="Operation"] record[id][success="false"][cid=' . $errCid . ']', 1)
            ->end();

        $this->checkRecordError($errCid, '[Invalid.] No such account for transfer');
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


    /**
     * Принять: отсутствующая сумма перевода
     */
    public function testTransferAccountNoAmount()
    {
        $acc_from = $this->helper->makeAccount($this->_user);
        $acc_to   = $this->helper->makeAccount($this->_user);
        $expectedData = array(
            'transfer_account_id' => $acc_to->getId(),
            'account_id' => $acc_from->getId(),
            'amount' => -10.99,
            'type'   => 2,
            'transfer_amount' => null,
        );

        $xml = $this->getXMLHelper()->make(array_merge($expectedData, array('cid' => 2)));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="Operation"] record[id][success="true"][cid]', "OK")
            ->end();

        unset($expectedData['transfer_amount']);

        $this->browser
            ->with('model')->check('Operation', $expectedData, 1, $foundList);

        // условность - конвертации валют не произошло, т.к. счета созданы в 1ой валюте
        $this->assertEquals(abs($expectedData['amount']), $foundList['0']->getTransferAmount(), '', 0.01);
    }


    /**
     * Принять: операция-черновик с незаполненными полями
     */
    public function testEmptyFieldsDraftOperation()
    {
        $expectedData = array(
            'type'                => 1,    // тип всегда приходит какой-то
            'user_id'             => $this->_user->getId(),
        );

        $xmlData = array_merge(array(
            'amount'              => null, // пустое кол-во денеГ
            'account_id'          => null, // пустой Id счета
            'date'                => null, // пользователь не установил дату
            'transfer_account_id' => null, // счет для перевода пустой
            'transfer_amount'     => null, // и пустое кол-во денег для перевода
            'category_id'         => null, // нет категории
            'comment'             => null, // пустой коммент
            'accepted'            => null, // пусто или не принята
            'cid'                 => 3,
        ), $expectedData);

        $xml = $this->getXMLHelper()->make($xmlData);

        $this->myXMLPost($xml, 200);

        $this->browser
            ->with('model')->check('Operation', $expectedData, 1, $foundList);

        // Ответ
        $this->browser
            ->with('response')->begin()
                ->checkElement('resultset', 1)
                ->checkElement(sprintf('resultset[type="Operation"] record[id="%d"][cid="%d"][success="true"]',
                        $foundList->getFirst()->getId(),
                        $xmlData['cid'])
                    , 'OK')
            ->end();
    }


    /**
     * Удалить операцию
     */
    public function testDeleteOperation()
    {
        $expectedData = array(
            'user_id'     => $this->_user->getId(),
            'created_at'  => $this->_makeDate(-10000),
            'updated_at'  => $this->_makeDate(-300),
            'deleted_at'  => null,
        );

        $operation = $this->helper->makeOperation(
            $this->helper->makeAccount($this->_user),
            $expectedData
        );

        $expectedData['id']         = $operation->getId();
        $expectedData['cid']        = 2;
        $expectedData['deleted_at'] = $this->_makeDate(-300);

        $xml = $this->getXMLHelper()->make($expectedData);

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset', 1)
                ->checkElement('resultset record', 1)
                ->checkElement('resultset[type="Operation"] record[id][success="true"]', 'OK')
                ->checkElement(sprintf('resultset record[cid="%d"]', $expectedData['cid']), 'OK')
            ->end();

        $sql = "SELECT * FROM operation;";
        $operations = Doctrine_Manager::getInstance()
            ->getConnection('doctrine')->getDbh()->query($sql)->fetchAll(Doctrine::FETCH_ASSOC);

        $opRecord = "record[id=\"{$operation->getId()}\"][deleted=\"deleted\"]";

        $this->browser
            ->getAndCheck('sync', 'syncOut', $this->generateUrl('sync_get_modified', array(
                'model'   => 'operation',
                'from'    => $this->_makeDate(-1000),
                'to'      => $this->_makeDate(+1000),
            )), 200)
            ->with('response')->begin()
                ->isValid()
                ->checkContains('<recordset type="Operation">')
                ->checkElement("$opRecord", 1)
            ->end();
    }


    /**
     * Отправляем две балансовые операции для одного счёта
     */
    public function testDuplicateBalanceOperations()
    {
        $account = $this->helper->makeAccount($this->_user);

        // Балансовая операция существует и мы это знаем
        $balanceOperation = Doctrine::getTable('Operation')
            ->findOneByAccountIdAndType(
                $account->getId(),
                Operation::TYPE_BALANCE
            )
            ->getData();

        $xmlData = array(
            // Зная id балансовой операции, успешно меняем её
            array(
                'id'         => $balanceOperation['id'],
                'amount'     => 200,
                'account_id' => $account->getId(),
                'type'       => Operation::TYPE_BALANCE,
                'accepted'   => 1,
                'cid'        => 1,
            ),
            // Профит тут для массовки
            array(
                'amount'     => 400,
                'account_id' => $account->getId(),
                'type'       => Operation::TYPE_PROFIT,
                'accepted'   => 1,
                'cid'        => 2,
            ),
            // А это балансовая операция с неверным id
            array(
                'id'         => $balanceOperation['id'] + 1,
                'amount'     => 100,
                'account_id' => $account->getId(),
                'type'       => Operation::TYPE_BALANCE,
                'accepted'   => 1,
                'cid'        => 3,
            ),
        );

        $xml = $this->getXMLHelper()->makeCollection(3, $xmlData);

        $this->myXMLPost($xml, 200);

        // Ответ
        $this->browser
            ->with('response')->begin()
                ->checkElement('resultset[type="Operation"]', 1)
                ->checkElement('record[cid="1"][success="true"]', 'OK')
                ->checkElement('record[cid="2"][success="true"]', 'OK')
                ->checkElement('record[cid="3"][success="false"]')
            ->end();
    }


    /**
     * Отправляем балансовую операцию с пустым id
     */
    public function testBalanceOperationWithNullSid()
    {
        $account = $this->helper->makeAccount($this->_user);

        $xmlData = array(
            array(
                'amount'     => 100,
                'account_id' => $account->getId(),
                'type'       => Operation::TYPE_BALANCE,
                'accepted'   => 1,
                'cid'        => 3,
            ),
        );

        $xml = $this->getXMLHelper()->makeCollection(3, $xmlData);

        $this->myXMLPost($xml, 200);

        // Ответ
        $this->browser
            ->with('response')->begin()
                ->checkElement('resultset[type="Operation"]', 1)
                ->checkElement('record[cid="3"][success="true"]', 'OK')
            ->end();
    }


    /**
     * Отправляем операцию из часового пояса отличного от Москвы и Гринвича
     */
    public function testOperationDatesWithTimezones()
    {
        $account = $this->helper->makeAccount($this->_user);

        $dateStart   = new DateTime('1 month ago');
        $dateBetween = new DateTime('1 week ago');
        $dateEnd     = new DateTime('now');

        $timezone    = date_default_timezone_get() == 'America/Los_Angeles' ?
            'Europe/Paris' :
            'America/Los_Angeles';

        // Теперь мы в Париже или в Лос-Анджелесе
        $dateBetween->setTimezone(new DateTimeZone($timezone));

        $xmlData = array(
            array(
                'amount'     => 400,
                'account_id' => $account->getId(),
                'type'       => Operation::TYPE_PROFIT,
                'accepted'   => 1,
                'cid'        => 1,
                'comment'    => 'CID=1',
                'updated_at' => $dateBetween->format(DATE_ISO8601),
                'created_at' => $dateBetween->format(DATE_ISO8601),
            ),
        );

        $xml = $this->getXMLHelper()->makeCollection(1, $xmlData);

        $this->myXMLPost($xml, 200);

        // Ответ
        $this->browser
            ->with('response')->begin()
                ->checkElement('resultset[type="Operation"]', 1)
                ->checkElement('record[cid="1"][success="true"]', 'OK')
            ->end();

        $sxml     = new SimpleXMLElement($this->browser->getResponse()->getContent());
        $record   = $sxml->xpath('resultset/record[@cid="1"]');
        $recordId = (int) $record[0]['id'];

        // Дата осталась прежней, но в БД она хранится в часовом поясе Москвы
        $dateBetween->setTimezone(new DateTimeZone(date_default_timezone_get()));

        $this->browser
            ->getAndCheck(
                'sync',
                'syncOut',
                $this->generateUrl(
                    'sync_get_modified',
                    array(
                        'model'   => 'operation',
                        'from'    => $dateStart->format(DATE_ISO8601),
                        'to'      => $dateEnd->format(DATE_ISO8601)
                    )
                ),
                200
            )
            ->with('response')->begin()
                ->isValid()
                ->checkElement(
                    "record[id=\"$recordId\"] created_at",
                    $dateBetween->format(DATE_ISO8601)
                )
                ->checkElement(
                    "record[id=\"$recordId\"] updated_at",
                    $dateBetween->format(DATE_ISO8601)
                )
            ->end();
    }
}
