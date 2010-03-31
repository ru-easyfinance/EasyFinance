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
        $op = $this->helper->makeOperation();

        $this->assertNotEquals('0000-00-00 00:00:00', $op->getDtCreate(), 'CreatedAt');
        $this->assertNotEquals('0000-00-00 00:00:00', $op->getDtUpdate(), 'UpdateAt');
    }


    /**
     * При удалении пользователя, удаляются все его операции
     */
    public function testOnDeleteUserCascade()
    {
        $user = $this->helper->makeUser();
        $op   = $this->helper->makeOperation($user);

        $user->delete();
        $this->assertEquals(0, $op->getTable()->createQuery()->count());
    }

}
