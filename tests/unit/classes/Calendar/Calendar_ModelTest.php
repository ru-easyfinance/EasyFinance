<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

/**
 * Класс модели календаря
 */
class classes_Calendar_Calendar_ModelTest  extends UnitTestCase
{
    private $userId = null;

    function __construct()
    {
        $this->userId = CreateObjectHelper::createUser();
    }

    private function _makeOperation()
    {
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

    /**
     * Загружает все операции
     */
    public function testLoadAll()
    {
        $this->_makeOperation();

        $sql = "SELECT COUNT(*)
                FROM operation o
                LEFT JOIN calendar_chains c
                ON c.id=o.chain_id
                WHERE o.user_id = ?
                    AND (o.accepted=0 OR o.chain_id > 0)
                    AND o.deleted_at IS NULL";
        $actual = Core::getInstance()->db->selectCell($sql, $this->userId);
        $this->assertEquals(6, $actual, 'Expected 3 operation');
    }

    /**
     * Загружает все неподтверждённые операции
     */
    public function testLoadOverdue()
    {
        $this->_makeOperation();

        $sql = "SELECT COUNT(*)
                FROM operation o
                LEFT JOIN calendar_chains c ON c.id=o.chain_id
                WHERE o.user_id = ?
                AND o.`date` <= CURRENT_DATE()
                AND o.accepted=0
                AND o.deleted_at IS NULL";
        $actual = Core::getInstance()->db->selectCell($sql, $this->userId);
        $this->assertEquals(1, $actual, 'Expected 1 operation');
    }


    /**
     * Загружает список неподтверждённых операций
     */
    public function testLoadReminder()
    {
        $this->_makeOperation();

        $sql = "SELECT COUNT(*)
                FROM operation o
                LEFT JOIN calendar_chains c ON c.id=o.chain_id
                WHERE o.user_id = ?
                AND o.`date` BETWEEN ADDDATE(CURRENT_DATE(), INTERVAL 1 DAY) AND ADDDATE(CURRENT_DATE(), INTERVAL 8 DAY)
                AND o.accepted=0
                AND o.deleted_at IS NULL";
        $actual = Core::getInstance()->db->selectCell($sql, $this->userId);
        $this->assertEquals(1, $actual, 'Expected 1 operation');
    }


    /**
     * Возвращает количество выполненных событий в цепочке
     */
    public function testLoadAcceptedByChain()
    {
        $this->_makeOperation();

        $sql = "SELECT COUNT(*) FROM operation c WHERE user_id=? AND chain_id=? AND accepted=1 AND deleted_at IS NULL";
        $actual = Core::getInstance()->db->selectCell($sql, $this->userId, 999);
        $this->assertEquals(4, $actual, 'Expected 4 operation');
    }
}
