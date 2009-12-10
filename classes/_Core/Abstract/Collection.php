<?php

abstract class _Core_Abstract_Collection implements IteratorAggregate,  ArrayAccess, Countable
{
	/**
	 * Массив обьектов
	 *
	 * @var array
	 */
	protected $container = array();
	

	/** 
	 * Функция, часть реализации интерфейса IteratorAggregate
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator( $this->container );
	}

	/**
	 * ArrayAccess implementation
	 */
	public function offsetExists($offset)
	{
		return isset( $this->container[$offset] );
	}

	public function offsetGet($offset )
	{
		return $this->container[$offset];
	}
	
	public function offsetSet($offset, $value )
	{
		
	}
	
	public function offsetUnset($offset )
	{
		unset($this->container[$offset]);
	}
	
	/**
	 * Countable implementation
	 */
	public function count()
    	{
        		return sizeof($this->container);
	}
}
