<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

/**
 * Класс модели календаря
 */
class classes_Calendar_Calendar_ModelTest  extends UnitTestCase
{
    private $userId    = null;
    private $userLogin = null;
    private $userPass  = null;
    /** @var User */
    private $user      = null;


    /**
     * SetUp
     */
    function _start()
    {
        $this->userLogin = 'someLogin';
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
            'chain_id' => 999,
            'date'     => date('Y-m-d', time()-86400),
        );
        // Правильные операции, на вчера
        CreateObjectHelper::createOperation($options);
        CreateObjectHelper::createOperation($options);
        CreateObjectHelper::createOperation($options);

        // Операция не выполнена
        $data = $options;
        $data['accepted'] = 0;
        CreateObjectHelper::createOperation($data);


        // Дата операции установлена на завтра
        $data = $options;
        $data['accepted'] = 0;
        $data['date'] = date('Y-m-d', time()+86400);
        CreateObjectHelper::createOperation($data);

        // Дата операции установлена на завтра, но она отмечена выполненной
        $data = $options;
        $data['accepted'] = 1;
        $data['date'] = date('Y-m-d', time()+86400);
        CreateObjectHelper::createOperation($data);

        // Удалённая операция
        $data = $options;
        $data['accepted'] = 0;
        $data['deleted_at'] = '2010-02-02 02:02:02';
        CreateObjectHelper::createOperation($data);

        // Обычная операция, вне цепочки
        unset($options['deleted_at']);
        unset($options['chain_id']);
        CreateObjectHelper::createOperation($options);
    }


    /**
     * Загружает все операции
     */
    public function testLoadAll()
    {
        $this->_makeOperation();

        $start = new DateTime('-1week');
        $end   = new DateTime('+1week');

        $calendar = new Calendar($this->user);

        $calendar->loadAll($this->user, $start->format('U'), $end->format('U'));

        $actual = $calendar->getArray();
        $this->assertEquals(6, count($actual), 'Expected 6 operation');
    }


    /**
     * Загружает все неподтверждённые операции
     */
    public function testLoadOverdue()
    {
        $this->_makeOperation();
        $calendar = new Calendar($this->user);
        $calendar->loadOverdue($this->user);

        $result = $calendar->getArray();
        $this->assertEquals(1, count($result), 'Expected 1 operation');
        $item = current($result);
        $this->assertEquals(0, $item['accepted']);
    }


    /**
     * Загружает список неподтверждённых операций
     */
    public function testLoadReminder()
    {
        $this->_makeOperation();

        $calendar = new Calendar($this->user);
        $calendar->loadReminder($this->user);
        $result = $calendar->getArray();
        $this->assertEquals(1, count($result), 'Expected 1 operation');
    }
}
