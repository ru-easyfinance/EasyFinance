<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * Синхронизация: отдать список объектов
 */
class api_sync_OutTest extends myFunctionalTestCase
{
    protected $app = 'api';


    /**
     * 404 если указана неизвестная модель
     */
    public function test404IfModelNotSupported()
    {
        $this->browser
            ->getAndCheck('sync', 'syncOut', $this->generateUrl('sync_get_modified', array(
                'model' => 'unknown_model',
            )), 404);
    }


    /**
     * Отдать валюты
     * см. фикстуры, там куча валют
     */
    public function testGetCurrency()
    {
        $user = $this->helper->makeUser();

        $this->browser
            ->getAndCheck('sync', 'syncOut', $this->generateUrl('sync_get_modified', array(
                'model'   => 'currency',
                'user_id' => $user->getId())), 200)
            ->with('response')->begin()
                ->checkContains('<recordset type="Currency">')
                ->checkElement('record')
                ->checkElement('record code')
                ->checkElement('record symbol')
                ->checkElement('record name')
                ->checkElement('record rate')
                ->checkElement('record created_at')
                ->checkElement('record updated_at')
            ->end();
    }


    /**
     * Отдать список счетов
     */
    public function testGetAccounts()
    {
        $account  = $this->helper->makeAccount();
        $accountA = $this->helper->makeAccount();

        $this->browser
            ->getAndCheck('sync', 'syncOut', $this->generateUrl('sync_get_modified', array(
                'model' => 'account',
                'user_id'=>$account->getUserId())), 200)
            ->with('response')->begin()
                ->checkContains('<recordset type="Account">')
                ->checkElement('record', 1)
                ->checkElement('#'.$account->getId())
                ->checkElement('record name')
                ->checkElement('record description')
                ->checkElement('record currency_id')
                ->checkElement('record type_id')
            ->end();
    }


    /**
     * Отдать список операций
     */
    public function testGetOps()
    {
        $op1 = $this->helper->makeOperation();
        $op2 = $this->helper->makeOperation($op1->getAccount());

        $this->browser
            ->getAndCheck('sync', 'syncOut', $this->generateUrl('sync_get_modified', array(
                'model' => 'operation',
                'user_id'=>$op1->getUserId())), 200)
            ->with('response')->begin()
                ->checkContains('<recordset type="Operation">')
                ->checkElement('record', 2)
                ->checkElement('#'.$op1->getId())
                ->checkElement('#'.$op2->getId())
                ->checkElement('record account_id')
                ->checkElement('record category_id')
                ->checkElement('record amount')
                ->checkElement('record comment')
                ->checkElement('record dt_update')
                ->checkElement('record dt_create')
            ->end();
    }

}
