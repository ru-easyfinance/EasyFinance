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
        $opInitBalance = $this->helper->makeOperation($op->getAccount(), array(
            'amount'     => 100,
            'type'       => Operation::TYPE_BALANCE,
        ));

        $this->authenticateUser($op->getUser());

        $opRecord = "record[id=\"{$op->getId()}\"]";

        $this->browser
            ->getAndCheck('sync', 'syncOut', $this->generateUrl('sync_get_modified', array(
                'model'   => 'operation',
                'from'    => $this->_makeDate(-100),
                'to'      => $this->_makeDate(+100),
            )), 200)
            ->with('response')->begin()
                ->isValid()
                ->checkContains('<recordset type="Operation">')
                ->checkElement('record', 3)
                ->checkElement("record[id=\"{$opDeleted->getId()}\"][deleted]", 1)
                ->checkElement('#'.$op->getId())
                ->checkElement("$opRecord account_id",  (string)$op->getAccountId())
                ->checkElement("$opRecord category_id", (string)$op->getCategoryId())
                ->checkElement("$opRecord amount",      (string)$op->getAmount())
                ->checkElement("$opRecord type",        (string)$op->getType())
                ->checkElement("$opRecord date",        $op->getDateTimeObject('date')->format(DATE_ISO8601))
                ->checkElement("$opRecord comment",     $op->getComment())
                ->checkElement("$opRecord accepted",    $op->getAccepted())
                ->checkElement("$opRecord created_at")
                ->checkElement("$opRecord updated_at")
                ->checkElement('#'.$opInitBalance->getId())
                ->checkElement("record[id=\"{$opInitBalance->getId()}\"] amount", '100')
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
        $transfer = $this->helper->makeAccount();

        $op = $this->helper->makeOperation(null, array(
            'type' => Operation::TYPE_TRANSFER,
            'transfer_account_id' => $transfer->getId(),
            'transfer_amount' => -567.12,
            ));

        $this->authenticateUser($op->getUser());

        $opRecord = "record[id=\"{$op->getId()}\"]";

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
                ->checkElement("$opRecord account_id",  (string)$op->getAccountId())
                ->checkElement("$opRecord amount",      (string)$op->getAmount())
                ->checkElement("$opRecord transfer_account_id", (string)$op->getTransferAccountId())
                ->checkElement("$opRecord transfer_amount", (string)$op->getTransferAmount())
            ->end();
    }


    /**
     * Операция перевода где не указана переведенная сумма
     */
    public function testTransferOperationWithoutAmount()
    {
        $accFrom = $this->helper->makeAccount();
        $transfer = $this->helper->makeAccount($accFrom->getUser());

        $op1 = $this->helper->makeOperation($accFrom, array(
            'type'                => Operation::TYPE_TRANSFER,
            'transfer_account_id' => $transfer->getId(),
            'amount'              => -567.12,
            'transfer_amount'     => '',
            ));
        $op2 = $this->helper->makeOperation($accFrom, array(
            'type'                => Operation::TYPE_TRANSFER,
            'transfer_account_id' => $transfer->getId(),
            'amount'              => -563.12,
            'transfer_amount'     => '',
            'deleted_at'          => date('Y-m-d H:i:s'),
            ));

        $this->authenticateUser($accFrom->getUser());

        $op1Record = "record[id=\"{$op1->getId()}\"]";
        $op2Record = "record[id=\"{$op2->getId()}\"]";

        // проверим, что в БД попала уже не пустая сумма
        // при гидрации тоже не пустая
        $op1->refresh(false);
        $op2->refresh(false);
        $this->assertNotEmpty($op1->getTransferAmount());
        $this->assertNotEmpty($op2->getTransferAmount());

        $this->browser
            ->getAndCheck('sync', 'syncOut', $this->generateUrl('sync_get_modified', array(
                'model'   => 'operation',
                'from'    => $this->_makeDate(-500),
                'to'      => $this->_makeDate(+500),
            )), 200)
            ->with('response')->begin()
                ->isValid()
                ->checkElement("$op1Record transfer_amount", (string) $op1->getTransferAmount())
                ->checkElement("$op2Record transfer_amount", (string) $op2->getTransferAmount())
            ->end();
    }

}
