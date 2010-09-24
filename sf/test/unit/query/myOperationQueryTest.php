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

}
