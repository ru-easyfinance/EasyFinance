<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля logout
 * @category logout
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Logout_Controller extends _Core_Controller
{
	
	protected function __init(){}
    /**
     * Индексная страница
     * @param <array> $args  mixed
     * @return void
     */
    function index($args)
    {
        Core::getInstance()->user->destroy();
	if (IS_DEMO) {
        	header("Location: https://" . URL_ROOT_MAIN);
	        exit;
	} else {
        	header("Location: /");
	        exit;
	}
    }
}
