<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';
require_once sfConfig::get('sf_root_dir') . '/apps/frontend/lib/form/CitiBankApplicationForm.class.php';


/**
 * Форма: анкета сити банка
 */
class form_frontend_CitiBankApplicationFormTest extends myFormTestCase
{
    protected $app = 'frontend';

    /**
     * Отключим автоматизированное тестирование сохранения формы.
     * Сделаем это ручками здесь.
     */
    protected $saveForm = false;


    /**
     * Создать форму
     */
    protected function makeForm(User $user = null)
    {
        return new CitiBankApplicationForm();
    }


    /**
     * Получить массив доступных полей формы
     */
    protected function getFields()
    {
        return array(
            'city'         => array('required' => true),
            'employment'   => array('required' => true),
            'birthday'     => array('required' => true),
            'name'         => array('required' => true),
            'patronymic'   => array(),
            'surname'      => array('required' => true),
            'mobile_code'  => array('required' => true),
            'mobile_phone' => array('required' => true),
            'email'        => array('required' => true),
        );
    }


    /**
     * Получить массив валидных данных
     */
    protected function getValidData()
    {
        return array(
            'city'         => 'Уфа',
            'employment'   => 'Работаю по постоянному контракту',
            'birthday'     => date('Y-m-d', strtotime('25 years ago')),
            'name'         => 'Василий',
            'patronymic'   => 'Иванович',
            'surname'      => 'Тёркин',
            'mobile_code'  => '123',
            'mobile_phone' => '1234567',
            'email'        => 'vasya@nail.ru',
        );
    }


    /**
     * План тестирования ошибок валидации
     */
    protected function getValidationTestingPlan()
    {
        $validInput = $this->getValidInput();
        $fields = array_keys($this->getValidData());

        return array(
            // Ничего не отправлено
            'Empty request' => new sfPHPUnitFormValidationItem(
                array(),
                array_combine(
                    array_diff($fields, array('patronymic')), 
                    array_fill(0, count($fields) - 1, 'required') 
                )
            ),
            // Отчество может быть пустым
            'Empty patronymic' => new sfPHPUnitFormValidationItem(
                array_merge($validInput, array('patronymic'  => '')),
                array()
            ),
            // Ошибка в мыле
            'Password required: mail' => new sfPHPUnitFormValidationItem(
                array_merge(
                    $validInput, 
                    array(
                        'email' => 'root@localhost',
                    )
                ),
                array(
                    'email' => 'invalid',
                )
            ),
        );
    }
}
