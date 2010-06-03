<?php
/**
 * Модель услуги эксперта
 * @copyright http://easyfinance.ru/
 * @author Andrew Tereshko aka mamonth
 */

class Service_Expert_Model extends Service_Model
{
    /**
     * Модель услуги
     *
     * @var Service_Model
     */
    private $serviceModel;

    protected function __construct( Service_Model $serviceModel, $price, $term )
    {
        $this->serviceModel = $serviceModel;

        $this->fields = array (
            'service_price'     => $price,
            'service_term'     => $term,
        );
    }

    public static function loadByUserId( $userId )
    {
        if( !is_int( $userId ) )
        {
            throw new Service_Exception( _Core_Exception::typeErrorMessage( $userId, 'User id', 'integer' ) );
        }

        $sql = 'select s.service_id, service_name, service_desc, service_price, service_cur_id, service_term
            from services_expert se
            left join services s on se.service_id = s.service_id
            where s.service_active = 1 and se.user_id = ?';

        $rows = Core::getInstance()->db->select($sql, $userId);

        foreach ( $rows as $row )
        {
            $serviceModel = new Service_Model( (int)$row['service_id'], $row['service_name'], $row['service_desc'] );

            $model = new self( $serviceModel, $row['service_price'], $row['service_term'] );

            $modelsArray[] = $model;
        }

        return $modelsArray;
    }

    public static function create( $serviceId, $price, $term )//( User_Expert_Model $user, Service_Model $service, $price, $term )
    {
        // Ужас, знаю, но учитывая нехватку времени работать будет, и сохранит интерфейс
        $serviceModel = new Service_Model( $serviceId, '', '' );

        return new Service_Expert_Model( $serviceModel, $price, $term );
    }

    public static function insertAll( $userId, Service_Expert_Collection $container )
    {
        $inserts = array();

        foreach ( $container->getIterator() as $service )
        {
            $inserts[] = '( ' . $service->getId() . ',' . (int)$userId . ',' . $service->getPrice() . ', 1, ' . $service->getTerm() . ' )';
        }

        $sql = 'insert into services_expert
            (service_id, user_id, service_price, service_cur_id, service_term)
            values' . implode( ',' , $inserts );

        Core::getInstance()->db->query($sql);
    }

    public static function deleteAll( $userId )
    {
        $sql = 'delete from services_expert where user_id = ?';

        Core::getInstance()->db->query($sql, $userId);
    }

    public function __get( $variable )
    {
        if( array_key_exists( $variable, $this->fields) )
        {
            return $this->fields[ $variable ];
        }

        if( array_key_exists( $variable, $this->serviceModel->fields) )
        {
            return $this->serviceModel->fields[ $variable ];
        }

        return null;
    }
}
