<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс для управления валютами
 * @category currency
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Currency
{
     /**
     * Ссылка на экземпляр DBSimple
     * @var <DbSimple_Mysql>
     */
    private $db;

    /**
     * Массив с системными валютами
     * @var <array> mixed
     */
    private $sys_list_currency;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->db = Core::getInstance()->db;
    }

    /**
     *
     * @return unknown_type
     */
    function listCurrency () {
        $sql = "SELECT FROM ";
    }
}