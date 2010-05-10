<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Счета
 */
class model_AccountTest extends myUnitTestCase
{
    /**
     * Отношения
     */
    public function testRelations()
    {
        $acc = new Account;

        // Пользователь
        $this->assertType('User', $acc->User);

        // Свойства счета
        $this->assertType('Doctrine_Collection', $props = $acc->Properties);
        $this->assertType('AccountPropertyTable', $props->getTable());
    }


    /**
     * Создание объекта, алиасы
     */
    public function testMakeRecord()
    {
        $data = array(
            'user_id'     => 1,
            'name'        => 'Название счета',
            'type_id'     => 2,
            'currency_id' => 3,
            'description' => 'Описание счета',
        );
        $acc = new Account;
        $acc->fromArray($data, false);
        $expectedData = $acc->toArray(false);
        unset($expectedData['id']);
        $this->assertEquals($data, $expectedData, "Alias column mapping");

        $acc->save();
        $this->assertTrue((bool)$acc->getId());

        $this->assertEquals(1, $this->queryFind('Account', $data)->count());
    }


    /**
     * Создать счет с набором свойств
     */
    public function testMakeWithProperties()
    {
        $data = array(
            'name'    => 'Название счета',
            'type_id' => 5,
            'Properties' => array(
                $prop1 = array(
                    'field_id'    => 10,
                    'field_value' => 'Значение 1',
                ),
                $prop2 = array(
                    'field_id'    => 20,
                    'field_value' => 'Значение 2',
                ),
            )
        );

        $acc = new Account;
        $acc->fromArray($data, $deep = true);
        $acc->save();

        $prop1['account_id'] = $prop2['account_id'] = $acc->getId();
        $this->assertEquals(1, $this->queryFind('AccountProperty', $prop1)->count(), 'Prop 1');
        $this->assertEquals(1, $this->queryFind('AccountProperty', $prop2)->count(), 'Prop 1');
    }
}
