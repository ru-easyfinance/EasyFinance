<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля категорий
 * @copyright http://home-money.ru/
 * SVN $Id$
 */
class Calendar_Controller extends Template_Controller
{
    private $model = null;
    private $tpl = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->tpl = Core::getInstance()->tpl;
        $this->tpl->assign('name_page', 'calendar');
        $this->model = new Calendar_Model();
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {

    }
}