<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';
require_once sfConfig::get('sf_root_dir') . '/apps/frontend/lib/form/BudgetCategoryEditForm.php';


/**
 * Форма для создания/редактирования бюджета
 */
class form_frontend_BudgetCategoryEditFormTest extends myFormTestCase
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
        return new BudgetCategoryEditForm();
    }


    /**
     * Получить массив валидных данных
     */
    protected function getValidData()
    {
        return array(
            'category_id' => 66,
            'type'        => 1,
            'start'       => '2010-08-01',
            'value'       => 123.45
        );
    }


    /**
     * Получить массив доступных полей формы
     */
    protected function getFields()
    {
        return array();
    }


    /**
     * Перекрыл sfPHPUnitFormTestCase::testAutoFields т.к. у нас нет виджетов
     */
    public function testAutoFields()
    {
    }


    /**
     * План тестирования ошибок валидации
     */
    protected function getValidationTestingPlan()
    {
        $validInput = $this->getValidInput();

        return array(
            // Ничего не отправлено
            'Empty request' => new sfPHPUnitFormValidationItem(
                array(),
                array(
                    'category_id' => 'required',
                    'type'        => 'required',
                    'start'       => 'required',
                )
            ),

            // Неверный тип
            'Invalid type' => new sfPHPUnitFormValidationItem(
                array_merge($validInput, array('type' => 321)),
                array(
                    'type' => 'invalid',
                )
            ),

            // Верный запрос
            'Valid input' => new sfPHPUnitFormValidationItem(
                $validInput,
                array()
            ),
        );
    }


    /**
     * Редактируем счет с начальным балансом
     * Балансовой операции нет
     */
    public function testValidData()
    {
        $input = $this->getValidInput();

        $form = $this->makeForm();
        $form->bind($input);
        $this->assertFormIsValid($form);
    }
}
