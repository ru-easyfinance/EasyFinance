<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Пользователь
 */
class model_UserTest extends myUnitTestCase
{
    /**
     * Установить пароль пользователя
     */
    public function testSetPassword()
    {
        $user = new User;
        $user->setPassword($password = "qwerty");

        $this->assertEquals(sha1($password), $user->getPassword());
    }


    /**
     * Проверка паролей
     */
    public function testCheckPassword()
    {
        $user = $this->helper->makeUser(array('password' => 123));

        $this->assertTrue($user->checkPassword(123));
        $this->assertFalse($user->checkPassword(321));
    }
}
