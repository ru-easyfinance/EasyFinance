<?php

/**
 * Класс регулирующий доступ пользователей 
 * к разделам сайта на основе файла конфигурации
 * 
 * @copyright http://easyfinance.ru/
 * @author Andrew Tereshko aka mamonth
 */
class _Core_Access implements _Core_Router_Hook
{
	/**
	 * Конфигурация доступа
	 *
	 * @var array
	 */
	private $config = array();
	/**
	 * Обьект пользователя
	 *
	 * @var _User
	 */
	private $user;
	
	/**
	 * Конструктор
	 *
	 * @param _User $user
	 */
	public function __construct( $user )
	{
		$this->user = $user;
		
		$this->config = $this->loadConfig(  );
	}
	
	public static function execRouterHook( _Core_Request $request, &$class, &$method, array &$chunks )
	{
		$access = new _Core_Access( _User::getCurrent() );
		
		$requestString = strtolower( $class . '/' . $method );
		$requestString = str_replace( '_', '/' , $requestString );
		
		if( !$access->isAllowed( $requestString ) )
		{
			$class = 'Controller_Index';
			$method = '404';
		}
	}
	
	/**
	 * Проверяет разрешён ли доступ пользователю к запросу
	 *
	 * @param uri без get параметров $requestUri
	 * @return boolean
	 */
	public function isAllowed( $requestUri )
	{
		$allowed = false;
		
		$requestArr = explode( '/', $requestUri );
		
		// Если запрос начинался с "/" - отрезаем начало (пустой элемент)
		if( !$requestArr[0] )
		{
			array_shift( $requestArr );
		}
		
		$chunksCount = sizeof( $requestArr );
		
		for ( $i = 1; $i <= $chunksCount; $i++ )
		{
			$requestString = implode( '/', $requestArr );
			
			if( 
				isset( $this->config[ $request->uri ] )
				&& in_array( $this->user->getType(), $requestArr )
			)
			{
				$allowed = true;
				break;
			}
			else
			{
				// Если правило для uri не найдено
				// отрезаем последний элемент запроса и повторяем 
				array_pop( $requestArr );
			}
		}
		
		return $allowed;
	}
	
	private function loadConfig()
	{
		//include();
	}
}
