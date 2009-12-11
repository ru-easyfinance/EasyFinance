<?php

abstract class _Core_Abstract_Model
{
	/**
	 * Массив полей модели
	 *
	 * @var array
	 */
	protected $fields = array();
	
	/**
	 * Приватный конструктор, В модель не должно попадать то чего она не ожидает.
	 *
	 * @param array $row
	 */
	protected function __construct( array $row )
	{
		$this->fields = $row;
	}
	
	public function __get( $variable )
	{
		if( array_key_exists( $variable, $this->fields) )
		{
			return $this->fields[ $variable ];
		}
	}
	
	public function __set( $variable, $value )
	{
		if( array_key_exists( $variable, $this->fields ) )
		{
			$this->durty = true;
			
			_Core_ObjectWatcher::addDirty( $self );
			
			$this->fields[ $variable ] = $value;
		}
	}
	
	abstract public function save();
	
	abstract public function delete();
}
