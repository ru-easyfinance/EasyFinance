<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля welcome
 * @category welcome
 * @copyright http://easyfinance.ru/
 * @version SVN $Id: welcome.controller.php 2433 2010-01-19 20:07:00Z mamonth $
 */
class Index_Controller extends _Core_Controller
{

	/**
	 * Блюдём интерфейс
	 *
	 */
	protected function __init(){}
	
	/**
	 * Страница по умолчанию без параметров
	 * @return void
	 */
	public function index()
	{
		$counters = array(
			'users'		=> 8443,
			'operations'	=> 943132
		);
		
		$countersFile = DIR_SHARED . 'counters.json';
		
		if( file_exists( $countersFile ) )
		{
			$countersJson = (array)json_decode( file_get_contents( $countersFile ) );
			
			if( is_array($countersJson) )
			{
				$counters = $countersJson;
			}
		}
		
		$this->tpl->assign('usersCount',  number_format($counters['users'], 0, ',', ' '));
		$this->tpl->assign('operationsCount', number_format($counters['operations'], 0, ',', ' '));
		
		$welcome = new Welcome_Model();
		
		$this->tpl->assign('name_page', 'welcome');
	}
	
	public function notfound()
	{
		$this->tpl->assign('no_menu', '1');
		$this->tpl->assign('name_page', '404');
	}
	
	public function review()
	{
		$this->tpl->assign('no_menu', '1');
		$this->tpl->assign('head_val', '/review/');
		$this->tpl->assign('name_page', 'review');
	}
}