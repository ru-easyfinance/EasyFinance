<?php

/**
 * Базовый класс для модульных тестов
 */
abstract class UnitTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Do not preserve the global state when running in a separate PHP process.
     * @see PHPUnit_Framework_TestCase::run()
     */
    protected $preserveGlobalState = false;

    /**
     * @var DbSimple_Mysql
     */
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
     * @return DbSimple_Mysql
     */
    protected function getConnection()
    {
        return $this->db;
    }

}
