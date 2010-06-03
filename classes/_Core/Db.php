<?php
/**
 * Обёртка - одиночка для PDO
 *
 * @copyright easyfinance.ru
 * @author Andrew Tereshko aka mamonth
 * @package _Core
 */
class _Core_Db extends PDO
{
    /**
     * Экземпляр обьекта драйвера базы данных.
     *
     * @var _Core_Model
     */
    private static $instance = null;

    /**
     * Одиночка
     *
     * @return unknown
     */
    public static function getInstance()
    {
        if( self::$instance instanceof self )
        {
            return self::$instance;
        }

        self::$instance = new self( 'mysql:dbname=' . SYS_DB_BASE . ';host=' . SYS_DB_HOST, SYS_DB_USER, SYS_DB_PASS );
        self::$instance->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

        return self::$instance;
    }
}
