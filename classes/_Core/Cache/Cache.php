<?php

/**
 * Фронтенд
 *
 */
class _Core_Cache implements _Core_Cache_Interface
{
	/**
	 * Массив обьектов бекендов
	 *
	 * @var array
	 */
	private $backends = array();
	
	/**
	 * Флаг работы кеша (на будущее)
	 *
	 * @var boolean
	 */
	private $enabled;
	
	/**
	 * Префикс для ключей (на случай нахождения данных
	 * с разных проектов в одном хранилище)
	 *
	 * @var string
	 */
	private $keyPrefix;
	
	/**
	 * Реализация singleton
	 *
	 * @var _Core_Cache
	 */
	private static $instance=null;
	
	public static function getInstance()
	{
		if( self::$instance === null )
		{
			throw new _Core_Cache_Exception('Cache must be init before call throuth singleton method!');
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
	
	public function get( $id )
	{
		$value = null;
		
		reset($this->backends);
		
		// Перебор бекендов в поисках значения
		// while экономит несколько мс времени и мб памяти 
		// т.к. не создаёт клона массива
		while (list(,$backend) = each($this->backends))
		{
			$value = $backend->get( $this->keyPrefix . $id );
			
			// Если было найдено валидное значение - прекращаем поиск.
			if( null !== $value )
			{
				break;
			}
		}
		
		return $value;
	}
	
	public function getMulti( array $ids )
	{
		$value = null;
		
		reset($this->backends);
		
		foreach ( $ids as $key=>&$id )
		{
			$id = $this->keyPrefix . $id;
		}
		
		// Перебор бекендов в поисках значения
		while (list(,$backend) = each($this->backends))
		{
			$value = $backend->getMulti( $ids );
			
			// Если было найдено валидное значение - прекращаем поиск.
			if( null !== $value )
			{
				break;
			}
		}
		
		return $value;
	}
	
	public function set( $id, $value, $expired = null, array $tags = null )
	{
		reset($this->backends);
		
		while (list(,$backend) = each($this->backends))
		{
			$value = $backend->set( $this->keyPrefix . $id, $value, $expires );
		}
	}
	
	public function clean( $id )
	{
		reset($this->backends);
		
		while (list(,$backend) = each($this->backends))
		{
			$value = $backend->clean( $this->keyPrefix . $id );
		}
	}
}