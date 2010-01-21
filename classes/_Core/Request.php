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
	
	private function __construct()
	{
		
	}
	
	public static function getCurrent()
	{
		$request = new self();
		
		$request->scheme 	= $request->getScheme();
		$request->method 	= $_SERVER['REQUEST_METHOD'];
		$request->host 	= $_SERVER["HTTP_HOST"];
		
		$request->post	= $request->escapeVarsArray( $_POST );
		$request->get	= $request->escapeVarsArray( $_GET );
		
		$request->uri	= $request->cleanUri( $_SERVER["REQUEST_URI"] );
		
		return $request;
	}
	
	public function getFake( $uri, $domain = false, $get=false, $post=false, $method = false )
	{
		
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
