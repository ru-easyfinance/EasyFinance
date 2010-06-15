<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * Синхронизация: отдать счета
 */
class api_sync_OutAccountTest extends myFunctionalTestCase
{
    protected $app = 'api';


    /**
     * Отдать список счетов
     */
    public function testGetAccounts()
    {
        $account1 = $this->helper->makeAccount(null, array('updated_at' => $this->_makeDate(1000)));
        $account2 = $this->helper->makeAccount($account1->getUser());
        $accountA = $this->helper->makeAccount();

        $this->browser
            ->getAndCheck('sync', 'syncOut', $this->generateUrl('sync_get_modified', array(
                'model'   => 'account',
                'from'    => $this->_makeDate(500),
                'to'      => $this->_makeDate(1500),
                'user_id' => $account1->getUserId())), 200)
            ->with('response')->begin()
                ->checkContains('<recordset type="Account">')
                ->checkElement('record', 1)
                ->checkElement('#'.$account1->getId())
                ->checkElement('record name')
                ->checkElement('record description')
                ->checkElement('record currency_id')
                ->checkElement('record type_id')
                ->checkElement('record created_at')
                ->checkElement('record updated_at')
            ->end();
    }

}
