<?php

/**
 * _Core_Request
 *
 */
class _Core_Request
{
	private $method; // POST,GET,PUT etc
	private $scheme; // http, https
	private $post 	= array();
	private $get 		= array();
	
	private $host; // easyfinance.ru www.easyfinance.ru, demo.easyfinance.ru
	
	private $uri;
	
	/**
	 * Ссылка на текущий запрос
	 *
	 * @var _Core_Request
	 */
	private static $currentInstance = null;
	
	private function __construct()
	{
		
	}
	
	public static function getCurrent()
	{
		if( !(self::$currentInstance instanceof self) )
		{
			self::$currentInstance = new self();
			
			self::$currentInstance->scheme = self::$currentInstance->getScheme();
			self::$currentInstance->method = $_SERVER['REQUEST_METHOD'];
			self::$currentInstance->host 	= $_SERVER["HTTP_HOST"];
			
			self::$currentInstance->post	= self::$currentInstance->escapeVarsArray( $_POST );
			self::$currentInstance->get	= self::$currentInstance->escapeVarsArray( $_GET );
			
			self::$currentInstance->uri	= self::$currentInstance->cleanUri( $_SERVER["REQUEST_URI"] );
		}
		
		return self::$currentInstance;
	}
	
	public function getFake( $uri, $domain = false, $get=false, $post=false, $method = false )
	{
		$request = new self();
		
		$request->uri 	= $request->cleanUri( $uri );
		$request->host 	= ($domain)?$domain:$_SERVER["HTTP_HOST"];
		$request->post 	= ($post)?$post:self::$currentInstance->escapeVarsArray( $_POST );
		$request->get	= ($get)?$get:self::$currentInstance->escapeVarsArray( $_GET );
		
		return $request;
	}
	
	protected function getScheme()
	{
		return ($_SERVER["SERVER_PORT"] == 443 )?'http':'https';
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $uri
	 * @return string
	 */
	protected function cleanUri( $uri )
	{
		$uriArr = explode( '?', $_SERVER["REQUEST_URI"] );
		
		return $uriArr[0];
	}
	
	/**
	 * Возвращает обработанные переменные
	 * 
	 * @todo Реализовать базовую проверку и ескейп
	 * 
	 * @param array $array
	 * @return array
	 */
	protected function escapeVarsArray( array $array )
	{
		return $array;
	}
	
	public function __get( $var )
	{
		if( isset( $this->$var ) )
		{
			return $this->$var;
		}
		else
		{
			throw new _Core_Exception( 'Requested variable "' . $var . '" not exist!' );
		}
	}
}
