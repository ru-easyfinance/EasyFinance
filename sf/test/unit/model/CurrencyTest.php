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

}
