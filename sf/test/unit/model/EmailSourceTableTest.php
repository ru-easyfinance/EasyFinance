<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Таблица счетов
 */
class model_EmailSourceTableTest extends myUnitTestCase
{
    /**
     * Проверка метода testGetByEmail
     *
     */
    public function testGetByEmail()
    {
        // Создаем отправителя
        $source = new EmailSource();
        $source->setName("new source");
        $source->setEmailList("anytest@test.tst, othertest@test.ru");
        $source->save();

        // Ok
        $getSource = EmailSourceTable::getInstance()->getByEmail("anytest@test.tst");
        $this->assertModels( $source, $getSource );

        // Ok
        $getSource = EmailSourceTable::getInstance()->getByEmail("othertest@test.ru");
        $this->assertModels( $source, $getSource );

        // Fail
        $getSource = EmailSourceTable::getInstance()->getByEmail("anybodyelse");
        $this->assertEquals( false, $getSource );
    }
}