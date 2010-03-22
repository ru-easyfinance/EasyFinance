<?php

class scripts_updateCalendarTest extends PHPUnit_Framework_TestCase
{
    private $db;
    private $dir;

    public function setUp()
    {
        // инициализ базу данных
        define ('INDEX', true);
        $this->dir = "/www/ef.ru/releases/current";

        require_once $this->dir . "/include/config.php";

        $dsn      = 'mysql:dbname='.SYS_DB_BASE.';host=127.0.0.1';
        $user     = SYS_DB_USER;
        $password = SYS_DB_PASS;

        $this->db = mysql_connect(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PASS);
        mysql_select_db(SYS_DB_BASE, $this->db);

        mysql_query( "SET character_set_client = utf8;" );
        mysql_query( "SET character_set_results = utf8;" );
        mysql_query( "SET character_set_connection = utf8;" );

    }

    private function _runScript()
    {
        echo `php -f {$dir}/scripts/updateCalendarAndOperations.php`;
    }

    public function testConvertCount()
    {
        // Положить данные
        $sql = "INSERT INTO calendar_chains (`id`, `user_id`, `start`, `last`, `every`, `repeat`, `week`)
            SELECT o.id, o.user_id, o.start, o.last, o.every, o.repeat, o.week FROM calend o
            WHERE id=76706";

        mysql_query($sql);

        // запускаем скрипт
        

        // проверяем
        $expectedChainRow = array(


        );
        $this->assertEquals($expectedChainRow, $actualChainRow, "Calendar chain migration");

        // Операции
        $actualOpsCount = 2;
        $this->assertEquals($expectedOpsCount, $actualOpsCount, "Calendar operations count");

        $expectedOp1 = array();
        $expectedOp2 = array();
        $this->assertEquals($expectedOp1, $actualRowset[0], "Operation 1");
        $this->assertEquals($expectedOp2, $actualRowset[1], "Operation 2");

    }


    public function testConvertTillDate()
    {
        
    }
}
