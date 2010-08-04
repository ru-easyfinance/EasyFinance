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
        return array('login', 'password', 'remember');
    }


    /**
     * Получить валидные логин и пароль
     */
    protected function getValidData($doRemember = false)
    {
        $expected = array(
            'user_name'  => "test",
            'user_login' => "test",
            'password'   => "test1",
        );

        $user = $this->helper->makeUser($expected);
        unset($expected['user_name'], $expected['user_login']);

        $expected['login'] = 'test';
        $expected['remember'] = (boolean) $doRemember;

        return $expected;
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
                    'login'    => 'required',
                    'password' => 'required',
                    ''         => 'Неверный логин и/или пароль.',
                )),

            // Заполнен логин, пустой пароль
            'Empty password' => new sfPHPUnitFormValidationItem(
                array(
                    'login'    => 'some login',
                    'password' => '',
                ),
                array(
                    'password' => 'required',
                    ''         => 'Неверный логин и/или пароль.',
                )),

            // Неверный логин и пароль
            'Wrong login and password' => new sfPHPUnitFormValidationItem(
                array(
                    'login'    => 'wrong login',
                    'password' => 'foo bar',
                ),
                array(
                    ''         => 'Неверный логин и/или пароль.',
                )),

            // Галочко "запомни себя"
            'Remember boolean checked' => new sfPHPUnitFormValidationItem(
                $this->getValidData(true),
                array()),

            'Good login and password' => new sfPHPUnitFormValidationItem(
                $this->getValidData(),
                array()),
        );
    }
}
