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
    public function testQueryFindWithOverdueCalendarChains()
    {
        $user = $this->helper->makeUser();
        $account = $this->helper->makeAccount($user);

        $user2 = $this->helper->makeUser();
        $account2 = $this->helper->makeAccount($user2);

        $yesterday  = date('Y-m-d', time() - 24*60*60);
        $now        = date('Y-m-d', time());

       /*
       * Фикстуры (операции):
       * 0. Не привязана к календарю
       * 1. Подтвержденная
       * 2. Удаленная
       * 3. В будущем
       * 4. Добавлена только что
       * 5. Другого юзера
       * 6-7. Просроченные
       */

        $op0 = $this->helper->makeOperation($account);

        $cc1 = $this->helper->makeCalendarChain($account);
        $cc2 = $this->helper->makeCalendarChain($account2);

        $op1 = $this->helper->makeCalendarOperation($cc1, $account, 'op1', -1, array('accepted' => Operation::STATUS_ACCEPTED));
        $op2 = $this->helper->makeCalendarOperation($cc1, $account, 'op2', -1, array('deleted_at' => $now));
        $op3 = $this->helper->makeCalendarOperation($cc1, $account, 'op3', 1);
        $op4 = $this->helper->makeCalendarOperation($cc1, $account, 'op4', 0);
        $op5 = $this->helper->makeCalendarOperation($cc2, $account2, 'op5');
        $op6 = $this->helper->makeCalendarOperation($cc1, $account, 'op6');
        $op7 = $this->helper->makeCalendarOperation($cc1, $account, 'op7', -2);

        // Запись в календаре с операциями
        $result = Doctrine::getTable('Operation')->queryFindWithOverdueCalendarChains($user)->execute();
        $this->assertEquals(3, $result->count(), "Operations count");
        // Если операция создана только что (в ту же секунду), она сразу становится просроченной
        $this->assertModels($op4, $result->get(0));
        $this->assertModels($op6, $result->get(1));
        $this->assertModels($op7, $result->get(2));
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
