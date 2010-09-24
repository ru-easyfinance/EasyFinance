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
        $rAcc = $result1->getFirst();
        $this->assertEquals(0, $rAcc['money'], '', 0.01);

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

        $rAcc1 = $result2->get(0);
        $rAcc2 = $result2->get(1);
        $rAcc3 = $result2->get(2);
        $rAcc4 = $result2->get(3);
        $rAcc5 = $result2->get(4);
        $rAcc6 = $result2->get(5);

        $this->assertEquals(101.50, $rAcc1['money'], '', 0.01);
        $this->assertEquals(-102.50, $rAcc2['money'], '', 0.01);
        $this->assertEquals(-103.50, $rAcc3['money'], '', 0.01);
        $this->assertEquals(103.50, $rAcc4['money'], '', 0.01);
        $this->assertEquals(0.00, $rAcc5['money'], '', 0.01);
        $this->assertEquals(106.50 - 10.20, $rAcc6['money'], '', 0.01);
    }

}
