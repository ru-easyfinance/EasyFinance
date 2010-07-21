<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Таблица операций
 */
class model_OperationTableTest extends myUnitTestCase
{
    /**
     * Таблица просроченных операций из календаря
     */
    public function testQueryFindWithCalendarChains()
    {
        $user = $this->helper->makeUser();
        $account = $this->helper->makeAccount($user);

        $user2 = $this->helper->makeUser();
        $account2 = $this->helper->makeAccount($user2);

        $yesterday  = date('Y-m-d', time() - 24*60*60);
        $now        = date('Y-m-d', time());

       /*
       * Фикстуры (операции) (время операции в БД не хранится, только дата):
       * 0. Не привязана к календарю
       * 1. Не привязана к календарю другого юзера
       * 2. Подтвержденная, дата=вчера
       * 3. Подтвержденная, дата=завтра
       * 4. Удаленная, дата=вчера
       * 5. Удаленная, дата=завтра
       * 6. Дата вчера
       * 7. Дата сегодня
       * 8. Дата завтра
       * 9. С датой через 8 суток
       * 10. С датой через 9 суток
       * 11. Другого юзера вчера
       * 12. Другого юзера завтра
       * 13. С датой через 2 суток
       * 14. С датой через 7 суток
       */

        $op0 = $this->helper->makeOperation($account);
        $op1 = $this->helper->makeOperation($account2);

        $cc1 = $this->helper->makeCalendarChain($account);
        $cc2 = $this->helper->makeCalendarChain($account2);

        $op2  = $this->helper->makeCalendarOperation($cc1, $account, 'op2',  -1, array('accepted' => Operation::STATUS_ACCEPTED));
        $op3  = $this->helper->makeCalendarOperation($cc1, $account, 'op3',   1, array('accepted' => Operation::STATUS_ACCEPTED));
        $op4  = $this->helper->makeCalendarOperation($cc1, $account, 'op4',  -1, array('deleted_at' => $now));
        $op5  = $this->helper->makeCalendarOperation($cc1, $account, 'op5',   1, array('deleted_at' => $now));
        $op6  = $this->helper->makeCalendarOperation($cc1, $account, 'op6',  -1);
        $op7  = $this->helper->makeCalendarOperation($cc1, $account, 'op7',   0);
        $op8  = $this->helper->makeCalendarOperation($cc1, $account, 'op8',   1);
        $op9  = $this->helper->makeCalendarOperation($cc1, $account, 'op9',   8);
        $op10 = $this->helper->makeCalendarOperation($cc1, $account, 'op10',  9);
        $op11 = $this->helper->makeCalendarOperation($cc2, $account2,'op11', -1);
        $op12 = $this->helper->makeCalendarOperation($cc2, $account2,'op12',  1);
        $op13 = $this->helper->makeCalendarOperation($cc1, $account, 'op13',  2);
        $op14 = $this->helper->makeCalendarOperation($cc1, $account, 'op14',  7);

        // Проверяем просроченные операции
        $overdue = Doctrine::getTable('Operation')->queryFindWithOverdueCalendarChains($user)->execute();
        $this->assertEquals(2, $overdue->count(), "Overdue operations count");
        $this->assertModels($op6, $overdue->get(0));
        $this->assertModels($op7, $overdue->get(1));

        // Проверяем будущие операции
        $future = Doctrine::getTable('Operation')->queryFindWithFutureCalendarChains($user)->execute();
        $this->assertEquals(4, $future->count(), "Future operations count");
        $this->assertModels($op8,  $future->get(0));
        $this->assertModels($op9,  $future->get(1));
        $this->assertModels($op13, $future->get(2));
        $this->assertModels($op14, $future->get(3));
    }


    /**
     * Посчитать кол-во операций по счетам пользователя за месяц
     */
    public function testGetMonthCountByUser()
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
