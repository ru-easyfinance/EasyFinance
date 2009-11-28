<?php
/**
 * Контейнер для услуг
 * @copyright http://easyfinance.ru/
 * @author Andrew Tereshko aka mamonth
 */

class Service_Container
{
	/**
	 * Массив обьектов типа Service
	 *
	 * @var array
	 */
	protected $container = array();
	
	/**
	 * Загрузка всех услуг
	 *
	 * @return array
	 */
	public static function load()
	{
		$container = new self();
		
		$modelArray = Service_Model::loadAll();
		
		foreach ( $modelArray as $model )
		{
			$container->container[ (int)$model->service_id ] = new Service( $model );
		}
		
		return $container;
	}
	
	public function offsetGet( $name )
	{
		if( isset($this->container[$name]) )
		{
			return $this->container[$name];
		}
		else
		{
			return null;
		}
	}
	
	protected function offsetSet( $key, $value )
	{
		if( isset($this->container[$key]) )
		{
			throw new Exception('Already set!');
		}
		else
		{
			$this->container[$key] = $value;
		}
	}
	
	public function offsetExists( $key )
	{
		return isset($this->container[$key]);
	}
	
	public function offsetUnset( $key )
	{
		unset($this->container[$key]);
		
		return true;
	}
	
	public function count()
    	{
        		return sizeof($this->container);
	}
	
	public function getIterator()
	{
		return new ArrayIterator( $this->container );
	}
}
