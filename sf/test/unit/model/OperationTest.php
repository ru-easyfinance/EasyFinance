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

        // Операция из внешнего источника
        $this->assertType('SourceOperation', $op->SourceOperation);
    }


    /**
     * Timestampable
     */
    public function testTimestampable()
    {
        $op = $this->helper->makeOperation();

        $this->assertNotEquals('0000-00-00 00:00:00', $op->getDtCreate(), 'CreatedAt');
        $this->assertNotEquals('0000-00-00 00:00:00', $op->getDtUpdate(), 'UpdateAt');
    }


    /**
     * Невозможно удалить операцию, если у нее есть пользователь
     */
    public function testFailedDeleteOperationIdConnectedWithUser()
    {
        $op = $this->helper->makeOperation();

        $this->setExpectedException('Doctrine_Connection_Mysql_Exception', 'foreign key constraint fails');
        $op->getUser()->delete();
    }

}
