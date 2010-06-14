<?php

class _Core_ObjectWatcher
{
    private static $instance;

    private $dirty = array();

    private $deleted = array();

    private function __construct(){}

    /**
     * Получение экземпляра себя (Реализация синглетона)
     *
     * @return _Core_ObjectWatcher
     * @example $m = _Core_ObjectWatcher::getInstance();
     */
    public static function getInstance()
    {
        if( !isset( self::$instance ) )
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function addDirty( _Core_Abstract_Model $model )
    {
        self::getInstance()->dirty[  ] = $model;
    }

    public static function addDeleted( _Core_Abstract_Model $model )
    {
        self::getInstance()->deleted[  ] = $model;
    }

    public function performOperations()
    {
        foreach ( $this->dirty as $dirtyModel )
        {
            $dirtyModel->save();
        }

        foreach ( $this->deleted as $deletedModel )
        {
            $deletedModel->delete();
        }
    }
}
