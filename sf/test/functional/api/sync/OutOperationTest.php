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
        $transfer = $this->helper->makeAccount();

        $op = $this->helper->makeOperation(null, array(
            'type' => Operation::TYPE_TRANSFER,
            'transfer_account_id' => $transfer->getId(),
            'amount' => -567.12,
            'transfer_amount' => '',
            ));

        $this->authenticateUser($op->getUser());

        $opRecord = "record[id=\"{$op->getId()}\"]";

        // проверим, что в БД попала пустая сумма
        $result = $this->getConnection()->getDbh()
            ->query("SELECT o.transfer_amount FROM operation o WHERE o.id = '{$op->getId()}'")
            ->fetch(PDO::FETCH_ASSOC);
        $this->assertEmpty($result['transfer_amount']);

        $op->refresh(false);
        // при гидрации уже не пустая
        $this->assertNotEmpty($op->getTransferAmount());

        $this->browser
            ->getAndCheck('sync', 'syncOut', $this->generateUrl('sync_get_modified', array(
                'model'   => 'operation',
                'from'    => $this->_makeDate(-100),
                'to'      => $this->_makeDate(+100),
            )), 200)
            ->with('response')->begin()
                ->isValid()
                ->checkElement("$opRecord transfer_amount", (string) $op->getTransferAmount())
            ->end();
    }

}
