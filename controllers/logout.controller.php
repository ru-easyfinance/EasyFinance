<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля logout
 * @category logout
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Logout_Controller extends Template_Controller
{
    /**
     * Индексная страница
     * @param <array> $args  mixed
     * @return void
     */
    function index($args)
    {
        Core::getInstance()->user->destroy();
        header("Location: /");
        exit;
    }
}