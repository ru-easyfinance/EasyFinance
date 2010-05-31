<?php
require_once dirname(__FILE__) . '/../bootstrap.php';

/**
 * Тест операций
 */
class Operation_ModelTest extends UnitTestCase
{
    private $userId    = null;
    private $userLogin = null;
    private $userPass  = null;
    /** @var User */
    private $user      = null;
    private $model     = null;
    private $accountId = null;
    private $catId     = null;

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
        CreateObjectHelper::createUser($options);
    }

    private function _makeOperation()
    {
        $this->user = new oldUser($this->userLogin, $this->userPass);
        $this->userId = $this->user->getId();

        $options   = array(
            'user_id'  => $this->userId,
            'account_name' => 'ABC'
        );
        $this->accountId = CreateObjectHelper::createAccount($options);

        $options   = array(
            'user_id'  => $this->userId,
        );
        $this->catId     = CreateObjectHelper::createCategory($options);

        // Это важный метод. Он подгрузит все счета, категории пользователя (и что-нибудь ещё)
        $this->user->init();

        $options   = array(
            'user_id'    => $this->userId,
            'chain_id'   => 999,
            'date'       => date('Y-m-d', time()-86400),
            'cat_id'     => $this->catId,
            'account_id' => $this->accountId,

        );
        // Правильные операции, на вчера
        CreateObjectHelper::createOperation($options);
        CreateObjectHelper::createOperation($options);
        CreateObjectHelper::createOperation($options);

        // Операция не выполнена
        $options['accepted'] = 0;
        CreateObjectHelper::createOperation($options);


        // Дата операции установлена на завтра
        $options['date'] = date('Y-m-d', time()+86400);
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
    }


    public function testGetOperationList()
    {
        $this->_makeOperation();
        $this->model = new Operation_Model($this->user);

        $start = new DateTime('-1week');
        $end   = new DateTime('+1week');

        $operations = $this->model->getOperationList($start->format('Y-m-d'), $end->format('Y-m-d'), 0, $this->accountId, null, null, null);

        $this->assertEquals(5, count($operations), 'Expected 5 operations');
    }
}
