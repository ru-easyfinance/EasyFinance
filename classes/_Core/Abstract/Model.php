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
	 * Приватный конструктор, дабы не вызывали откуда не попадя
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
			
			$this->fields[ $variable ] = $value;
		}
	}
}
