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
     * 400 если не указаны даты
     */
    public function test400IfDateRangeNotDefined()
    {
        $this->browser
            ->getAndCheck('sync', 'syncOut', $this->generateUrl('sync_get_modified', array(
                'model' => 'currency',
            )), 400)
            ->with('form')->begin()
                ->hasErrors(true)
                ->isInstanceOf('mySyncOutForm')
            ->end()
            ->with('response')->begin()
                ->checkElement('response error message', '/Required/')
            ->end();
    }


    /**
     * Показать удаленный объект
     */
    public function testGetDeletedObject()
    {
        $account = $this->helper->makeAccount();
        $account->delete();

        $this->browser
            ->getAndCheck('sync', 'syncOut', $this->generateUrl('sync_get_modified', array(
                'model'   => 'account',
                'from'    => $this->_makeDate(-10),
                'to'      => $this->_makeDate(10),
                'user_id' => $account->getUserId())), 200)
            ->with('response')->begin()
                ->checkContains('<recordset type="Account">')
                ->checkElement('record', 1)
                ->checkElement('#'.$account->getId())
                ->checkElement('record created_at')
                ->checkElement('record updated_at')
                ->checkElement('record[deleted]')
            ->end();
    }


    /**
     * Отдать список операций
     */
    public function testGetOps()
    {
        $this->markTestIncomplete();
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
