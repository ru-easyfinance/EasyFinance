<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля start
 * @category start
 * @copyright http://home-money.ru/
 * @version SVN $Id: start.controller.php 109 2009-07-24 15:23:04Z ukko $
 */
class Start_Controller extends Template_Controller
{
    /**
     * Ссылка на класс Смарти
     * @var <Smarty>
     */
    private $tpl = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->tpl = Core::getInstance()->tpl;
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