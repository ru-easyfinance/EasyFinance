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
            'type'        => Operation::TYPE_PROFIT,
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
     * Невозможно удалить счёт, если по нему есть операции перевода
     */
    public function testFailedDeleteOperationIdConnectedWithTransferAccount()
    {
        $transfer = $this->helper->makeAccount();

        $prop = array(
            'transfer_account_id' => $transfer->id,
        );

        $op = $this->helper->makeOperation($this->helper->makeAccount(), $prop);

        $this->setExpectedException('Doctrine_Connection_Mysql_Exception', 'foreign key constraint fails');
        $transfer->hardDelete();
    }


    /**
     * SoftDelete
     */
    public function testSoftDelete()
    {
        $operation = $this->helper->makeOperation();
        $operation->delete();

        $this->assertEquals(
            strtotime($operation->getUpdatedAt()),
            strtotime($operation->getDeletedAt()),
            'UpdatedAt и CreatedAt должны быть приближённо равны',
            10
        );
    }


    /**
     * Исправление ошибок в операции перевода
     */
    public function testPreHydrateHook()
    {
        $account         = $this->helper->makeAccount(
            null,
            array('currency_id' => myMoney::RUR)
        );
        $transferAccount = $this->helper->makeAccount(
            $account->getUser(),
            array('currency_id' => myMoney::USD)
        );
        $operation       = $this->helper->makeOperation(
            $account,
            array(
                'amount'              => 123,
                'type'                => Operation::TYPE_TRANSFER,
                'transfer_amount'     => 0,
                'transfer_account_id' => $transferAccount->getId()
            )
        );

        $hydratedOperation = $operation->getTable()
            ->findOneById($operation->getId());

        $rate = $this->getContext()->getMyCurrencyExchange()
            ->getRate(myMoney::RUR, myMoney::USD);

        $this->assertEquals(
            abs(floor($rate * $hydratedOperation->getAmount() * 100) / 100),
            abs($hydratedOperation->getTransferAmount()),
            'transfer_amount должен вычисляться, если он 0',
            0.01
        );
    }


    /**
     * Добавление категории к переводу на долговой счёт
     */
    public function testAddingDebtCategoryToTransfer()
    {
        $user = $this->helper->makeUser();
        $debtCategory = $this->helper->makeCategory(
            $user,
            array('system_id' => Category::DEBT_SYSTEM_CATEGORY_ID)
        );
        $account = $this->helper->makeAccount(
            $user,
            array(
                'type_id' => Account::TYPE_CASH
            )
        );
        $transferAccount = $this->helper->makeAccount(
            $user,
            array(
                'type_id' => Account::TYPE_CREDIT
            )
        );

        $data = array(
            'type'                => Operation::TYPE_TRANSFER,
            'user_id'             => $user->getId(),
            'account_id'          => $account->getId(),
            'transfer_account_id' => $transferAccount->getId(),
            'category_id'         => null,
            'amount'              => 1234.56,
        );

        $operation = $this->helper->makeOperation(
            $account,
            $data,
            true
        );

        $this->assertEquals(
            $debtCategory->getId(),
            $operation->getCategory()->getId(),
            'Должна подставляться долговая категория'
        );
    }

}
