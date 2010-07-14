<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';
require_once dirname(__FILE__).'/../../../../plugins/myAuthPlugin/lib/form/BaseMyAuthForm.php';
require_once dirname(__FILE__).'/../../../../plugins/myAuthPlugin/lib/form/myAuthForm.php';
require_once dirname(__FILE__).'/../../../../plugins/myAuthPlugin/lib/validator/myValidatorAuthUser.php';

/**
 * Форма для обработки запроса на синхронизацию
 */
class form_myAuthFormTest extends sfPHPUnitFormTestCase
{
    protected $saveForm = false;


    /**
     * Создать форму
     */
    protected function makeForm()
    {
        return new myAuthForm();
    }


    /**
     * Получить массив доступных полей формы
     */
    protected function getFields()
    {
        return array('login', 'password');
    }


    /**
     * Получить валидные логин и пароль
     */
    protected function getValidData()
    {
        $expected = array(
            'user_name' => "test",
            'login'     => "test",
            'password'  => "test1",
        );

        $user = $this->helper->makeUser($expected);
        unset($expected['user_name']);

        return $expected;
    }


    /**
     * План тестирования ошибок валидации
     */
    protected function getValidationTestingPlan()
    {
        $expected = $this->getValidData();
        return array(
            // Ничего не отправлено
            'Empty request' => new sfPHPUnitFormValidationItem(
                array(),
                array(
                    'login'    => 'required invalid',
                    'password' => 'required',
                )),

            // Заполнен логин, пустой пароль
            'Empty password' => new sfPHPUnitFormValidationItem(
                array(
                    'login'    => 'wrong login',
                    'password' => '',
                ),
                array(
                    'login'    => 'invalid',
                    'password' => 'required',
                )),

            // Неверный логин и пароль
            'Wrong login and password' => new sfPHPUnitFormValidationItem(
                array(
                    'login'    => 'wrong login',
                    'password' => 'foo bar',
                ),
                array(
                    'login'    => 'invalid',
                )),

            'Good login and password' => new sfPHPUnitFormValidationItem(
                $expected,
                array()
                ),
        );
    }
}