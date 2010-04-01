<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Операция из внешнего источника
 */
class model_SourceOperationTest extends myUnitTestCase
{
    /**
     * Отношения
     */
    public function testRelations()
    {
        $op = new SourceOperation;

        // Операция
        $this->assertType('Operation', $op->Operation);
    }


    /**
     * При удалении удалении операции, удаляется ссылка на источник
     */
    public function testOnDeleteOperationCascade()
    {
        $op = $this->helper->makeOperation();
        $source = new SourceOperation;
        $source->setOperationId($op->getId());
        $source->save();
        $this->assertEquals(1, $source->getTable()->createQuery()->count());

        $this->assertModels($op, $source->Operation);

        $op->delete();
        $this->assertEquals(0, $source->getTable()->createQuery()->count());
    }

}
