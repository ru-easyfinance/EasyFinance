<?php
class Core
{
    /**
     * Ссылка на себя же
     * @var Core
     */
    private static $instance = null;

    /**
     * Возвращает ссылку на себя
     * @return Core
     */
    public static function getInstance ( )
    {
        if ( is_null ( self::$instance ) ) {
            self::$instance = new Singleton ( );
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
     * Конструктор копирования нам не нужен
     * @return void
     */
    private function __clone ( )
    {

    }



}