<?php
/**
 * Класс-родитель для классов контроллеров
 * @author Max Kamashev "ukko" <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * SVN $Id$
 */
class Template_Controller {

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

    public $template = 'template';

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

    /**
     * Если нам были переданы ошибочные данные, генерируем 404 страницу
     * @param $method
     * @param $args
     * @return void
     */
    public function __call($method, $args)
    {
        header("HTTP/1.0 404 Not Found");
        file_get_contents('/404.html');
    }
}