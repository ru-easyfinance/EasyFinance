<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
 /**
 * Ядро проекта, класс-одиночка
 * @category core
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
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
    public $db = null;

    /**
     * Массив зависимостей от загружаемых скриптов от подключаемых модулей
     * @var array
     */
    public static $js = array();

    /**
     * Ссылка на экземпляр класса User
     * @var User
     */
    public static $user = null;

    /**
     * Ссылка на экземпляр класса с валютами
     * @var Currency
     * @example Core::getInstance->currency[$id] Так можно получить системную валюту по id
     * @example Пример части массива системных валют
     * <code>array(
     * '2'=>array(
     *      'name'=>'Доллар США',
     *      'abbr'=>'$',
     *      'charCode'=>'USD',
     *      'value'=>'31.2424',
     *      'dirrection'=>'up'
     *  )),
     * </code>
     */
    public static $currency = null;

    /**
     * Массив с ошибками, которые будут сообщаться пользователю в виде всплывающего облачка
     * @var array mixed
     */
    public $errors = array();

    /**
     * Хранит массив с текущим путём
     * @var array
     * @example array('accounts','edit','14')
     */
    public $url = array();

    
    /**
     * Swift_Mailer instance
     *
     * @var object
     */
    public static $mailer = null;
    
    /**
     * Возвращает ссылку на себя
     * @example Core::getInstance()->parse_url();
     *
     * @example $core = Core::getInstance();
     * @example $core->parse_url();
     * @return Core
     */
    public static function getInstance ( )
    {
        if ( is_null ( self::$instance ) ) {
            self::$instance = new Core ();
        }

        return self::$instance;
    }

    /**
     * Конструктор копирования нам не нужен
     * @return void
     */
    private function __clone ( )
    {

    }

    /**
     * Конструктор, запрещаем его переопределение, сделав приватным
     * @return void
     */
    private function __construct( )
    {
        //self::$user = self::authUser();
    }

    /**
     * Инициализация подключения к БД
     */
    public function initDB()
    {
        // Инициализируем одно *единственное* подключение к базе данных
        $db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
        // И обработчик ошибок для бд
        $db->setErrorHandler('databaseErrorHandler');
        
        Core::getInstance()->db = $db;
        //Логгируем все запросы. Только во включенном режиме DEBUG
        if (DEBUG) {
            $db->setLogger('databaseLogger');
        }
    }
}
