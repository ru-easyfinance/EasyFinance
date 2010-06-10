<?php
require_once dirname(__FILE__) . '/../bootstrap.php';

/**
 * Login_Model
 */
class models_LoginTest extends UnitTestCase
{
    /**
     * Создать набор дефолтных категорий
     */
    public function testInsertDefaultCategories()
    {
        $userId = CreateObjectHelper::makeUser();
        $date = date('Y-m-d H:i:s');
        Login_Model::defaultCategory($userId);

        $count = $this->getConnection()->selectCell("SELECT count(*) FROM category WHERE user_id=?", $userId);
        $this->assertTrue((bool)$count, "Expected categories were created");

        // Timestampable
        $cat = $this->getConnection()->selectRow("SELECT * FROM category WHERE user_id=? LIMIT 1", $userId);
        $this->assertEquals($date, $cat['created_at']);
        $this->assertEquals($cat['created_at'], $cat['updated_at']);
    }


    /**
     * Создать набор дефолтных счетов
     */
    public function testInsertDefaultAccounts()
    {
        $userId = CreateObjectHelper::makeUser();
        $date = date('Y-m-d H:i:s');
        Login_Model::defaultAccounts($userId);

        $count = $this->getConnection()->selectCell("SELECT count(*) FROM accounts WHERE user_id=?", $userId);
        $this->assertTrue((bool)$count, "Expected accounts were created");

        // Timestampable
        $cat = $this->getConnection()->selectRow("SELECT * FROM accounts WHERE user_id=? LIMIT 1", $userId);
        $this->assertEquals($date, $cat['created_at']);
        $this->assertEquals($cat['created_at'], $cat['updated_at']);
    }
}
