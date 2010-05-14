<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

/**
 * Анкета ТКС
 */
class Promo_Tks_ModelTest extends UnitTestCase
{

    /**
     * @var Promo_Tks_Model
     */
    protected $_model;

    /**
     * Проверяем создание объекта с валидными данными
     */
    public function testMakeObjectWithValidData()
    {
        $validData = array(
            'surname'=> 'Пупкин',
            'name' => 'Васисуалий',
            'patronymic' => 'Петрович',
            'phone'=> '+79262562132',
            'user_id'=> rand(1,999999),
            );

        $ob = new Promo_Tks_Model($validData);
        $this->assertEquals($validData, $ob->toArray());
    }

    /**
     * Проверяем создание объекта с неправильными данными
     */
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

    /**
     * Пробуем сохранить объект с избыточными данными
     */
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
     * Проверяем сохранение
     */
    public function testSave()
    {
        $validData = array(
            'surname'=> 'Пупкин',
            'name' => 'Васисуалий',
            'patronymic' => 'Петрович',
            'phone'=> '+79262562132',
            'user_id'=> 155,
            );

        $ob = new Promo_Tks_Model($validData);
        $ob->save();

        $sql = "SELECT count(*) FROM anketa_tks WHERE
                `surname` = ? AND
                `name` = ? AND
                `patronymic` = ? AND
                `phone` = ? AND
                `created_at` <> ''";

        $count = $this->getConnection()->selectCell($sql,
            $validData['surname'],
            $validData['name'],
            $validData['patronymic'],
            $validData['phone']);
        $this->assertEquals(1, $count, "Expected found object");
    }

    /**
     * Проверяем сохранение пустого объекта
     */
    public function testSaveEmptyObject()
    {
        $ob = new Promo_Tks_Model(null);
        $ob->save();

        $sql = "SELECT count(*) FROM anketa_tks WHERE
                `surname` = '' AND
                `name` = '' AND
                `patronymic` = '' AND
                `phone` = '' AND
                `created_at` <> ''";

        $count = $this->getConnection()->selectCell($sql);
        $this->assertEquals(1, $count, "Expected found object");
    }
}
