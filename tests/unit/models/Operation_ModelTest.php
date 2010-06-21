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
        $options   = array(
            'user_id'  => $this->userId,
            'account_name' => 'ABC'
        );
        $account = CreateObjectHelper::makeAccount($options);
        $this->accountId = $account['account_id'];


        $options['account_name'] = 'ABC 2';
        $account = CreateObjectHelper::makeAccount($options);
        $this->accountId2 = $account['account_id'];

        // Категории
        $options   = array(
            'user_id'  => $this->userId,
        );
        $this->catId     = CreateObjectHelper::createCategory($options);

        // Это важный метод. Он подгрузит все счета, категории пользователя (и что-нибудь ещё)
        $this->user->init();
        $this->user->save();
    }

    /**
     * Создаём операции
     */
    private function _makeOperation()
    {
        $options   = array(
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
            'user_id'       => $this->userId,
            'money'         => 100,
            'time'          => '00:00:00',
            'date'          => '2010-01-01',
            'cat_id'        => $this->catId,
            'account_id'    => $this->accountId,
            'drain'         => 0,
            'comment'       => 'Комментарий',
            'transfer'      => 0,
            'tr_id'         => null,
            'imp_id'        => null,
            'tags'          => 'тег1',
            'type'          => Operation::TYPE_PROFIT,
            'source_id'     => null,
            'accepted'      => 1,
            'chain_id'      => 0,
            'exchange_rate' => 0.000000,
            'deleted_at'    => null,
        );

        $operation  = new Operation_Model($this->user);
        $opId = $operation->add(
                100,
                '2010-01-01',
                $this->catId,
                0,
                "Комментарий",
                $this->accountId,
                array('тег1')
        );

        $expected = array_merge(array('id' => $opId), $expected);

        // Получаем созданную операцию из БД
        $sql = "SELECT * FROM operation WHERE id=?";
        $actual = $this->getConnection()->selectRow($sql, $opId);

        // Смотрим на дату создания и редактирования
        $this->assertGreaterThan(mktime(+1), strtotime($actual['created_at']));

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
            'user_id'       => $this->userId,
            'money'         => -100,
            'time'          => '00:00:00',
            'date'          => '2010-01-01',
            'cat_id'        => $this->catId,
            'account_id'    => $this->accountId,
            'drain'         => 1,
            'comment'       => 'Комментарий',
            'transfer'      => 0,
            'tr_id'         => null,
            'imp_id'        => null,
            'tags'          => 'тег1',
            'type'          => Operation::TYPE_WASTE,
            'source_id'     => null,
            'accepted'      => 1,
            'chain_id'      => 0,
            'exchange_rate' => 0.000000,
            'deleted_at'    => null,
        );

        $operation  = new Operation_Model($this->user);
        $opId = $operation->add(
                -100,
                '2010-01-01',
                $this->catId,
                1,
                "Комментарий",
                $this->accountId,
                array('тег1')
        );

        $expected = array_merge(array('id' => $opId), $expected);

        $sql = "SELECT * FROM operation WHERE id=?";
        $actual = $this->getConnection()->selectRow($sql, $opId);

        // Смотрим на дату создания и редактирования
        $this->assertGreaterThan(mktime(+1), strtotime($actual['created_at']));

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

        $expected = array(array(
            'id'            => $opId,
            'user_id'       => $this->userId,
            'money'         => -100,
            'time'          => '00:00:00',
            'date'          => '2010-01-01',
            'cat_id'        => null,
            'account_id'    => $this->accountId,
            'drain'         => 1,
            'comment'       => 'Комментарий',
            'transfer'      => $this->accountId2,
            'tr_id'         => 0,
            'imp_id'        => null,
            'tags'          => null,
            'type'          => Operation::TYPE_TRANSFER,
            'source_id'     => null,
            'accepted'      => 1,
            'chain_id'      => 0,
            'exchange_rate' => 0.000000,
            'deleted_at'    => null,
        ),array(
            'id'            => $opId+1,
            'user_id'       => $this->userId,
            'money'         => 100,
            'time'          => '00:00:00',
            'date'          => '2010-01-01',
            'cat_id'        => null,
            'account_id'    => $this->accountId2,
            'drain'         => 1,
            'comment'       => 'Комментарий',
            'transfer'      => $this->accountId,
            'tr_id'         => $opId,
            'imp_id'        => 100.00,
            'tags'          => null,
            'type'          => Operation::TYPE_TRANSFER,
            'source_id'     => null,
            'accepted'      => 1,
            'chain_id'      => 0,
            'exchange_rate' => 0.000000,
            'deleted_at'    => null,
        ));

        
        $sql = "SELECT * FROM operation WHERE id=? OR tr_id=?";
        $actual = $this->getConnection()->select($sql, $opId, $opId);

        unset($actual[0]['created_at']);
        unset($actual[0]['updated_at']);
        unset($actual[1]['created_at']);
        unset($actual[1]['updated_at']);

        $this->assertEquals($expected, $actual, 'Expected equals operation');
    }
}
