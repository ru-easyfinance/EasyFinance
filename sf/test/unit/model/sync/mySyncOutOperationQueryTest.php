<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * Запрос для выборки операций для синхронизации
 */
class model_sync_mySyncOutOperationQueryTest extends myUnitTestCase
{
    protected $app = 'api';


    /**
     * Не отдаем неподтвержденные операции из календаря
     */
    public function testFindActiveForSync()
    {
        $account = $this->helper->makeAccount();
        $opOk1 = $this->helper->makeOperation($account);
        $opOk2 = $this->helper->makeOperation($account, array(
            'accepted' => 1,
            'chain_id' => 123,
            ));
        $opNo = $this->helper->makeOperation($account, array(
            'accepted' => 0,
            'chain_id' => 123,
            ));

        $q = new mySyncOutOperationQuery(new myDatetimeRange(new DateTime('-1year'), new DateTime), $account->getUserId());
        $found = $q->getQuery()->execute();

        $this->assertEquals(3, $found->count(), 'Count');
        $this->assertEquals($opOk1->getId(), $found->get(1)->getId(), 'Found first operation');
        $this->assertEquals($opOk2->getId(), $found->get(2)->getId(), 'Found second operation');
    }

}
