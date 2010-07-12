<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Таблица операций
 */
class model_OperationTableTest extends myUnitTestCase
{
    /**
     * Посчитать кол-во операций по счетам пользователя за месяц
     */
    public function testQueryFindMonthCountByUser()
    {
        $user = $this->helper->makeUser();

        $account1 = $this->helper->makeAccount($user);
        $account2 = $this->helper->makeAccount($user);

        // балансовые операции, которые не нужно учитывать при подсчете (ну по идее)
        $op1 = $this->helper->makeBalanceOperation($account1, 1000);
        $op2 = $this->helper->makeBalanceOperation($account2, -1000);

        // это у другого пользователя
        $this->helper->makeBalanceOperation(null, 10000);
        $this->helper->makeOperationCollection(5);

        // слишком давние операции
        $this->helper->makeOperationCollection(2, $account1, array(
            array('updated_at' => date('Y-m-d', strtotime('-40 day'))),
            array('updated_at' => date('Y-m-d', strtotime('-40 day'))),
        ));

        // операции перевода со счета на счет
        $op3 = $this->helper->makeOperation($account1, array(
            'amount'              => -3333.14,
            'transfer_account_id' => $account2->getId(),
            'transfer_amount'     => 3333.14,
            'category_id'         => null,
            'type'                => Operation::TYPE_TRANSFER,
        ));

        // набор операций для рассчета "частоты использования"
        $coll1 = $this->helper->makeOperationCollection(5, $account1);
        $coll2 = $this->helper->makeOperationCollection(10, $account2);

        // в наборе будут и операции перевода
        $coll1->add($op3);
        $coll2->add($op3);

        $result = Doctrine::getTable('Operation')->getMonthCountByUser($user);

        $this->assertEquals(2, count($result), "Кол-во счетов");
        $this->assertEquals($coll1->count(), $result[$account1->getId()], "Кол-во операций по 1 счету");
        $this->assertEquals($coll2->count(), $result[$account2->getId()], "Кол-во операций по 2 счету");
    }

}
