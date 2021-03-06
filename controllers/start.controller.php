<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля start
 * @category start
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Start_Controller extends _Core_Controller
{
    /**
     * Конструктор класса
     * @return void
     */
    protected function __init()
    {
        $this->tpl->assign('name_page', 'start');
    }

    /**
     * Индексная страница
     * @param <array> $args  mixed
     * @return void
     */
    function index($args)
    {
        $this->tpl->assign('name', $_SESSION['user']['user_name']); //@FIXME Поправить имя
        $this->tpl->assign('name_page', 'start');
    }
}