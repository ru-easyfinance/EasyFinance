<?php
require_once dirname(__FILE__) . '/../bootstrap.php';

/**
 * Registration_Model
 */
class models_RegistrationTest extends UnitTestCase
{
    private $user;
    private $model;


    /**
     * SetUp
     */
    protected function _start()
    {
        $this->user = array(
            'login'                 => 'some unique login',
            'email'                 => 'some unique email',
            'user_currency_default' => myMoney::RUR,
        );
        $this->getConnection()->query(sprintf("INSERT INTO users (user_login, user_mail, user_currency_default) VALUES ('%s')", implode("','", $this->user)));

        $this->model = new Registration_Model;
    }


    /**
     * Тест-план для testExistUser
     */
    public function testPlanForExistUser()
    {
        return array(
            array(0, 0, false, array()),
            array(1, 0, true,  array('login')),
            array(0, 1, true,  array('mail')),
            array(1, 1, true,  array('mail', 'login')),
        );
    }


    /**
     * exist user
     *
     * @dataProvider testPlanForExistUser
     */
    public function testExistUser($hasLogin, $hasEmail, $isFound, $errors)
    {
        $login = $hasLogin ? $this->user['login'] : 'some unknown login';
        $email = $hasEmail ? $this->user['email'] : 'some unknown email';
        $this->assertEquals($isFound, $this->model->exist_user($login, $email), 'exist_user');
        $this->assertEquals($errors, array_keys($this->model->getErrors()), 'Errors');
    }


    /**
     * Создать пользователя
     */
    public function testNewUser()
    {
        $this->model->new_user($name='name', $login='login', $password='pass', $confirm = null, $email = 'email');
        $this->assertTrue($this->model->exist_user($login, $email));
    }

}
