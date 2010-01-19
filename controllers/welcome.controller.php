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
}
