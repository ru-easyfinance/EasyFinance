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


    /**
     * Проверка проверка долговой категории
     */
    public function testDebtCategory()
    {
        $user = $this->helper->makeUser(array('password' => 123));
        $category = $this->helper->makeCategory(
            $user,
            array('system_id' => Category::DEBT_SYSTEM_CATEGORY_ID)
        );

        $this->assertEquals(
            $user->getDebtCategoryId(),
            $category->getId(),
            'Долговая категория у пользователя не найдена'
        );
    }
}
