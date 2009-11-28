<?php
/**
 * Услуга
 * 
 * @copyright http://easyfinance.ru/
 * @author Andrew Tereshko aka mamonth
 */

class Service
{
	/**
	 * Экземпляр модели
	 *
	 * @var Service_Model
	 */
	protected $model;
	
	/**
	 * Конструктор
	 *
	 * @param Service_Model $model
	 */
	public function __construct( Service_Model $model )
	{
		$this->model = $model;
	}
	
	public function getId()
	{
		return $this->model->service_id;
	}
	
	public function getName()
	{
		return $this->model->service_name;
	}
	
	public function getDesc()
	{
		return $this->model->service_desc;
	}
}
