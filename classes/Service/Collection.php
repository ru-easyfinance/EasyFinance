<?php
/**
 * Контейнер для услуг
 * @copyright http://easyfinance.ru/
 * @author Andrew Tereshko aka mamonth
 */

class Service_Collection extends _Core_Abstract_Collection
{
    /**
     * Загрузка всех услуг
     *
     * @return Service_Collection
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
}
