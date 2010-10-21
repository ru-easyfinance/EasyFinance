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

    protected static $myCurrencyExchange = null;

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

        // Событие на получение "из контекста" обменника валют
        // @see sfContext::__call
        $this->dispatcher->connect('context.method_not_found', array(__CLASS__, 'getMyCurrencyExchange'));

        // см. ниже. Вообще это хак.
        $this->dispatcher->connect('doctrine.configure_connection', array(__CLASS__, 'bufferedQueriesEnablerHack'));
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
            'myAuthPlugin',
            'sfWebBrowserPlugin',
            'myDoctrineLoggerPlugin',
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

        // Глобальный кастомный Query класс
        // @see http://www.doctrine-project.org/projects/orm/1.2/docs/manual/configuration/en#configure-query-class
        $manager->setAttribute(Doctrine_Core::ATTR_QUERY_CLASS, 'myBaseQuery');

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


    /**
     * Загружает объект обменника валют
     */
    public static function getMyCurrencyExchange(sfEvent $event)
    {
        $params = $event->getParameters();
        if ($params['method'] != 'getMyCurrencyExchange')
            return false;

        if (!self::$myCurrencyExchange) {
            $currencies = Doctrine::getTable('Currency')->createQuery()->execute(array(), Doctrine::HYDRATE_ARRAY);
            self::$myCurrencyExchange = new myCurrencyExchange();

            foreach ($currencies as $currency) {
                // такого не должно быть по идее, но есть :-(
                if ($currency['rate'] != 0) {
                    self::$myCurrencyExchange->setRate($currency['id'], $currency['rate'], myCurrencyExchange::BASE_CURRENCY);
                }
            }
        }

        // set return value and stop chain
        $event->setReturnValue(self::$myCurrencyExchange);
        return true;
    }


    /**
     * Хак, вешаем принудительно на коннект использование "буферов" =/
     */
    public static function bufferedQueriesEnablerHack(sfEvent $event)
    {
        $event['connection']->getDbh()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    }

}
