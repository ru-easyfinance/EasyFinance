<?php

/**
 * Базовый класс для модульных тестов
 */
class UnitTestCase extends PHPUnit_Framework_TestCase
{
    private $db;


    /**
     * SetUp
     */
    final protected function setUp()
    {
        $_POST = array();
        $_GET = array();

        // Для тестов контроллера
        $_SERVER["SERVER_PORT"] = 443;
        $_SERVER["REQUEST_METHOD"] = 'GET';
        $_SERVER["HTTP_HOST"] = trim(COOKIE_DOMEN, '.');
        $_SERVER["REQUEST_URI"] = '/index.php';


        $this->db = Core::getInstance()->db;
        $this->db->query("START TRANSACTION");

        $this->_start();
    }


    /**
     * SetUp hook
     */
    protected function _start()
    {
    }


    /**
     * TearDown
     */
    final protected function tearDown()
    {
        $this->_end();
        $this->db->query("ROLLBACK");
    }


    /**
     * TearDown hook
     */
    protected function _end()
    {
    }


    /**
     * Get database connection
     */
    protected function getConnection()
    {
        return $this->db;
    }

}
