<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';

/**
 * Список операций
 */
class model_OperationCollectionTest extends myUnitTestCase
{
    /**
     * @var myTestObjectHelper
     */
    protected $helper;

    /**
     * Выборка операций за период
     */
    public function testFillForPeriod()
    {
        $dateStart = new DateTime(date('Y-m-01'));
        $dateEnd   = date_add(clone $dateStart, new DateInterval('P1M'));

        $op = $this->helper->makeOperation();

        $operationCollection = new OperationCollection($op->getUser());
        $operationCollection->fillForPeriod($dateStart, $dateEnd);
        $this->assertNotEquals(0, count($operationCollection->getOperations()));
    }
}
