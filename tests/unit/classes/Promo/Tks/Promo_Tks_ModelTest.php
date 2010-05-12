<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

/**
 * Test class for Promo_Tks_Model.
 * Generated by PHPUnit on 2010-05-12 at 12:25:51.
 */
class Promo_Tks_ModelTest extends UnitTestCase
{

    /**
     * @var Promo_Tks_Model
     */
    protected $_model;


    public function testMakeObjectWithValidData()
    {
        $validData = array(
            'surname'=> 'Пупкин',
            'name' => 'Васисуалий',
            'patronymic' => 'Петрович',
            'phone'=> '+79262562132',
            'user_id'=> null,
            );

        $ob = new Promo_Tks_Model($validData);
        $this->assertEquals($validData, $ob->toArray());
    }

    public function testMakeObjectWithInvalidData()
    {
        $expected = array(
            'surname'=> '',
            'name' => '',
            'patronymic' => '',
            'phone'=> '',
            'user_id'=> null,
            );

        $ob = new Promo_Tks_Model(null);
        $this->assertEquals($expected, $ob->toArray());

        $ob = new Promo_Tks_Model('some value');
        $this->assertEquals($expected, $ob->toArray());
    }


    public function testMakeObjectWithExtraData()
    {
        $expected = array(
            'surname'=> 'Пупкин',
            'name' => 'Васисуалий',
            'patronymic' => 'Петрович',
            'phone'=> '',
            'user_id'=> null,
            );
        $input = $expected;
        unset($input['phone']);
        $input['some key'] = 'some value';

        $ob = new Promo_Tks_Model($input);
        $this->assertEquals($expected, $ob->toArray());
    }



    /**
     * @todo Implement testSave().
     */
    public function testSave()
    {
        $validData = array(
            'surname'=> 'Пупкин',
            'name' => 'Васисуалий',
            'patronymic' => 'Петрович',
            'phone'=> '+79262562132',
            'user_id'=> null,
            );

        $ob = new Promo_Tks_Model($validData);
        $ob->save();

        $sql = "SELECT count(*) FROM anketa_tks WHERE
                `surname` = ? AND
                `name` = ? AND
                `patronymic` = ? AND
                `phone` = ?";

        $count = $this->getConnection()->selectCell($sql,
            $validData['surname'],
            $validData['name'],
            $validData['patronymic'],
            $validData['phone']);
        $this->assertEquals(1, $count, "Expected found object");
    }


    public function testSaveEmptyObject()
    {
        $ob = new Promo_Tks_Model(null);
        $ob->save();

        $sql = "SELECT count(*) FROM anketa_tks WHERE
                `surname` = '' AND
                `name` = '' AND
                `patronymic` = '' AND
                `phone` = ''";

        $count = $this->getConnection()->selectCell($sql);
        $this->assertEquals(1, $count, "Expected found object");
    }
}
