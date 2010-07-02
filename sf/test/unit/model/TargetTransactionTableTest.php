<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Таблица переводов на фин.цели
 */
class model_TargetTransactionTableTest extends myUnitTestCase
{
    /**
     * Зарезервированные средства по счетам
     */
    public function testQueryCountReserves()
    {
        $user = $this->helper->makeUser();
        $acc1 = $this->helper->makeAccount($user);
        $acc2 = $this->helper->makeAccount($user);
        $acc3 = $this->helper->makeAccount();

        $t1 = $this->helper->makeTarget($acc1);
        $t2 = $this->helper->makeTarget($acc1);
        $t3 = $this->helper->makeTarget($acc1);
        $t4 = $this->helper->makeTarget($acc2);

        $tr1 = $this->helper->makeTargetTransaction($t1);
        $tr2 = $this->helper->makeTargetTransaction($t1);
        $tr3 = $this->helper->makeTargetTransaction($t2);
        $tr4 = $this->helper->makeTargetTransaction($t4);
        $tr5 = $this->helper->makeTargetTransaction();

        $reserve1 = $tr1->getAmount() + $tr2->getAmount() + $tr3->getAmount();
        $reserve2 = $tr4->getAmount();

        $result = Doctrine::getTable('TargetTransaction')
            ->queryCountReserves(array($acc1->getId(), $acc2->getId(), $acc3->getId()), $user)
            ->execute();

        $this->assertEquals(2, $result->count());

        $this->assertModels($tr1, $result->getFirst());
        $this->assertModels($tr4, $result->getLast());
        $this->assertEquals($reserve1, $result->getFirst()->getReserve(), "", 0.01);
        $this->assertEquals($reserve2, $result->getLast()->getReserve(),  "", 0.01);
    }

}
