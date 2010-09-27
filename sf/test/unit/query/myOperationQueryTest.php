<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';
/**
 * myBaseQuery
 */

class myOperationQueryTest extends myUnitTestCase
{
    /**
     * Проверить, происходит ли объединение операций со счетами
     * условия нас не интересуют (они по умолчанию со связей жрутся)
     */
    public function testJoinAcount()
    {
        $user = $this->helper->makeUser();
        $acc1 = $this->helper->makeAccount($user);
        $acc2 = $this->helper->makeAccount($user);
        $opColl1 = $this->helper->makeOperationCollection(3, $acc1);
        $opColl2 = $this->helper->makeOperationCollection(2, $acc1);

        $expectedOpCount = $opColl1->count() + $opColl2->count() + 2; // 2 - балансовые операции счетов

        $result = Doctrine::getTable('Operation')
            ->createQuery()
            ->joinAccount('accAlias')
            ->execute();

        $this->assertType('Doctrine_Collection', $result);
        $this->assertEquals($expectedOpCount, $result->count());
        $this->assertType('Operation', $result->get(0));
        $this->assertEquals($acc1, $result->get(0)->getAccount());
    }


    /**
     * Получить запрос баланса счетов, проверить все ли ок
     */
    public function testGetBalanceQuery()
    {
        $user = $this->helper->makeUser();
        // счета: 1, 2, 5, 15, 16 или 8 с деньгами больше 0
        $accTypes = array(1, 2, 5, 15, 16);
        $i = 0;
        foreach ($accTypes as $typeId) {
            ++$i;
            ${'acc' . $i} = $this->helper->makeAccount($user, array('type_id' => $typeId,));
        }
        // участвует: кредитная карта с положительной суммой
        $acc6 = $this->helper->makeAccount($user, array('type_id' => 8,));
        // не участвует в выборке: кредит
        $acc0 = $this->helper->makeAccount($user, array('type_id' => 9,));

        // для начала проверим без операций
        $result1 = Doctrine::getTable('Operation')
            ->createQuery()
            ->getBalanceQuery($user)
            ->execute();

        // должны выбрать балансы счетов с типами счета 1, 2, 5, 15, 16
        $this->assertEquals(5, $result1->count());
        $this->assertType('Operation', $result1->getFirst());
        $this->assertType('Account', $result1->getFirst()->getAccount());
        // баланс счета 0, т.к. операций нет и балансовая операция = 0
        $this->assertEquals($acc1->getId(), $result1->getFirst()->getAccountId());
        $this->assertEquals(0, $result1['0']['money'], '', 0.01);

        // заполним операциями
        $op1 = $this->helper->makeOperation($acc1, array('amount' => 101.50, 'type' => Operation::TYPE_PROFIT,));
        $op2 = $this->helper->makeOperation($acc2, array('amount' => -102.50, 'type' => Operation::TYPE_EXPENSE,));
        $op3 = $this->helper->makeOperation($acc6, array('amount' => 106.50, 'type' => Operation::TYPE_PROFIT,));
        $op4 = $this->helper->makeOperation($acc6, array('amount' => -10.20, 'type' => Operation::TYPE_EXPENSE,));
        $op5 = $this->helper->makeOperation($acc3, array(
            'amount' => -103.50,
            'transfer_amount' => 123.50,
            'transfer_account_id' => $acc4->getId(),
            'type' => Operation::TYPE_TRANSFER,
        ));

        $result2 = Doctrine::getTable('Operation')
            ->createQuery()
            ->getBalanceQuery($user)
            ->execute();

        $this->assertNotEquals($result1->count(), $result2->count());
        $this->assertEquals(6, $result2->count());
        $this->assertEquals($acc1->getAccountId(), $result2->getFirst()->getAccountId(), '');

        $this->assertEquals($op1->getAmount(), $result2['0']['money'], '', 0.01);
        $this->assertEquals($op2->getAmount(), $result2['1']['money'], '', 0.01);
        $this->assertEquals($op5->getAmount(), $result2['2']['money'], '', 0.01);
        $this->assertEquals($op5->getTransferAmount(), $result2['3']['money'], '', 0.01);
        $this->assertEquals(0.00, $result2['4']['money'], '', 0.01);
        $this->assertEquals($op3->getAmount() + $op4->getAmount(), $result2['5']['money'], '', 0.01);
    }


    /**
     * Запрос расходов
     */
    public function testGetExpenceQuery()
    {
        $user = $this->helper->makeUser();
        $acc = $this->helper->makeAccount($user);

        $op1 = $this->helper->makeOperation($acc, array(
            'type' => Operation::TYPE_PROFIT,
            'amount' => 200.00,
        ));
        $op2 = $this->helper->makeOperation($acc, array(
            'type' => Operation::TYPE_EXPENSE,
            'amount' => -300.00,
        ));
        $op3 = $this->helper->makeOperation($acc, array(
            'type' => Operation::TYPE_TRANSFER,
            'amount' => -400.00,
            'transfer_amount' => 500.00,
            'transfer_account_id' => $this->helper->makeAccount($user)->getId(),
        ));

        $result = Doctrine::getTable('Operation')
            ->createQuery()
            ->getExpenceQuery($user)
            ->execute();

        $this->assertEquals(1, $result->count());
        $this->assertEquals($op2->getAmount(), $result[0]['money'], '', 0.01);
    }


    /**
     * Запрос доходов
     */
    public function testGetProfitQuery()
    {
        $user = $this->helper->makeUser();
        $acc = $this->helper->makeAccount($user);

        $op1 = $this->helper->makeOperation($acc, array(
            'type' => Operation::TYPE_PROFIT,
            'amount' => 200.00,
        ));
        $op2 = $this->helper->makeOperation($acc, array(
            'type' => Operation::TYPE_EXPENSE,
            'amount' => -300.00,
        ));
        $op3 = $this->helper->makeOperation($acc, array(
            'type' => Operation::TYPE_TRANSFER,
            'amount' => -400.00,
            'transfer_amount' => 500.00,
            'transfer_account_id' => $this->helper->makeAccount($user)->getId(),
        ));
        $op4 = $this->helper->makeOperation($acc, array(
            'type' => Operation::TYPE_PROFIT,
            'amount' => 100.30,
            'date' => date('Y-m-d', time()-(60*60*24*130)), // 130 дней, больше 3х месяцев
        ));
        $op5 = $this->helper->makeOperation($acc, array(
            'type' => Operation::TYPE_PROFIT,
            'amount' => 100.60,
            'date' => date('Y-m-d', time()-(60*60*24*75)), // 75 дней, меньше 3х месяцев
        ));

        $result = Doctrine::getTable('Operation')
            ->createQuery()
            ->getProfitQuery($user, $months = 3)
            ->execute();

        $this->assertEquals(1, $result->count());
        $this->assertEquals($op1->getAmount() + $op5->getAmount(), $result[0]['money'], '', 0.01);
    }


    /**
     * Запрос переводов на долговые счета
     */
    public function testGetRepayLoanQuery()
    {
        $user = $this->helper->makeUser();
        // шум
        $this->helper->makeOperationCollection(3, $this->helper->makeAccount($user));

        $accFrom = $this->helper->makeAccount($user);
        // счета куда переводим
        $accTypes = array(7, 8, 9);
        $i = 0;
        foreach ($accTypes as $typeId) {
            ++$i;
            ${'accTo' . $i} = $this->helper->makeAccount($user, array('type_id' => $typeId,));
        }

        $op1 = $this->helper->makeOperation($accFrom, array(
            'type' => Operation::TYPE_TRANSFER,
            'amount' => 200.34,
            'transfer_amount' => 210.34,
            'transfer_account_id' => $accTo1->getId(),
        ));
        $op2 = $this->helper->makeOperation($accFrom, array(
            'type' => Operation::TYPE_TRANSFER,
            'amount' => 100.34,
            'transfer_amount' => 110.34,
            'transfer_account_id' => $accTo2->getId(),
        ));
        $op3 = $this->helper->makeOperation($accFrom, array(
            'type' => Operation::TYPE_TRANSFER,
            'amount' => 300.34,
            'transfer_amount' => 310.34,
            'transfer_account_id' => $accTo3->getId(),
        ));
        /**
        Чисто для потомков: идейно такая запись должна привести к 0,
        а фактически будет ошибка, т.к. суммируется amount
        $op4 = $this->helper->makeOperation($accTo3, array(
            'type' => Operation::TYPE_TRANSFER,
            'amount' => -333.34,
            'transfer_amount' => 333.34,
            'transfer_account_id' => $accTo3->getId(),
        ));
        */

        $result = Doctrine::getTable('Operation')
            ->createQuery()
            ->getRepayLoanQuery($user, 0)
            ->execute();

        $this->assertEquals(3, $result->count());
        $this->assertEquals($op1->getTransferAmount(), $result[0]['money'], '', 0.01);
        $this->assertEquals($op2->getTransferAmount(), $result[1]['money'], '', 0.01);
        $this->assertEquals($op3->getTransferAmount(), $result[2]['money'], '', 0.01);
    }


    /**
     * Проценты по кредитам и займам
     */
    public function testGetInterestOnLoanQuery()
    {
        $user = $this->helper->makeUser();
        $acc = $this->helper->makeAccount($user);
        $category = $this->helper->makeCategory($user, array('system_id' => 15));
        $op = $this->helper->makeOperation($acc, array('category_id' => $category->getId()));

        $result = Doctrine::getTable('Operation')
            ->createQuery()
            ->getInterestOnLoanQuery($user, 1)
            ->execute();

        $this->assertEquals(1, $result->count());
        $this->assertEquals($op->getAmount(), $result['0']['money']);
    }

}
