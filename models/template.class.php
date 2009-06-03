<?php
/**
 * Класс-родитель для классов-моделей
 * @author Max Kamashev "ukko" <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * SVN $Id$
 */
class Template_Model {

    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    public $db;

    /**
     * Ссылка на экземпляр Smarty
     * @var Smarty
     */
    public $tpl;

    /**
     * Конструктор
     * @param DbSimple_Mysql $db
     * @return void
     */
    public function __construct(DbSimple_Mysql $db, Smarty $tpl)
    {
        $this->db = $db;
        $this->tpl = $tpl;
    }
}