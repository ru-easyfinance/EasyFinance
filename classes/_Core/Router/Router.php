<?php

class _Core_Router
{
	protected $request;
	protected $hooks = array();
	
	protected $className 		= null;
	protected $methodName 		= null;
	protected $requestRemains 	= array();
	
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
		// Формируем массив для разбора (substr отрезает "/" в начале)
		$uri 		= substr( $this->request->uri, 1 );
		$uriArr		= explode( '/', $uri );
		
		// Цикл по реверсивному поиску класса\метода оО (ниже понятнее) =)		
		$iterations = sizeof($uriArr);
		for( $i = $iterations; $i >= 1; $i-- )
		{			
			// Пытаемся сформировать название класса из запроса
			$className = $this->formClassName( $uriArr );
			
			// если таковой существует ...
			if( class_exists( $className ) )
			{
				$this->className = $className;
				
				// Проверяем нет ли метода с имененем = последнему элементу запроса
				if( method_exists( $this->className, $lastPart ) )
				{
					$this->methodName	= $lastPart;
				}
				// ... ежели нету - устанавливаем метод по умолчанию
				else
				{
					$this->methodName	= 'index';
				}
				
				break;
			}
			
			// Перед переходом на последующую итерацию обрезаем последний элемент запроса 
			$this->requestRemains[] = $lastPart = array_pop( $uriArr );
			
			// Если итерация = 1 а класс ещё не определён
			if( $i == 1 && empty($this->className) )
			{
				//Подкидываем ещё один элемент в запрос
				array_unshift( $uriArr, 'index' );
				
				// Увеличиваем счётчик... 
				$i++;
			}
		}
		
		// Создаём обьект заглушку шаблонизатора
		// сделанно временно из за необходимости поддержки smarty
		$templateEngine = new _Core_TemplateEngine();
		
		// Вызов подключённых хуков
		foreach ( $this->hooks as $className )
		{
			call_user_func( array($className,'execRouterHook'), 
				$this->request,
				$this->className,
				$this->methodName,
				$this->requestRemains,
				$templateEngine
			);
		}
		
		try
		{
			$controller = new $this->className( $templateEngine );
			
			call_user_func( array( $controller, $this->methodName ), $this->requestRemains );
			
			unset($controller);
			
			$templateEngine->display( 'index.html' );
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
				self::redirect('/404', false, 404);
			}
		}
	}
	
	protected function formClassName( array $array )
	{
		$array = array_map( 'ucfirst', $array );
		
		$className = implode( '_', $array ) . '_Controller';
		
		return $className;
	}
}
