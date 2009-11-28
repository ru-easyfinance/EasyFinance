<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля welcome
 * @category welcome
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Welcome_Controller extends _Core_Controller
{

	/**
	 * Блюдём интерфейс
	 *
	 */
	function __init(){}
	
	/**
	 * Страница по умолчанию без параметров
	 * @return void
	 */
	function index()
	{
		$welcome = new Welcome_Model();
		
		$this->tpl->assign('name_page', 'welcome');
	}
}
