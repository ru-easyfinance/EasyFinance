<?php

class _Core_Access implements _Core_Router_Hook
{
	private $config = array();
	private $user;
	
	public function __construct()
	{
		$user = _User::loadCurrent();
	}
	
	public static function execRouterHook( _Core_Request $request, $class, $method, array $chunks )
	{
		$access = new _Core_Access();
		
		if( !$access->isAllowed( $request ) )
		{
			$class = 'Controller_Index';
			$method = '404';
		}
	}
	
	public function isAllowed( _Core_Request $request )
	{
		if( $this->config[ $request->uri ] instanceof $user->getType )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
