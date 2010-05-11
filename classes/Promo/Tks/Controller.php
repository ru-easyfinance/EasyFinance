<?php
if(!defined('INDEX')) trigger_error("Index required!", E_USER_WARNING);

/**
 * Класс контроллера для Promo страницы
 * @copyright http://easyfinance.ru/
 */
class Promo_Tks_Controller extends _Core_Controller
{

    /**
     * Ссылка на класс User
     * @var User
     */
    private $_user = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __init()
    {
        $this->_user = Core::getInstance()->user;
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index()
    {
        $this->tpl->assign('name_page', 'promo/tks');
    }


    function anketa()
    {
        $this->tpl->assign('name_page', 'promo/tks-anketa');
    }

}
