<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Форма для обработки запроса на синхронизацию
 */
class form_mySyncOutFormTest extends sfPHPUnitFormTestCase
{
    protected $saveForm = false;


    /**
     * Создать форму
     */
    protected function makeForm()
    {
        return new mySyncOutForm;
    }


    /**
     * Получить массив доступных полей формы
     */
    protected function getFields()
    {
        // Виджетов нет
        return array();
    }


    /**
     * Получить массив валидных данных
     */
    protected function getValidData()
    {
        return array(
            'from' => date(DATE_ISO8601),
            'to'   => date(DATE_ISO8601),
        );
    }


    /**
     * План тестирования ошибок валидации
     */
    protected function getValidationTestingPlan()
    {
        return array(
            // Ничего не отправлено
            'Empty request' => new sfPHPUnitFormValidationItem(
                array(),
                array(
                    'from'  => 'required',
                    'to'    => 'required',
                )),

            // Неверный интервал
            'Invalid range' => new sfPHPUnitFormValidationItem(
                array(
                    'from'  => date(DATE_ISO8601),
                    'to'    => date(DATE_ISO8601, time()-1),
                ),
                array(
                    ''  => 'Invalid date/time range',
                )),

            // Неверный формат дат
            'Invalid datetime format' => new sfPHPUnitFormValidationItem(
                array(
                    'from'  => date('Y-m-d H:i:s'),
                    'to'    => date('Y-m-d H:i:s', time()+1),
                ),
                array(
                    'from'  => 'invalid',
                    'to'    => 'invalid',
                )),
        );
    }


    /**
     * Получить из формы интервал дат
     */
    public function testGetDateRange()
    {
        $input = $this->getValidData();

        $this->form->bind($input, array());
        $range = $this->form->getDatetimeRange();
        $this->assertEquals($range, new myDatetimeRange(new DateTime($input['from']), new DateTime($input['to'])));
    }

}
