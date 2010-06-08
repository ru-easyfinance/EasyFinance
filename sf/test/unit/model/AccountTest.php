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

        // Валюта
        $this->assertType('Currency', $acc->Currency);

        // Тип счета
        $this->assertType('AccountType', $acc->AccountType);

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
            'user_id'     => $this->helper->makeUser()->getId(),
            'name'        => 'Название счета',
            'type_id'     => 2,
            'currency_id' => 1,
            'description' => 'Описание счета',
        );
        $acc = new Account;
        $acc->fromArray($data, false);
        $expectedData = array_intersect_key($acc->toArray(false), $data);
        $this->assertEquals($data, $expectedData, "Alias column mapping");

        $acc->save();
        $this->assertTrue((bool)$acc->getId());
        // Time
        $date = date('Y-m-d H:i:s');
        $this->assertEquals($date, $acc->getCreatedAt(), 'CreatedAt');
        $this->assertEquals($date, $acc->getUpdatedAt(), 'UpdatedAt');

        $this->assertEquals(1, $this->queryFind('Account', $data)->count());
    }


    /**
     * Создать счет с набором свойств
     */
    public function testMakeWithProperties()
    {
        $data = array(
            'user_id'     => $this->helper->makeUser()->getId(),
            'name'    => 'Название счета',
            'type_id' => 5,
            'currency_id' => 1,
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


    /**
     * Невозможно удалить пользователя, если у него есть счета
     */
    public function testFailedDeleteUserIdConnectedWithAccount()
    {
        $account = $this->helper->makeAccount();

        $this->setExpectedException('Doctrine_Connection_Mysql_Exception', 'foreign key constraint fails');
        $account->getUser()->delete();
    }


    /**
     * Невозможно удалить валюту, если есть связи со счетами
     */
    public function testFailedDeleteCurrencyIdConnectedWithAccount()
    {
        $account = $this->helper->makeAccount(null, array('currency_id' => 1));

        $this->setExpectedException('Doctrine_Connection_Mysql_Exception', 'foreign key constraint fails');
        $account->getCurrency()->delete();
    }


    /**
     * Невозможно удалить тип счёта, если есть связь со счетами
     */
    public function testFailedDeleteAccount_TypesIdConnectedWithAccount()
    {
        $account = $this->helper->makeAccount(null, array('type_id' => Account::TYPE_CASH));

        $this->setExpectedException('Doctrine_Connection_Mysql_Exception', 'foreign key constraint fails');
        $account->getAccountType()->delete();
    }


    /**
     * SoftDelete
     */
    public function testSoftDelete()
    {
        $account = $this->helper->makeAccount();
        $account->delete();

        $this->assertEquals($account->getUpdatedAt(), $account->getDeletedAt());
    }

}
