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
        $this->helper->makeOperation($acc1, array('amount' => 101.50, 'type' => Operation::TYPE_PROFIT,));
        $this->helper->makeOperation($acc2, array('amount' => -102.50, 'type' => Operation::TYPE_EXPENSE,));
        $this->helper->makeOperation($acc6, array('amount' => 106.50, 'type' => Operation::TYPE_PROFIT,));
        $this->helper->makeOperation($acc6, array('amount' => -10.20, 'type' => Operation::TYPE_EXPENSE,));
        $this->helper->makeOperation($acc3, array(
            'amount' => -103.50,
            'transfer_amount' => 103.50,
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

        $this->assertEquals(101.50, $result2['0']['money'], '', 0.01);
        $this->assertEquals(-102.50, $result2['1']['money'], '', 0.01);
        $this->assertEquals(-103.50, $result2['2']['money'], '', 0.01);
        $this->assertEquals(103.50, $result2['3']['money'], '', 0.01);
        $this->assertEquals(0.00, $result2['4']['money'], '', 0.01);
        $this->assertEquals(106.50 - 10.20, $result2['5']['money'], '', 0.01);
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

}
