<?php
/**
 * Контейнер для сертификатов эксперта
 * @copyright http://easyfinance.ru/
 * @author Andrew Tereshko aka mamonth
 */

class Certificate_Container
{
    /**
     * Массив обьектов типа Certificate
     *
     * @var array
     */
    private $container = array();

    /**
     * Создание и наполнение контейнера сертификатов пользователя
     *
     * @param oldUser $user
     * @return Certificate_Container
     */
    public static function loadByUser( oldUser $user )
    {
        $container = new Certificate_Container();

        $modelArray = Certificate_Model::loadByUserId( (int)$user->getId() );

        foreach ( $modelArray as $model )
        {
            $container->add( new Certificate( $model ) );
        }

        return $container;
    }

    /**
     * Добавление сертификата в контейнер
     *
     * @param Certificate $certificate
     */
    public function add( Certificate $certificate )
    {
        $this->container[ $certificate->getId() ] = $certificate;
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