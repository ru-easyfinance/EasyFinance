<?php if (!defined('INDEX')) trigger_error("Index required!", E_USER_WARNING);
/**
 * Класс контроллера для страницы интеграции
 * @copyright http://easyfinance.ru/
 */

class Integration_Controller extends _Core_Controller
{

    /**
     * Ссылка на класс User
     * @var User
     */
    private $user = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __init()
    {
        $this->user  = Core::getInstance()->user;
        $this->tpl->assign('name_page', 'integration/amt');
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index()
    {
    }

}