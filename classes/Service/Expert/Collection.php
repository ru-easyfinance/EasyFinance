<?php
/**
 * Контейнер для услуг эксперта
 * @copyright http://easyfinance.ru/
 * @author Andrew Tereshko aka mamonth
 */

class Service_Expert_Collection extends Service_Collection
{
	/**
	 * Экземпляр обьекта пользователя
	 *
	 * @var User
	 */
	private $user;
	
	/**
	 * Конструктор
	 *
	 * @param User $user
	 */
	public function __construct( User $user )
	{
		$this->user = $user;
	}
	
	/**
	 * Создание и наполнение контейнера услуг эксперта
	 *
	 * @param User $user
	 * @return Service_Expert_Container
	 */
	public static function load( User $user )
	{
		$container = new self( $user );
		
		$modelArray = Service_Expert_Model::loadByUserId( (int)$user->getId() );
		
		foreach ( $modelArray as $model )
		{
			$container->container[ (int)$model->service_id ] = new Service_Expert( $model );
		}
		
		return $container;
	}
	
	/**
	 * Добавление эксперту оказываемой услуги
	 *
	 * @param integer $serviceId Идентификатор 
	 * @param integer $cost Стоимость
	 * @param integer $term Срок исполнения
	 */
	public function add( $serviceId, $cost, $term)//Service $service )
	{
		$model = Service_Expert_Model::create( $serviceId, $cost, $term );
		
		$this->container[ (int)$model->service_id ] = new Service_Expert( $model );
	}
	
	/**
	 * Сохранение услуг экперта.
	 *
	 */
	public function save()
	{
		Service_Expert_Model::deleteAll( (int)$this->user->getId() );
		
		Service_Expert_Model::insertAll( (int)$this->user->getId(), $this );
	}
}
