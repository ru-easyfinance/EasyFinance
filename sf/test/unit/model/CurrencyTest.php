<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Валюта
 */
class model_CurrencyTest extends myUnitTestCase
{
    /**
     * Создание объекта, алиасы
     */
    public function testMakeRecord()
    {
        $data = array(
            'code'        => 'AAA',
            'symbol'      => 'a.',
            'name'        => 'Название валюты',
            'rate'        => 0.654321,
            'is_active'   => 2,
        );
        $this->checkModelDeclaration('Currency', $data, $isTimestampable = true);
    }


    /**
     * Невозможно удалить валюту, если она установлена как валюта по-умолчанию у пользователей
     */
    public function testFailedDeleteCurrencyIdConnectedWithUsers()
    {
        $user = $this->helper->makeUser(array('currency_id' => 1));

        $this->setExpectedException('Doctrine_Connection_Mysql_Exception', 'foreign key constraint fails');
        Doctrine::getTable('Currency')->find(1)->delete();
    }
}
