<?php
require_once dirname(__FILE__) . '/../bootstrap.php';

/**
 * Тест операций
 */
class Operation_ModelTest extends UnitTestCase
{
    private $userId     = null;
    private $userLogin  = null;
    private $userPass   = null;
    /** @var oldUser */
    private $user       = null;
    private $model      = null;
    private $accountId  = null;
    private $accountId2 = null;
    private $catId      = null;

    function _start()
    {
        $this->userLogin = 'someLogin++' . mktime();
        $this->userPass  = 'somePass';

        $options = array(
            'user_login' => $this->userLogin,
            'user_pass'  => sha1($this->userPass),
            'user_active'=> 1,
            'user_new'   => 0,
        );
        CreateObjectHelper::makeUser($options);
    }

    /**
     * Подготавливает среду для создания операций
     */
    private function _prepareOperation()
    {
        // Создаём пользователя
        $this->user = new oldUser($this->userLogin, $this->userPass);
        $this->userId = $this->user->getId();

        // Счета
        $options = array(
            'user_id'      => $this->userId,
            'account_name' => 'RUR Account'
        );
        $account = CreateObjectHelper::makeAccount($options);
        $this->accountId = $account['account_id'];


        $options['account_name'] = 'ABC 2';
        $account = CreateObjectHelper::makeAccount($options);
        $this->accountId2 = $account['account_id'];

        // Доллары
        $options = array(
            'user_id'      => $this->userId,
            'account_name' => 'USD account',
            'account_currency_id' => myMoney::USD
        );

        $account = CreateObjectHelper::makeAccount($options);
        $this->accountId3 = $account['account_id'];

        // Доллары
        $options = array(
            'user_id'      => $this->userId,
            'account_name' => 'Another USD account',
            'account_currency_id' => myMoney::USD
        );

        $account = CreateObjectHelper::makeAccount($options);
        $this->accountId4 = $account['account_id'];

        // Категории
        $options = array(
            'user_id'  => $this->userId,
        );
        $this->catId = CreateObjectHelper::createCategory($options);

        // Это важный метод. Он подгрузит все счета, категории пользователя (и что-нибудь ещё)
        $this->user->init();
        $this->user->save();
    }

    /**
     * Создаём операции
     */
    private function _makeOperation()
    {
        $options = array(
            'user_id'    => $this->userId,
            'chain_id'   => 999,
            'date'       => date('Y-m-d', time()-86400),
            'cat_id'     => $this->catId,
            'account_id' => $this->accountId,

        );
        // Правильные операции, на вчера
        CreateObjectHelper::makeOperation($options);
        CreateObjectHelper::makeOperation($options);
        CreateObjectHelper::makeOperation($options);

        // Операция не выполнена
        $options['accepted'] = 0;
        CreateObjectHelper::makeOperation($options);


        // Дата операции установлена на завтра
        $options['date'] = date('Y-m-d', time()+86400);
        CreateObjectHelper::makeOperation($options);

        // Дата операции установлена на завтра, но она отмечена выполненной
        $options['accepted'] = 1;
        $options['date'] = date('Y-m-d', time()+86400);
        CreateObjectHelper::makeOperation($options);

        // Удалённая операция
        $options['deleted_at'] = '2010-02-02 02:02:02';
        CreateObjectHelper::makeOperation($options);

        // Обычная операция, вне цепочки
        unset($options['deleted_at']);
        unset($options['chain_id']);
        CreateObjectHelper::makeOperation($options);
    }


    /**
     * Тест получения списка операций
     */
    public function testGetOperationList()
    {
        $this->_prepareOperation();
        $this->_makeOperation();
        $this->model = new Operation_Model($this->user);

        $start = new DateTime('-1week');
        $end   = new DateTime('+1week');

        $operations = $this->model->getOperationList(
                $start->format('Y-m-d'),
                $end->format('Y-m-d'),
                0,
                $this->accountId,
                null,
                null,
                null
        );

        $this->assertEquals(5, count($operations), 'Expected 5 operations');
    }


    /**
     * Тест создания доходной операции
     */
    public function testEqualsProfit()
    {
        $this->_prepareOperation();
        $expected = array(
            'user_id'               => $this->userId,
            'money'                 => 100,
            'time'                  => '00:00:00',
            'date'                  => '2010-01-01',
            'cat_id'                => $this->catId,
            'account_id'            => $this->accountId,
            'comment'               => 'Комментарий',
            'transfer_account_id'   => null,
            'transfer_amount'       => null,
            'tags'                  => 'тег1',
            'type'                  => Operation::TYPE_PROFIT,
            'source_id'             => null,
            'accepted'              => 1,
            'chain_id'              => 0,
            'exchange_rate'         => 0.000000,
            'deleted_at'            => null,
        );

        $operation  = new Operation_Model($this->user);
        $opId = $operation->add(
                Operation::TYPE_PROFIT,
                100,
                '2010-01-01',
                $this->catId,
                "Комментарий",
                $this->accountId,
                array('тег1')
        );

        $expected = array_merge(array('id' => $opId), $expected);

        // Получаем созданную операцию из БД
        $sql = "SELECT * FROM operation WHERE id=?";
        $actual = $this->getConnection()->selectRow($sql, $opId);

        // Смотрим на дату создания и редактирования
        $this->assertGreaterThan(date('U', time()-60), strtotime($actual['created_at']));

        $this->assertEquals($actual['created_at'], $actual['updated_at']);

        // Удаляем время создания и обновления
        unset($actual['created_at']);
        unset($actual['updated_at']);

        // Сравниваем эквивалентность
        $this->assertEquals($expected, $actual, 'Expected equals operation');
    }


    /**
     * Тест создания расходной операции
     */
    public function testEqualsWaste()
    {
        $this->_prepareOperation();
        $expected = array(
            'user_id'               => $this->userId,
            'money'                 => -100,
            'time'                  => '00:00:00',
            'date'                  => '2010-01-01',
            'cat_id'                => $this->catId,
            'account_id'            => $this->accountId,
            'comment'               => 'Комментарий',
            'transfer_account_id'   => null,
            'transfer_amount'       => null,
            'tags'                  => 'тег1',
            'type'                  => Operation::TYPE_WASTE,
            'source_id'             => null,
            'accepted'              => 1,
            'chain_id'              => 0,
            'exchange_rate'         => 0.000000,
            'deleted_at'            => null,
        );

        $operation  = new Operation_Model($this->user);
        $opId = $operation->add(
                Operation::TYPE_WASTE,
                -100,
                '2010-01-01',
                $this->catId,
                "Комментарий",
                $this->accountId,
                array('тег1')
        );

        $expected = array_merge(array('id' => $opId), $expected);

        $sql = "SELECT * FROM operation WHERE id=?";
        $actual = $this->getConnection()->selectRow($sql, $opId);

        // Смотрим на дату создания и редактирования
        $this->assertGreaterThan(date('U', time()-60), strtotime($actual['created_at']));

        $this->assertEquals($actual['created_at'], $actual['updated_at']);

        // Удаляем время создания и обновления
        unset($actual['created_at']);
        unset($actual['updated_at']);

        $this->assertEquals($expected, $actual, 'Expected equals operation');
    }

    /**
     * Тест создания операции перевода
     */
    public function testEqualsTransfer()
    {
        $this->_prepareOperation();
        $operation  = new Operation_Model($this->user);

        $opId = $operation->addTransfer(
                100,
                0,
                0,
                '2010-01-01',
                $this->accountId,
                $this->accountId2,
                'Комментарий',
                array('тег 1')
        );

        $expected = array(
            'id'                    => $opId,
            'user_id'               => $this->userId,
            'money'                 => -100,
            'time'                  => '00:00:00',
            'date'                  => '2010-01-01',
            'cat_id'                => null,
            'account_id'            => $this->accountId,
            'comment'               => 'Комментарий',
            'transfer_account_id'   => $this->accountId2,
            'transfer_amount'       => 100,
            'tags'                  => 'тег 1',
            'type'                  => Operation::TYPE_TRANSFER,
            'source_id'             => null,
            'accepted'              => 1,
            'chain_id'              => 0,
            'exchange_rate'         => 0.000000,
            'deleted_at'            => null,
        );


        $sql = "SELECT * FROM operation WHERE id=?";
        $actual = $this->getConnection()->selectRow($sql, $opId);

        unset($actual['created_at']);
        unset($actual['updated_at']);

        $this->assertEquals($expected, $actual, 'Expected equals operation');
    }

    /**
     * Тест создания операции перевода
     * Проверка корректности бивалютного перевода
     */
    public function testBicurrencyTransfer()
    {
        $this->_prepareOperation();
        $operation  = new Operation_Model($this->user);

        // Перевели 100 рублей с рублёвого на долларовый
        $opId = $operation->addTransfer(
                100,
                0,
                0,
                '2010-01-01',
                $this->accountId,
                $this->accountId3,
                'Комментарий',
                array('тег 1')
        );

        $dateFrom = '2009-12-29';
        $dateTo   = '2010-01-02';

        $list = $operation->getOperationList($dateFrom, $dateTo);

        $this->assertEquals(1, count($list), 'Expected only 1 transfer operation');

        $list = $operation->getOperationList($dateFrom, $dateTo, null, $this->accountId, -1);

        $this->assertEquals(1, count($list), 'Expected only 1 transfer operation');

        // А теперь пусть переводов будет 2
        // Перевели 500 $ с долларового кошелька на рублёвый
        $opId = $operation->addTransfer(
                500,
                0,
                0,
                '2010-01-01',
                $this->accountId3,
                $this->accountId,
                'Комментарий',
                array('тег 1')
        );

        $list = $operation->getOperationList($dateFrom, $dateTo, null, $this->accountId, -1);

        $this->assertEquals(2, count($list), 'Expected only 2 transfer operation');

        $list = $operation->getOperationList($dateFrom, $dateTo, null, $this->accountId3, -1);

        $this->assertEquals(2, count($list), 'Expected only 2 transfer operation');
    }

    /**
     * Тест создания операции перевода
     * Проверка корректности одновалютного перевода в иностранной валюте
     */
    public function testUnicurrencyTransfer()
    {
        $this->_prepareOperation();
        $operation  = new Operation_Model($this->user);

        // Перевели 100 долларов с одного на другой
        $opId = $operation->addTransfer(
                100,
                0,
                0,
                '2010-01-01',
                $this->accountId3,
                $this->accountId4,
                'Комментарий',
                array('тег 1')
        );

        $op = $operation->getOperation($this->user->getId(), $opId);

        $this->assertEquals(100, $op['moneydef'], 'Expected 100 dollars have been transfered');
    }

    /**
     * Проверка корректности подсчёта баланса
     */
    public function testStat()
    {
        $login = 'SomeLogin--' . mktime();
        $pass  = 'qwerty';
        $options = array(
            'user_login' => $login,
            'user_pass'  => sha1($pass),
            'user_active'=> 1,
            'user_new'   => 0,
            'user_currency_default' => myMoney::UAH,
        );

        CreateObjectHelper::makeUser($options);
        $user = new oldUser($login, $pass);

        // Счета
        $options = array(
            'user_id'      => $user->getId(),
            'account_name' => 'USD Account For stat test',
            'account_currency_id' => myMoney::USD,
        );

        $account = CreateObjectHelper::makeAccount($options);
        $operation  = new Operation_Model($user);

        $options = array(
            'user_id'  => $user->getId(),
        );

        $catId = CreateObjectHelper::createCategory($options);

        $dateFrom = '2009-12-29';
        $dateTo   = '2010-01-02';

        $rate  = sfConfig::get('ex')->getRate(myMoney::UAH, myMoney::USD);
        $usdSpent = 100;
        $uahSpent = $usdSpent / $rate ;

        $opId = $operation->add(
                Operation::TYPE_EXPENSE,
                $usdSpent,
                '2010-01-01',
                $catId,
                'Комментарий',
                $account['account_id'],
                array('тег 1')
        );

        $stat = $operation->getOperationList($dateFrom, $dateTo, null, null, -1, null, null, null, true);

        $this->assertEquals(round($uahSpent, 2), round(abs($stat), 2), 'Expected 100 dollars have been spent');
    }

    /**
     * Тест перевода на долговой счёт
     */
    public function testDebtTransfer()
    {
        $this->_prepareOperation();

         // Долговой счёт
        $options = array(
            'user_id'      => $this->userId,
            'account_name' => 'Debt account',
            'account_currency_id' => myMoney::RUR,
            'account_type_id' => Account_Collection::ACCOUNT_TYPE_CREDIT
        );

        $account = CreateObjectHelper::makeAccount($options);
        $toAccountId = $account['account_id'];

        $options = array(
            'user_id' => $this->userId,
            'system_category_id' => Category_Model::DEBT_SYSTEM_CATEGORY_ID,
        );
        $debtCategoryId = CreateObjectHelper::createCategory($options);

        $this->user->init();
        $this->user->save();

        $operation  = new Operation_Model($this->user);

        // Перевели 100 рублей с рублёвого на долларовый
        $opId = $operation->addTransfer(
                100,
                0,
                0,
                '2010-01-01',
                $this->accountId,
                $toAccountId,
                'Комментарий',
                array('тег 1')
        );

        $dateFrom = '2009-12-29';
        $dateTo   = '2010-01-02';

        $list = $operation->getOperationList($dateFrom, $dateTo);

        $this->assertEquals($debtCategoryId, $list[0]['cat_id'], 'Expected only 1 transfer operation');
    }

}
