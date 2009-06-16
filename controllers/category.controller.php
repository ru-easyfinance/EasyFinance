<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля logi
 * @copyright http://home-money.ru/
 * SVN $Id$
 */
class Category_Controller extends Template_Controller
{
    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        Core::getInstance()->tpl->assign('name_page', 'login');
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {

    }

//// Если запрос пришел не от аякс
//if (!isset($_GET['ajax']) || !$_GET['ajax']) {
//	include(SYS_DIR_MOD."categories/categories.module.php");
//} else {
//	//include(SYS_DIR_MOD."categories/ajax/categories.ajax.php");
//	include(SYS_DIR_MOD."categories/categories.module.php");
//}
}