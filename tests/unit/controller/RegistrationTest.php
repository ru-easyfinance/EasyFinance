<?php
require_once dirname(__FILE__) . '/../bootstrap.php';


/**
 * Тестовый контроллер, чтобы хоть как-то его можно было изолировать
 */
class controller_RegistrationTest_Controller extends Registration_Controller
{
    public
        $output,
        $authIsCalled = false;

    /**
     * Перекрываем конструктор, чтобы не тянуть лишние зависимости
     */
    public function __construct()
    {
        $this->tpl = new _Core_TemplateEngine_Json();
        $this->__init();
    }

    /**
     * Контроллер у нас привык "умирать" в json-ом в зубах
     */
    protected function _output(array $data)
    {
        $this->output = $data;
    }

    /**
     * А в деструкторе вообще делаются страшные вещи
     */
    public function __destruct()
    {
    }

    /**
     * Заглушка для авторизации пользователя,
     * потому что оригинал ломает вывод куками
     */
    protected function _authenticateUser()
    {
        $this->authIsCalled = true;
    }
}


/**
 * Test
 */
class controller_RegistrationTest extends UnitTestCase
{
    /**
     * Успешная регистрация
     */
    public function testRegistrationSuccessful()
    {
        $_SERVER["REQUEST_METHOD"] = 'POST';
        $_POST = array(
            'login'            => 'somelogin',
            'password'         => 12345,
            'confirm_password' => 12345,
            'name'             => 'User Name',
            'mail'             => 'user.email@example.org',
        );

        $ctl = new controller_RegistrationTest_Controller;
        $ctl->new_user();

        $this->assertTrue($ctl->authIsCalled, 'Auth is called');
        $this->assertEquals(array('result' => array(
            'text'     => 'Спасибо, вы зарегистрированы!',
            'redirect' => "".URL_ROOT_MAIN."my/review/",
        )), $ctl->output);
    }


    /**
     * Такой пользователь уже существует
     */
    public function testUserExists()
    {
        $login = 'someuniquelogin';
        $email = 'someuniqueemail@example.org';

        $this->getConnection()->query(sprintf(
            "INSERT INTO users (user_login, user_mail, user_currency_default)
            VALUES ('{$login}', '{$email}', '" . myMoney::RUR . "')"));


        $_SERVER["REQUEST_METHOD"] = 'POST';
        $_POST = array(
            'login'            => $login,
            'password'         => 12345,
            'confirm_password' => 12345,
            'name'             => 'User Name',
            'mail'             => $email,
        );

        $ctl = new controller_RegistrationTest_Controller;
        $ctl->new_user();

        $this->assertFalse($ctl->authIsCalled, 'Auth is NOT called');
        $this->assertEquals(array('error' => array(
            'text' => 'Пользователь с таким адресом электронной почты уже зарегистрирован!<br />Пользователь с таким логином уже существует!',
        )), $ctl->output);
    }

}
