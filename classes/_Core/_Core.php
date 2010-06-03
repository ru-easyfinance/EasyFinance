<?php
/**
 * Основной класс системы, инициализирует всё необходимое для работы.
 *
 * @copyright easyfinance.ru
 * @author Andrew Tereshko aka mamonth
 * @package _Core
 */
class _Core
{
    public function __construct()
    {
        spl_autoload_register( array('_Core','__autoload') );

        $this->initCache();
    }

    public static function __autoload( $className )
    {
        $classNameChunks = explode( '_', $className );

        if( !$classNameChunks[0] )
        {
            array_shift($classNameChunks);
            $classNameChunks[0] = '_' . $classNameChunks[0];
        }

        $classPath = SYS_DIR_ROOT . '/classes/' . implode( '/', $classNameChunks );

        if( !file_exists( $classPath . '.php' ) )
        {
            $classPath .= '/' . array_pop( $classNameChunks );
        }

        if( !file_exists($classPath . '.php') )
        {
            //throw new _Core_Exception( 'File with class "' . $className . '" was not found at "' . $classPath . '.php" !' );
            return false;
        }

        require_once( $classPath . '.php' );

        if( !class_exists($className,false) && !interface_exists($className,false) )
        {
            //throw new _Core_Exception( 'Class "' . $className . '" was not found in "' . $classPath . '.php" !' );
            return false;
        }

        return true;
    }

    private function initCache()
    {
        // Кеширование. Инициализация.
        $cache = new _Core_Cache(false, CACHE_ENABLED);

        // Файловый кеш
        if( CACHE_ENABLED )
        {
            $cache->addBackend(
                new _Core_Cache_Backend_Files( CACHE_FILES_DIR )
            );
        }
        // Memcached кеш
        if( CACHE_ENABLED && MEMCACHE_ENABLED )
        {
            $cache->addBackend(
                new _Core_Cache_Backend_Memcache( MEMCACHE_HOST, MEMCACHE_PORT )
            );
        }
    }
}

//require_once( 'Exception.php' );
