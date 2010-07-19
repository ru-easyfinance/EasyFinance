<?php

// Подключить константы старого сайта
require_once dirname(__FILE__).'/../../include/config.php';

require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();


class ProjectConfiguration extends sfProjectConfiguration
{
    /**
     * Zend_Loader_Autoloader
     */
    static protected $zendAutoloader = null;


    /**
     * SetUp
     */
    public function setup()
    {
        $this->initPlugins();

        // Escaper
        sfOutputEscaper::markClassesAsSafe(array(
            'DateTime',
        ));

        // г-но, но без этого Zend_Mail_Message raw письма по русски не читает
        // @see http://framework.zend.com/issues/browse/ZF-3591
        @ini_set('iconv.internal_encoding', 'UTF-8');
  }


    /**
     * Инициализировать все плагины для CLI
     * Конкретные приложения перекрывают этот метод
     */
    protected function initPlugins()
    {
        $this->enablePlugins(array(
            'sfDoctrinePlugin',
            'sfPhpunitPlugin',
        ));
    }


    /**
     * Настройки Doctrine
     */
    public function configureDoctrine(Doctrine_Manager $manager)
    {
        // Legacy database
        $manager->setAttribute(Doctrine_Core::ATTR_QUOTE_IDENTIFIER, true);
        // $manager->setAttribute(Doctrine_Core::ATTR_AUTO_ACCESSOR_OVERRIDE, false);

        $manager->setAttribute(Doctrine_Core::ATTR_DEFAULT_TABLE_CHARSET, 'utf8');
        $manager->setAttribute(Doctrine_Core::ATTR_DEFAULT_TABLE_COLLATE, 'utf8_general_ci');
        $manager->setAttribute(Doctrine_Core::ATTR_DEFAULT_TABLE_TYPE,    'INNODB');

        // SoftDelete
        $manager->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, true);

        // что бы использовать IF | CASE и проч. SQL-полезняшки
        $manager->setAttribute(Doctrine::ATTR_PORTABILITY, Doctrine::PORTABILITY_ALL ^ Doctrine::PORTABILITY_EXPR);

        // Кастомный гидратор
        $manager->registerHydrator('FetchPair', 'Doctrine_Hydrator_FetchPair');
    }


    /**
     * Подключает ZF (автолоадер)
     * @use ProjectConfiguration::registerZend() перед использованием ZF-классов
     *
     * @return Zend_Loader_Autoloader instance
     */
    final static public function registerZend()
    {
        if (!self::$zendAutoloader) {
            set_include_path(implode(PATH_SEPARATOR, array(
                sfConfig::get('sf_lib_dir') . '/vendor',
                get_include_path(),
            )));
            require_once("Zend/Loader/Autoloader.php");
            self::$zendAutoloader = Zend_Loader_Autoloader::getInstance();
        }

        return self::$zendAutoloader;
    }

}
