<?php

class _Core_Router
{
	protected $request;
	protected $hooks;
	
	protected $className;
	protected $methodName;
	
	protected $view;
	
	public function __construct( _Core_Request $request )
	{
		$this->request = $request;
	}
	
	public function addHook( $className )
	{
		if( class_exists( $className ) && in_array( '_Core_Router_iHook', class_implements($className) ) )
		{
			$this->hooks[] = $className;
		}
	}
	
	public function performRequest()
	{
		//some kind of internal logic
		
		// Разбираем запрос, отделяем его от переменных и адреса
		// Ищем класс убирая по куску с конца ( если пусто - Index )
		// Ищем метод (если пусто - index)
		// Вызываем метод.
		// В случае неудачи - исключение
		
		$class = '';
		$method = '';
		$chunks = array();
		
		foreach ( $this->hooks as $className )
		{
			call_user_func( array($className,'execRouterHook'), $this->request, $class, $method, $chunks );
		}
		
		$view = $this->getTemplateEngine();
		
		try
		{
			$controller = new $class( $view );
			
			call_user_func( array( $controller, $method ), $chunks );
			
			//$view->display();
		}
		catch ( Exception $e )
		{
			$this->redirect( '/404', false, 404 );
		}
	}
	
	public static function redirect( $url, $isExternal = false, $statusCode = 200 )
	{
		// Если внешний - редирект заголовком.
		// Если нет -
		// Формируем соотв. запрос
		// Отдаём заголовок
		
		$router = new _Core_Router( $request );
		
		try
		{
			$router->performRequest();
		}
		catch ( Exception $e )
		{
			// Вывод отладочной информации
			if(  DEBUG )
			{
				
				exit();
			}
			// Не позволяем бесконечных циклов
			elseif( '/404' == $url )
			{
				exit();
			}
			else
			{
				_Core_Router::redirect('/404', false, 404);
			}
		}
	}
}
