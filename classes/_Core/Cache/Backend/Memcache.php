<?php

/**
 * Бекенд. Хранение в memcached.
 *
 */
class _Core_Cache_Backend_Memcache implements _Core_Cache_Interface
{
	/**
	 * Обьект memcache
	 *
	 * @var Memcache
	 */
	protected $server;
	
	/**
	 * Конструктор
	 *
	 * @param array $servers
	 * @param boolean $persistent
	 */
	public function __construct( $host, $port, $persistent = false )
	{
		if( !extension_loaded('memcache') || !class_exists( 'Memcache' ) )
		{
			throw new _Core_Cache_Exception('Extension "memcache" is not loaded or not up to date !');
		}
		
		$this->server = new Memcache;
		
		if( $persistent )
		{
			$connected = $this->server->pconnect( $host, $port );
		}
		else
		{
			$connected = $this->server->connect( $host, $port );
		}
		
		if( !$connected )
		{
			throw new _Core_Cache_Exception( 'Could not connect to memcached server on ' . $host . ':' . $port . ' !' );
		}
	}
	
	/**
	 * Получение данных
	 *
	 * @param string $id ключ
	 * @return mixed
	 */
	public function get( $id )
	{
		$value = $this->server->get( $this->formId($id) );
		return unserialize( $value );
	}
	
	/**
	 * Получение нескольких значений за раз
	 *
	 * @param array $ids массив ключей
	 */
	public function getMulti( array $ids )
	{
		// преобразование ключей
		$ids = array_map( array($this, 'formKey'), $ids );
		
		// наполнение возвращаемого массива
		// на случай невозврата данных
		$return = array_fill_keys( $ids, null );
		
		$return = array_merge( $return, array_map( 'unserialize', $this->server->get( $ids )) );
		
		return $return;
	}
	
	/**
	 * Сохранение данных в кеш
	 *
	 * @param string $id Идентификатор 
	 * @param mixed $value Данные
	 * @param integer $expire Кол-во секунд до протухания данных (по умолчанию - никогда)
	 */
	public function set( $id, $value, $expire = null)
	{
		$this->server->set( $this->formId($id), serialize($value), $expire );
	}
	
	/**
	 * Очистка (удаление) данных
	 *
	 * @param string $id ключ
	 */
	public function clean( $id )
	{
		return $this->server->delete( $this->formId($id) );
	}
	
	/**
	 * Формирование ключа. Вынесено для упрощения правок. 
	 *
	 * @param string $id
	 * @return mixed
	 */
	protected function formId( $id )
	{
		return md5( $id );
	}
}
