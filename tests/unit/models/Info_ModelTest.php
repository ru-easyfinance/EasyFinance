<?php

require_once dirname(__FILE__) . '/../bootstrap.php';

/**
 * Тест для модели "Инфо" (тахометров на странице инфо)
 */
class Info_ModelTest extends UnitTestCase
{
    private $userId    = null;
    private $userLogin = null;
    private $userPass  = null;
    /** @var oldUser */
    private $user      = null;

    function _start()
    {
        $this->userLogin = 'someLogin'. mktime();
        $this->userPass  = 'somePass';

        $options = array(
            'user_login' => $this->userLogin,
            'user_pass'  => sha1($this->userPass),
            'user_active'=> 1,
            'user_new'   => 0,

        );
        CreateObjectHelper::makeUser($options);
    }

    private function _makeOperation()
    {
        $this->user = new oldUser($this->userLogin, $this->userPass);
        $this->userId = $this->user->getId();

        $account = CreateObjectHelper::makeAccount(array('user_id'=>$this->userId));
        $accountId = $account['account_id'];

        $options   = array(
            'user_id'  => $this->userId,
            'chain_id' => 999,
            'date'     => date('Y-m-d', time()-(86400*31)),
            'account_id' => $accountId,
        );
        // Правильные операции, месячной давности
        CreateObjectHelper::createOperation($options);
        CreateObjectHelper::createOperation($options);
        CreateObjectHelper::createOperation($options);

        // Операция не выполнена
        $options['accepted'] = 0;
        CreateObjectHelper::createOperation($options);


        // Доходная операция
        $options['drain'] = 0;
        CreateObjectHelper::createOperation($options);

        // Дата операции установлена на завтра, но она отмечена выполненной
        $options['accepted'] = 1;
        $options['date'] = date('Y-m-d', time()+86400);
        CreateObjectHelper::createOperation($options);

        // Удалённая операция
        $options['deleted_at'] = '2010-02-02 02:02:02';
        CreateObjectHelper::createOperation($options);

        // Обычная операция, вне цепочки
        unset($options['deleted_at']);
        unset($options['chain_id']);
        CreateObjectHelper::createOperation($options);

        $options = array(
            'user_id' => $this->user->getId(),
        );
        $catId1     = CreateObjectHelper::createCategory($options);
        $catId2     = CreateObjectHelper::createCategory($options);
        $accountId = CreateObjectHelper::makeAccount($options);

        $options['category']  = $catId1;
        $options['drain']     = 0;
        CreateObjectHelper::createBudget($options);

        $options['category']  = $catId2;
        $options['drain']     = 1;
        CreateObjectHelper::createBudget($options);
    }

    public function testGetData()
    {
        // Дурацкий тест. Не совсем понятно как его ещё проверять.
        $this->_makeOperation();

        $info = new Info_Model($this->user);

        $data = $info->get_data();

        $actual = array();
        foreach($data as $value) {
            unset($value['description']);
            unset($value['title']);
            $actual[] = $value;
        }

        $expected = array(
            array('value' => 28),
            array('value' => 0),
            array('value' => 35),
            array('value' => 0),
            array('value' => 8),
        );
        $this->assertEquals($expected, $actual, 'Expected equals arrays');
    }
}
