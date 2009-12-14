<?php

class _Core_Cache implements _Core_Cache_Interface
{
	
	private $backends = array();
	
	private $enabled;
	
	private $keyPrefix;
	
	private static $instance=null;
	
	public static function getInstance()
	{
		if( self::$instance === null )
		{
			throw new _Core_Cache_Exception('Cache must be init before call throw singleton method!');
		}
		
		return self::$instance;
	}
	
	public function __construct( $keyPrefix = false, $enabled = true )
	{
		self::$instance = $self;
		
		$this->enabled 	= (bool)$enabled;
		$this->keyPrefix 	= $keyPrefix;
	}
	
	public function addBackend( _Core_Cache_Interface $backend )
	{
		$this->backends[ get_class( $backend ) ] = $backend; 
	}
	
}