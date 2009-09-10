<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
 /**
 * Ядро проекта, класс-одиночка
 * @category core
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
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
            self::$instance = new Core ( );
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
        $module .= '_Controller';

        $action = array_shift($args);
        if(!$action) {
            $action = 'index';
        }

        $m = new $module();
        $m->$action($args);
    }

    /**
     * Проверяем авторизацию пользователя
     * @return bool
     */
    public static function authUser() {
        if (!self::$user) {
            self::$user = new User();
        }
    }
}