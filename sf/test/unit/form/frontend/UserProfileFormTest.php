<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';
require_once sfConfig::get('sf_root_dir') . '/apps/frontend/lib/form/UserProfileForm.class.php';


/**
 * Форма редактирования профиля пользователя
 */
class form_frontend_UserProfileFormTest extends myFormTestCase
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
        if (!$user) {
            $user = $this->helper->makeUser($this->getValidData());
        }

        return new UserProfileForm($user);
    }


    /**
     * Получить массив доступных полей формы
     */
    protected function getFields()
    {
        return array(
            'user_login'        => array(),
            'user_service_mail' => array(),
            'name'              => array(
                'required'   => true,
            ),
            'user_mail'         => array(),
            'password_new'      => array(),
            'password_repeat'   => array(),
            'notify'            => array(),
            'password'          => array(
                'required' => false,
            ),
        );
    }


    /**
     * Получить массив валидных данных
     */
    protected function getValidData()
    {
        return array(
            'user_login'        => 'Login',
            'user_service_mail' => 'unique.mail',
            'name'              => 'Дядя Федор',
            'user_mail'         => 'real.mail@site.com',
            'password'          => 'qwertyasdf',
            'password_new'      => '',
            'password_repeat'   => '',
            'notify'            => 1,
        );
    }


    /**
     * План тестирования ошибок валидации
     */
    protected function getValidationTestingPlan()
    {
        $validInput = $this->getValidInput();

        return array(
            // Заполнить пароль для обновления мэйла
            'Password required: mail' => new sfPHPUnitFormValidationItem(
                array_merge($validInput, array(
                    'password'  => '',
                    'user_mail' => 'mynewmail@site.com',
                    )),
                array(
                    'password' => 'Нужно заполнить пароль для обновления',
                )),

            // Заполнить пароль для обновления пароля
            'Password required: new password' => new sfPHPUnitFormValidationItem(
                array_merge($validInput, array(
                    'password'     => '',
                    'password_new' => 'qwaszx',
                    )),
                array(
                    'password' => 'Нужно заполнить пароль для замены',
                )),
        );
    }


    /**
     * Сохранить профиль
     */
    public function testSaveSimple()
    {
        $input = $this->getValidInput();
        unset($input['password_new']);

        $user = $this->helper->makeUser($input);
        $form = new UserProfileForm($user);

        $expected = $input;
        unset($expected['password_repeat']);

        unset($input['user_mail']);
        $form->bind($input);
        $this->assertFormIsValid($form);

        $user = $form->save();
        $expected['password'] = sha1($input['password']);

        $this->assertEquals(1, $this->queryFind('User', $expected)->count(), 'Expected found 1 object (User)');
    }


    /**
     * Сохранить: обновить пароль
     */
    public function testUpdatePassword()
    {
        $input = $this->getValidInput();

        $user = $this->helper->makeUser($input);
        $form = new UserProfileForm($user);

        $input['password_new'] = 'testUpdatePassword';
        $form->bind($input);
        $this->assertFormIsValid($form);

        $formUser = $form->save();
        $expected = $input;
        $expected['password'] = sha1($input['password_new']);
        unset($expected['password_new'], $expected['user_service_mail'], $expected['password_repeat']);

        $this->assertEquals($expected['password'], $form->getObject()->getPassword());
        $this->assertEquals(1, $this->queryFind('User', $expected)->count(), 'Expected found 1 object (User)');
    }


    /**
     * Сохранить: обновить только email
     */
    public function testUpdateEmail()
    {
        $input = array(
            'user_login'        => 'Login',
            'user_service_mail' => 'unique.mail',
            'name'              => 'Дядя Федор',
            'user_mail'         => 'real.mail@site.com',
            'password'          => 'qwertyasdf',
            'notify'            => 1,
        );

        $user = $this->helper->makeUser($input);

        $input['user_mail'] = 'another.mail@site.com';
        $form = new UserProfileForm($user);

        $expected = $input;
        $expected['password'] = sha1($input['password']);
        unset($expected['user_service_mail']);

        $form->bind($input);
        $this->assertFormIsValid($form);

        $user = $form->save();
        $this->assertEquals(1, $this->queryFind('User', $expected)->count(), 'Expected found 1 object (User)');
    }

}
