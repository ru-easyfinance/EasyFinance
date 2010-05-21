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
            'created_at'  => null,
            'updated_at'  => null,
        );
        $c = new Currency;
        $c->fromArray($data, false);

        $actualData = $c->toArray(false);
        unset($actualData['id']);
        $this->assertEquals($data, $actualData, "Alias column mapping");


        // Save
        $c->save();
        $this->assertTrue((bool)$c->getId());

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->assertEquals(1, $this->queryFind('Currency', $data)->count());
    }

}
