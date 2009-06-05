<?php
class Core
{
    /**
     * Ссылка на себя же
     * @var Core
     */
    private static $instance = null;

    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    public static $db;

    /**
     * Ссылка на экземпляр Smarty
     * @var Smarty
     */
    public static $tpl;

    /**
     * Возвращает ссылку на себя
     * @return Core
     */
    public static function getInstance ( )
    {
        if ( is_null ( self::$instance ) ) {
            self::$instance = new Core ( );
        }

        return self::$instance;
    }

    /**
     * Конструктор, запрещаем его переопределение, сделав приватным
     * @return void
     */
    private function __construct( )
    {
        //TODO: Код конструктора здесь
    }

    /**
     * Разбираем URL и вызываем нужные модули
     * @return void
     */
    public function parseUrl()
    {
        $args   = explode('/',$_SERVER['REQUEST_URI']);

        $module = array_shift($args);
        if (empty($module)) {
            $module = array_shift($args);
        }
        if(!$module) {
            $module = DEFAULT_MODULE;
        }
        $module .= '_Controller';

        $action = array_shift($args);

        if(!$action) {
            $action = 'index';
        }

        $m = new $module();
        $m->$action($args);
    }

    /**
     * Конструктор копирования нам не нужен
     * @return void
     */
    private function __clone ( )
    {

    }


}