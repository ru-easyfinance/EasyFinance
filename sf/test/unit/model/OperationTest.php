<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Операции
 */
class model_OperationTest extends myUnitTestCase
{
    /**
     * Отношения
     */
    public function testRelations()
    {
        $op = new Operation;

        // Пользователь
        $this->assertType('User', $op->User);
    }


    /**
     * Timestampable
     */
    public function testTimestampable()
    {
        $op = new Operation;
        $op->save();

        $this->assertNotEquals('0000-00-00 00:00:00', $op->getDtCreate(), 'CreatedAt');
        $this->assertNotEquals('0000-00-00 00:00:00', $op->getDtUpdate(), 'UpdateAt');
    }
}
