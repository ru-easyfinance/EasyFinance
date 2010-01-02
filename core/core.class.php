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
    public static $db = null;

    /**
     * Массив зависимостей от загружаемых скриптов от подключаемых модулей
     * @var array
     */
    public static $js = array();

    /**
     * Ссылка на экземпляр Smarty
     * @var Smarty
     */
    public static $tpl = null;

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
    public static $errors = array();

    /**
     * Хранит массив с текущим путём
     * @var array
     * @example array('accounts','edit','14')
     */
    public static $url = array();

    
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

    /**
     * Проверяет, разрешён ли доступ пользователю к ресурсу, если это гость
     * @param string $module
     * @return bool
     */
    private function isAllowedModule($module)
    {
        if (Core::getInstance()->user->getId() == '') {
            $modules = explode(',', GUEST_MODULES);
            if (!in_array($module, $modules)) {
                if (isset ($_SESSION)) {
                    session_start();
                }
                // Добавляем запрошенный адрес, если юзер не прошёл валидацию
                $_SESSION['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
                return false;
            }
        }
        return true;
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
        } elseif (substr($module,0, 7) == '?XDEBUG') { //@XXX Грязный хак, потом можно убрать
            $module = DEFAULT_MODULE;
        }
        

        // Смотрим разрешения на использование модуля
        if (!$this->isAllowedModule($module)) {
            header("Location: /login/");
            exit;
        }
        
        $action = array_shift($args);
        if(!$action) {
            $action = 'index';
        }

        Core::getInstance()->url[] = $module;
        Core::getInstance()->url[] = $action;
        //array_push(Core::getInstance()->$url, $args); //@FIXME

        $module .= '_Controller';
        $m = new $module();
        $m->$action($args);
    }
}
