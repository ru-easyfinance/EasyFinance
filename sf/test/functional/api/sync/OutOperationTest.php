<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * Синхронизация: отдать операции
 */
class api_sync_OutOperationTest extends myFunctionalTestCase
{
    protected $app = 'api';


    /**
     * Отдать список операций
     */
    public function testGetOperations()
    {
        $op = $this->helper->makeOperation(null, array('type' => Operation::TYPE_PROFIT));
        $opDeleted = $this->helper->makeOperation($op->getAccount(), array(
            'type'       => Operation::TYPE_PROFIT,
            'deleted_at' => date(DATE_ISO8601),
        ));

        $this->authenticateUser($op->getUser());

        $this->browser
            ->getAndCheck('sync', 'syncOut', $this->generateUrl('sync_get_modified', array(
                'model'   => 'operation',
                'from'    => $this->_makeDate(-100),
                'to'      => $this->_makeDate(+100),
            )), 200)
            ->with('response')->begin()
                ->isValid()
                ->checkContains('<recordset type="Operation">')
                ->checkElement('record', 2)
                ->checkElement("record[id=\"{$opDeleted->getId()}\"][deleted]", 1)
                ->checkElement('#'.$op->getId())
                ->checkElement('record account_id',  (string)$op->getAccountId())
                ->checkElement('record category_id', (string)$op->getCategoryId())
                ->checkElement('record amount',      (string)$op->getAmount())
                ->checkElement('record type',        (string)$op->getType())
                ->checkElement('record date',        $op->getDateTimeObject('date')->format(DATE_ISO8601))
                ->checkElement('record comment',     $op->getComment())
                ->checkElement('record accepted',    $op->getAccepted())
                ->checkElement('record created_at')
                ->checkElement('record updated_at')
                // TODO: Если это не перевод, тогда не показываем поля
                // ->checkElement('record transfer_account_id', false)
                // ->checkElement('record transfer_amount', false)
            ->end();
    }


    /**
     * Операция перевода
     */
    public function testTransferOperation()
    {
        $op = $this->helper->makeOperation(null, array(
            'type' => Operation::TYPE_TRANSFER,
            'transfer_account_id' => 123,
            'transfer_amount' => -567.12,
            ));

        $this->authenticateUser($op->getUser());

        $this->browser
            ->getAndCheck('sync', 'syncOut', $this->generateUrl('sync_get_modified', array(
                'model'   => 'operation',
                'from'    => $this->_makeDate(-100),
                'to'      => $this->_makeDate(+100),
            )), 200)
            ->with('response')->begin()
                ->isValid()
                ->checkContains('<recordset type="Operation">')
                ->checkElement('record', 1)
                ->checkElement('record account_id',  (string)$op->getAccountId())
                ->checkElement('record amount',      (string)$op->getAmount())
                ->checkElement('record transfer_account_id', (string)$op->getTransferAccountId())
                ->checkElement('record transfer_amount', (string)$op->getTransferAmount())
            ->end();
    }

}
