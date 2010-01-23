<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * ����� ����������� ��� ������ "�����"
 * @copyright http://easyfinance.ru/
 * @version SVN $Id: $
 */

class Review_Controller extends _Core_Controller
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
		$this->tpl->assign('no_menu', '1');
		$this->tpl->assign('head_val', '/review/');
		$this->tpl->assign('name_page', 'review');
	}
}
