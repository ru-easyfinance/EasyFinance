<?php

class _Core_Router
{
	private $request;
	private $hooks;
	
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
		}
		catch ( Exception $e )
		{
			$this->redirect( '/404', false, 404 );
		}
	}
	
	public function redirect( $url, $isExternal = false, $statusCode = 200 )
	{
		
	}
}
