<?php
/**
 * Модель услуги
 * @copyright http://easyfinance.ru/
 * @author Andrew Tereshko aka mamonth
 */

class Service_Model
{
    protected $fields = array();

    protected $durty = false;

    protected function __construct( $id, $name, $desc )
    {
        if( !is_int( $id ) )
        {
            throw new Service_Exception( _Core_Exception::typeErrorMessage( $id, 'Service id', 'integer' ) );
        }

        $this->fields = array (
            'service_id'     => $id,
            'service_name' => $name,
            'service_desc'    => $desc,
        );

        _Core_IdentityMap::add( $this, $id );
    }

    public static function loadAll()
    {
        $modelsArray = array();

        $sql = 'select service_id, service_name, service_desc
            from services
            where service_active = 1';

        // Массив существующих моделей
        $exists = _Core_IdentityMap::getObjects( __CLASS__ );

        if( sizeof( $exists ) )
        {
            $sql .= ' and service_id not in(' . implode( ',', array_keys($exists) ) . ')';
        }

        $rows = Core::getInstance()->db->select($sql);

        foreach ( $rows as $row )
        {
            $model = new self( (int)$row['service_id'], $row['service_name'], $row['service_desc'] );

            $modelsArray[] = $model;
        }

        return $modelsArray;
    }

    public function __get( $variable )
    {
        if( array_key_exists( $variable, $this->fields) )
        {
            return $this->fields[ $variable ];
        }

        return null;
    }
}
