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

        // Счет
        $this->assertType('Account', $op->Account);

        // Категория
        $this->assertType('Category', $op->Category);

        // Операция из внешнего источника
        $this->assertType('SourceOperation', $op->SourceOperation);
    }


    /**
     * Создание объекта, алиасы
     */
    public function testMakeRecord()
    {
        $user = $this->helper->makeUser();
        $account = $this->helper->makeAccount($user);

        $data = array(
            'user_id'     => $user->getId(),
            'account_id'  => $account->getId(),
            'category_id' => $this->helper->makeCategory($user)->getId(),
            'amount'      => 1234.56,
        );
        $this->checkModelDeclaration('Operation', $data, $isTimestampable = true);
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


    /**
     * Невозможно удалить операцию, если у нее есть счет
     */
    public function testFailedDeleteOperationIdConnectedWithAccount()
    {
        $operation = $this->helper->makeOperation();

        $this->setExpectedException('Doctrine_Connection_Mysql_Exception', 'foreign key constraint fails');
        $operation->getAccount()->hardDelete();
    }


    /**
     * Невозможно удалить операцию, если у нее есть категория
     */
    public function testFailedDeleteOperationIdConnectedWithCategory()
    {
        $operation = $this->helper->makeOperation();

        $this->setExpectedException('Doctrine_Connection_Mysql_Exception', 'foreign key constraint fails');
        $operation->getCategory()->hardDelete();
    }


    /**
     * SoftDelete
     */
    public function testSoftDelete()
    {
        $operation = $this->helper->makeOperation();
        $operation->delete();

        $this->assertEquals($operation->getUpdatedAt(), $operation->getDeletedAt());
    }

}
