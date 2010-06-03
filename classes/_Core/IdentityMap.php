<?php
/**
 * Допиленная реализация шаблона Identity Map.
 * ( с возможностью получения списка обьектов\ id по типу )
 *
 * @copyright http://easyfinance.ru/
 * @author Andrew Tereshko aka mamonth
 */

class _Core_IdentityMap
{
    /**
     * Массив хранимых обьектов в формате :
     * array (
     *     'classname' => array (
     *         id => object,
     *     ),
     * )
     *
     * @var array
     */
    private $objects = array();

    /**
     * Экземпляр себя (Реализация синглетона)
     *
     * @var _Core_IdentityMap
     */
    private static $instance;


    /**
     * Закрытый конструктор для предотвращения прямого создания обьекта.
     *
     */
    private function __construct() { }

    /**
     * Получение экземпляра себя (Реализация синглетона)
     *
     * @return _Core_IdentityMap
     */
    public static function getInstance()
    {
        if( !isset( self::$instance ) )
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Проверка существования обьекта типа $className
     * с идентификатором $id
     *
     * @param string $className
     * @param integer $id
     */
    public static function get( $className, $id )
    {
        $self = self::getInstance();

        if(
            array_key_exists( $className, $self->objects )
            && array_key_exists( $id, $self->objects[ $className ] )
            && $self->objects[ $className ][ $id ] instanceof $className
        )
        {
            return $self->objects[ $className ][ $id ];
        }

        return null;
    }

    /**
     * Добавление обьекта
     *
     * @param object $object
     * @param integer $id
     */
    public static function add( $object, $id )
    {
        if( !is_object($object) )
        {
            throw new _Core_Exception( _Core_Exception::typeErrorMessage( $object, 'Object', 'object' ) );
        }

        if( is_null( $id ) )
        {
            throw new _Core_Exception( 'Object id cannot be null!' );
        }

        $self         = self::getInstance();
        $className     = get_class( $object );

        if ( $self->get($className, $id) != null )
        {
            throw new _Core_Exception( 'Object instance of "' . $className . '" already exist !' );
        }

        if( !array_key_exists( $className, $self->objects ) )
        {
            $self->objects[ $className ] = array();
        }

        $self->objects[ $className ][ $id ] = $object;
    }

    /**
     * Возвращает массив всех обьектов указанного типа (класса)
     *
     * @param string $className
     *
     * @return array
     */
    public static function getObjects( $className )
    {
        $self         = self::getInstance();
        $objects     = array();

        // echo $className;

        if( array_key_exists( $className, $self->objects ) )
        {
            $objects = $self->objects[ $className ];
        }

        return $objects;
    }

    /**
     * Возвращает массив всех идентификаторов обьектов указанного типа (класса)
     *
     * @param string $className
     * @return array
     */
    public static function getIds( $className )
    {
        $self        = self::getInstance();
        $ids        = array();

        if( array_key_exists( $className, $self->objects ) )
        {
            $ids = array_keys($self->objects[ $className ]);
        }

        return $ids;
    }
}
