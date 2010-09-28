<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Таблица операций
 */
class model_OperationTableTest extends myUnitTestCase
{

    /**
    * Создаем фикстуры для теста выборок просроченных и будущих операций
    * Наполняем массив операций
    *
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
    *
    * @param User   $user
    * @param array  $operations
    */
    private function makeFixturesForOverdueAndFuture(User $user, array &$operations)
    {
        $account = $this->helper->makeAccount($user);

        $user2 = $this->helper->makeUser();
        $account2 = $this->helper->makeAccount($user2);

        $yesterday  = date('Y-m-d', time() - 24*60*60);
        $now        = date('Y-m-d', time());

        $operations[0] = $this->helper->makeOperation($account);
        $operations[1] = $this->helper->makeOperation($account2);

        $cc1 = $this->helper->makeCalendarChain($account);
        $cc2 = $this->helper->makeCalendarChain($account2);

        $operations[2]  = $this->helper->makeCalendarOperation($cc1, $account, 'op2',  -1,
            array('accepted' => Operation::STATUS_ACCEPTED));
        $operations[3]  = $this->helper->makeCalendarOperation($cc1, $account, 'op3',   1,
            array('accepted' => Operation::STATUS_ACCEPTED));
        $operations[4]  = $this->helper->makeCalendarOperation($cc1, $account, 'op4',  -1,
            array('deleted_at' => $now));
        $operations[5]  = $this->helper->makeCalendarOperation($cc1, $account, 'op5',   1,
            array('deleted_at' => $now));
        $operations[6]  = $this->helper->makeCalendarOperation($cc1, $account, 'op6',  -1);
        $operations[7]  = $this->helper->makeCalendarOperation($cc1, $account, 'op7',   0);
        $operations[8]  = $this->helper->makeCalendarOperation($cc1, $account, 'op8',   1);
        $operations[9]  = $this->helper->makeCalendarOperation($cc1, $account, 'op9',   8);
        $operations[10] = $this->helper->makeCalendarOperation($cc1, $account, 'op10',  9);
        $operations[11] = $this->helper->makeCalendarOperation($cc2, $account2,'op11', -1);
        $operations[12] = $this->helper->makeCalendarOperation($cc2, $account2,'op12',  1);
        $operations[13] = $this->helper->makeCalendarOperation($cc1, $account, 'op13',  2);
        $operations[14] = $this->helper->makeCalendarOperation($cc1, $account, 'op14',  7);
    }

    /**
    * Проверяем выборку просроченных операций
    *
    * @param User   $user
    * @param array  $operations
    */
    private function doTestQueryOverdue(User $user, array $operations)
    {
        $overdue = Doctrine::getTable('Operation')->queryFindWithOverdueCalendarChains($user)->execute();
        $this->assertEquals(2, $overdue->count(), "Overdue operations count");
        $this->assertModels($operations[6], $overdue->get(0));
        $this->assertModels($operations[7], $overdue->get(1));
    }

    /**
    * Проверяем выборку будущих операций
    *
    * @param User   $user
    * @param array  $operations
    */
    private function doTestQueryFuture(User $user, array $operations)
    {
        $future = Doctrine::getTable('Operation')->queryFindWithFutureCalendarChains($user)->execute();
        $this->assertEquals(4, $future->count(), "Future operations count");
        $this->assertModels($operations[8],  $future->get(0));
        $this->assertModels($operations[9],  $future->get(1));
        $this->assertModels($operations[13], $future->get(2));
        $this->assertModels($operations[14], $future->get(3));
    }

    /**
     * Таблицы будущих и просроченных операций из календаря
     */
    public function testQueryFindWithCalendarChains()
    {
        $user = $this->helper->makeUser();

        $operations = array();
        $this->makeFixturesForOverdueAndFuture($user, $operations);

        // Проверяем просроченные операции
        $this->doTestQueryOverdue($user, $operations);

        // Проверяем будущие операции
        $this->doTestQueryFuture($user, $operations);
    }

    /**
    * Создаем фикстуры для теста выборки за период.
    * Наполняем массив операций
    *
    * Фикстуры (операции) (время операции в БД не хранится, только дата):
    * 0. Не привязана к календарю, 1-е число
    * 1. Не привязана к календарю другого юзера, 1-е число
    * 2. Подтвержденная, 1-е число
    * 3. Удаленная, 1-е число
    * 4. 1-е число
    * 5. Подтвержденная, 1-е число, прошлый месяц
    * 6. Удаленная, 1-е число, прошлый месяц
    * 7. 1-е число, прошлый месяц
    * 8. 1-е число, следующий месяц
    * 9. 9 число
    * 10. Последнее число месяца
    * 11. Последнее число прошлого месяца
    *
    * @param User   $user
    * @param array  $operations
    */
    private function makeFixturesForPeriod(User $user, array &$operations)
    {
        $account = $this->helper->makeAccount($user);

        $user2 = $this->helper->makeUser();
        $account2 = $this->helper->makeAccount($user2);

        $dateObj = DateTime::createFromFormat('Y-m-d', date('Y-m-', time()) . '01');
        $thisMonth9 = DateTime::createFromFormat('Y-m-d', date('Y-m-', time()) . '09');

        // Первое число месяца
        $date = $dateObj->format('Y-m-d');
        $operations[0] = $this->helper->makeOperation($account, array('date' => $date));
        $operations[1] = $this->helper->makeOperation($account2, array('date' => $date));

        $cc1 = $this->helper->makeCalendarChain($account);
        $cc2 = $this->helper->makeCalendarChain($account2);

        $operations[2]  = $this->helper->makeCalendarOperation($cc1, $account, 'op2', 0,
            array('date'=>$date, 'accepted'=>Operation::STATUS_ACCEPTED));
        $operations[3]  = $this->helper->makeCalendarOperation($cc1, $account, 'op4', 0,
            array('date'=>$date, 'deleted_at'=>$thisMonth9->format('Y-m-d')));
        $operations[4]  = $this->helper->makeCalendarOperation($cc1, $account, 'op6', 0,
            array('date' => $date));

        // Первое число прошлого месяца
        $date = $dateObj->sub(new DateInterval('P1M'))->format('Y-m-d');
        $operations[5]  = $this->helper->makeCalendarOperation($cc1, $account, 'op3', 0,
            array('date'=>$date, 'accepted'=>Operation::STATUS_ACCEPTED));
        $operations[6]  = $this->helper->makeCalendarOperation($cc1, $account, 'op5', 0,
            array('date'=>$date, 'deleted_at'=>$thisMonth9->format('Y-m-d')));
        $operations[7]  = $this->helper->makeCalendarOperation($cc1, $account, 'op7', 0,
            array('date'=>$date));

        // Первое число следующего месяца
        $date = $dateObj->add(new DateInterval('P2M'))->format('Y-m-d');
        $operations[8]  = $this->helper->makeCalendarOperation($cc1, $account, 'op8', 0,
            array('date' => $date));

        // Середина месяца, например, 9-е число
        $operations[9]  = $this->helper->makeCalendarOperation($cc1, $account, 'op9', 0,
            array('date'=>$thisMonth9->format('Y-m-d')));

        // Последнее число месяца
        $date = $dateObj->sub(new DateInterval('P1D'))->format('Y-m-d');
        $operations[10] = $this->helper->makeCalendarOperation($cc1, $account, 'op10', 0,
            array('date' => $date));

        // Последнее число прошлого месяца
        $date = $dateObj
            ->add(new DateInterval('P1D')) // Первое число следующего месяца
            ->sub(new DateInterval('P1M1D')) // Последнее число предыдущего месяца
            ->format('Y-m-d');
        $operations[11] = $this->helper->makeCalendarOperation($cc1, $account, 'op11', 0, array('date'=>$date));

        // Визуально проверяем, что у фикстур правильные даты
        //foreach($operations as $i=>$val) {
        //    var_dump($i. ' '.$val->getDate().' '.$val->getDeletedAt(). ' '.$val->getAccepted());
        //}
    }

    /**
    * Проверяем выборку операций за текущий месяц
    *
    * @param User   $user
    * @param array  $operations
    */
    private function doTestQueryForCurrentMonth(User $user, array $operations)
    {
        $date1 = DateTime::createFromFormat('Y-m-d', date('Y-m-', time()) . '01');
        $date2 = DateTime::createFromFormat('Y-m-d', date('Y-m-', time()) . '01')
                    ->add(new DateInterval('P1M'))
                    ->sub(new DateInterval('P1D'))
            ;

        $result = Doctrine::getTable('Operation')
            ->queryFindWithCalendarChainsForPeriod($user, $date1, $date2)
            ->execute();
        $this->assertEquals(4, $result->count(), "Calendar operations count (this month)");
        $this->assertModels($operations[2],  $result->get(0));
        $this->assertModels($operations[4],  $result->get(1));
        $this->assertModels($operations[9],  $result->get(2));
        $this->assertModels($operations[10], $result->get(3));
    }

    /**
    * Проверяем выборку операций за предыдущий месяц
    *
    * @param User   $user
    * @param array  $operations
    */
    private function doTestQueryForPreviousMonth(User $user, array $operations)
    {
        $date1 = DateTime::createFromFormat('Y-m-d', date('Y-m-', time()) . '01')
                    ->sub(new DateInterval('P1M'));
        $date2 = DateTime::createFromFormat('Y-m-d', date('Y-m-', time()) . '01')
                    ->sub(new DateInterval('P1D'));

        $result = Doctrine::getTable('Operation')
            ->queryFindWithCalendarChainsForPeriod($user, $date1, $date2)
            ->execute();
        $this->assertEquals(3, $result->count(), "Calendar operations count (prev month)");
        $this->assertModels($operations[5],  $result->get(0));
        $this->assertModels($operations[7],  $result->get(1));
        $this->assertModels($operations[11], $result->get(2));
    }

    /**
     * Таблицы операций из календаря, выборка за период
     */
    public function testQueryFindWithCalendarChainsForPeriod()
    {
        $user = $this->helper->makeUser();
        $operations = array();
        $this->makeFixturesForPeriod($user, $operations);

        // Операции за текущий месяц
        $this->doTestQueryForCurrentMonth($user, $operations);

        // Операции за прошлый месяц
        $this->doTestQueryForPreviousMonth($user, $operations);
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

        // t2056: Импортнутая операция (без счета), неподтвержденная? - не может влиять на статистику
        $op4 = $this->helper->makeOperation($account1, array('account_id' => null, 'accepted' => Operation::STATUS_DRAFT,));

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


    /**
     * Найти ID счета последней активной операции пользователя по источнику
     */
    public function testFindAccountIdByLastAcceptedOperationBySource()
    {
        $testsource = "test6666";
        $acc1 = $this->helper->makeAccount();
        $acc2 = $this->helper->makeAccount();

        $op1 = $this->helper->makeOperation($acc2, array(
            'updated_at' => date('Y-m-d', strtotime('-3 day')),
            'source_id'  => $testsource,
        ));

        // найдем эту операцию
        $op2 = $this->helper->makeOperation($acc2, array(
            'updated_at' => date('Y-m-d', strtotime('-1 day')),
            'source_id'  => $testsource,
        ));

        $op3 = $this->helper->makeOperation($acc2, array(
            'source_id'  => $testsource,
        ));
        $op4 = $this->helper->makeOperation($acc1, array(
            'source_id'  => $testsource,
            'accepted'   => 0,
        ));
        $op5 = $this->helper->makeOperation($acc1);
        $op6 = $this->helper->makeOperation($acc1, array(
            'accepted'   => 0,
        ));

        $result = Doctrine_Core::getTable('Operation')
            ->findAccountIdByLastAcceptedOperationBySource($acc2->getUserId(), $testsource);

        $this->assertNotNull($op2->getId(), "id счета не null");
        $this->assertEquals($op2->getAccountId(), $result, "Нашли нужный id счета");
    }


    /**
     * Обновить все неподтвержденные операции пользователя по источнику, присвоить ID счета
     */
    public function testUpdateAccountIdBySourceOperation()
    {
        $testsource = "test6666";
        $account = $this->helper->makeAccount();

        $op1 = $this->helper->makeOperation($account, array(
            'source_id'  => $testsource,
        ));

        $op2 = $this->helper->makeOperation($account, array(
            'source_id'  => $testsource,
            'accepted'   => Operation::STATUS_DRAFT,
        ), false);
        $op2->setAccountId(null);$op2->save();
        $op3 = $this->helper->makeOperation($account, array(
            'source_id'  => $testsource,
            'accepted'   => Operation::STATUS_DRAFT,
        ), false);
        $op3->setAccountId(null);$op3->save();

        $op4 = $this->helper->makeOperation(null, array(
            'source_id'  => $testsource,
            'accepted'   => Operation::STATUS_DRAFT,
        ), false);
        $op4->setAccountId(null);$op4->save();

        $result = Doctrine::getTable("Operation")->updateAccountIdBySourceOperation($op1);

        $this->assertEquals(2, $result);
    }

}
