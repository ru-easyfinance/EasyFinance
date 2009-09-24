<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс для управления пользователями
 * @author korogen
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * @category user
 * @version SVN $Id$
 */
class User
{
    /**
     * Массив, хранит свойства пользователя
     * @var array mixed
     *      int user_id       Ид пользователя
     *      string user_name  Имя пользователя, отображаемое на форуме
     *      string user_login Логин
     *      string user_pass  Пароль в формате SHA-1
     *      string user_mail  е-мейл
     *      date user_created  Дата создания аккаунта пользователя в формате (%d.%m.%Y)
     *      int user_active 0 - аккаунт неактивирован, 1 - активирован
     */
    private $props = array();

    /**
     * Массив, хранит категории пользователя
     * @var array mixed
     */
    private $user_category = array();

    /**
     * Массив, хранит счета пользователя
     * @var array mixed
     */
    private $user_account  = array();

    /**
     * Массив, хранит валюты пользователя
     * @var array mixed
     */
    private $user_currency = array();

    /**
     * Массив, который хранит в себе все теги пользователя
     * @var array mixed
     */
    private $user_tags = array();

    /**
     * Массив со списком фин.целей пользователя
     * @var array mixed
     */
    private $user_targets = array();

    /**
     *
     * @var <type>
     */
    private $user_periodic = array();

    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    private $db;

    /**
     * Конструктор
     * @return void
     */
    public function __construct()
    {
        // Если соединение пользователя защищено, то пробуем авторизироваться
        if (isset($_SERVER['HTTPS'])) {
             //Если есть кук с авторизационными данными, то пробуем авторизироваться
            if (isset($_COOKIE[COOKIE_NAME])) {
                if (is_null(Core::getInstance()->db)) {
                    Core::getInstance()->initDB();
                }
                $this->db = Core::getInstance()->db;

                if (!isset($_SESSION)) {
                    session_start();
                }

                $array = decrypt($_COOKIE[COOKIE_NAME]);
                $this->initUser($array[0], $array[1]);
            }
        // иначе, переходим в защищённое соединение, и снова пробуем авторизироваться
        } else {
            header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            exit;
        }

        $this->load(); //Пробуем загрузить из сессии данные
    }

    /**
     * Возвращает Id пользователя
     * @return int || null
     */
    public function getId()
    {
        if (isset($this->props['id']) && !empty($this->props['id'])) {
            return $this->props['id'];
        } else {
            return null;
        }
    }

    /**
     * Иниализирует пользователя, достаёт из базы некоторые его свойства
     * @param string $login
     * @param string $pass  SHA1 пароля
     * @return bool
     */
    public function initUser($login, $pass)
    {
        if (is_null(Core::getInstance()->db)) {
            Core::getInstance()->initDB();
        }
        $this->db = Core::getInstance()->db;
        if (isset($_SESSION['REMOTE_ADDR']) || isset($_SESSION['HTTP_USER_AGENT'])) {
             if ($_SESSION['REMOTE_ADDR'] !== $_SERVER['REMOTE_ADDR'] || $_SESSION['HTTP_USER_AGENT'] !== $_SERVER['HTTP_USER_AGENT']) {
                 $this->destroy();
             }
        }
        //@FIXME Вероятно, стоит подключаться к базе лишь в том случае, если в сессии у нас пусто
        $sql = "SELECT id, user_name, user_login, user_pass, user_mail,
                    DATE_FORMAT(user_created,'%d.%m.%Y') as user_created, user_active,
                    user_currency_default, user_currency_list, user_type
                FROM users
                WHERE user_login  = ?
                    AND user_pass = ?
                    AND user_new  = 0";

        $this->props = $this->db->selectRow($sql, $login, $pass);
        if (count($this->props) == 0) {
            trigger_error('Не верный логин или пароль! ' . $login . ' ' . $pass , E_USER_WARNING);
            $this->destroy();
            return false;
        } elseif ($this->props['user_active'] == 0) {
            trigger_error('Ваш профиль был заблокирован!', E_USER_WARNING);
            $this->destroy();
            return false;
        }
        // Если у нас подключен профиль пользователя
        if ($this->props['user_type'] == 0) {
            if (!$this->init($this->getId())) {
               //@TODO Вызывать мастера настройки счетов, категорий и валют
            }
            $_SESSION['user']            = $this->props;
            $_SESSION['REMOTE_ADDR']     = $_SERVER['REMOTE_ADDR'];
            $_SESSION['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
            return $this->save();
        // Если у нас подключен профиль эксперта
        } else if ($this->props['user_type'] == 1) {
            $_SESSION['user']            = $this->props;
            $_SESSION['REMOTE_ADDR']     = '/experts/';
            $_SESSION['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
            return $this->save();
        }
        
    }

    /**
     * Сериализуем данные в сессии
     * @return bool
     */
    public function save ()
    {
        
        $_SESSION['user']          = $this->props;
        $_SESSION['user_category'] = $this->user_category;
        $_SESSION['user_account']  = $this->user_account;
        $_SESSION['user_currency'] = $this->user_currency;
        $_SESSION['user_tags']     = $this->user_tags;
        $_SESSION['user_targets']  = $this->user_targets;
        $_SESSION['pop_targets']   = $this->pop_targets;
        return true;
    }

    /**
     * Вызывает инициализацию пользовательских категорий, счетов, денег
     * @return bool 
     */
    public function init()
    {
        $this->initUserCurrency();
        $this->initUserCategory();
        $this->initUserAccounts();
        $this->initUserTags();
        $this->initUserTargets();
    }

    /**
     * Удаляет сессию и куки пользователя, очищает авторизацию
     * @return void
     */
    public function destroy()
    {
        if (!empty ($_SESSION)) {
            session_destroy();
        }
        setcookie(COOKIE_NAME, '', COOKIE_EXPIRE - 60, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
    }

    /**
     * Загружает ранее объявленные параметры пользователя из сессии
     * @return void
     */
    public function load()
    {

        if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
            $this->props = $_SESSION['user'];
        } else {
            $this->props = array();
        }
        if (isset($_SESSION['user_category']) && is_array($_SESSION['user_category'])) {
            $this->user_category = $_SESSION['user_category'];
        } else {
            $this->user_category = array();
        }
        if (isset($_SESSION['user_account']) && is_array($_SESSION['user_account'])) {
            $this->user_account = $_SESSION['user_account'];
        } else {
            $this->user_account = array();
        }
        if (isset($_SESSION['user_currency']) && is_array($_SESSION['user_currency'])) {
            $this->user_currency = $_SESSION['user_currency'];
        } else {
            $this->user_currency = array(1); //Устанавливает валюты пользователя (по умолчанию устанавливается 1 - русский рубль)
        }
        if (isset($_SESSION['user_tags']) && is_array($_SESSION['user_tags'])) {
            $this->user_tags = $_SESSION['user_tags'];
        } else {
            $this->user_tags = array();
        }

        if (isset ($_SESSION['user_targets']) && is_array($_SESSION['user_targets']) ) {
            $this->user_targets = $_SESSION['user_targets'];
        } else {
            $this->user_targets = array();
        }
        return true;
    }

    /**
     * Инициализирует пользовательские категории
     * @param string $id хэш-MD5 ид пользователя
     * @return void
     */
    public function initUserCategory ()
    {
        $sql = "SELECT * FROM category
            WHERE user_id = ? AND cat_active = '1' ORDER BY cat_parent, cat_name;";
        $this->user_category = array();
        $category = $this->db->select($sql, $this->getId());
        foreach ($category as $val) {
            $this->user_category[$val['cat_id']] = $val;
        }
    }

    /**
     * Получает список валют
     * @param array mixed $user_currency_list
     * @param int $currency_default
     * @return void
     */
    public function initUserCurrency ($user_currency_list = null, $currency_default = null)
    {
        // Если обновляем список валют с нуля
        if (!$user_currency_list) {
            $currency = unserialize($this->props['user_currency_list']);
        } else { // Если мы переназначаем список валют пользователя
            $currency = $user_currency_list;
            $this->props['user_currency_list'] = serialize($user_currency_list);
        }
        // Если нужно сменить валюту по умолчанию
        if ($currency_default) {
            $this->props['user_currency_default'] = (int)$currency_default;
        }

        if (!is_array($currency)) {
            trigger_error('Ошибка десериализации валют пользователя', E_USER_NOTICE);
            $currency = array(1);
        }

        $this->user_currency = array();
        foreach (Core::getInstance()->currency as $key => $val) {
            if (in_array($key, $currency)) {
                // В начало массива добавляем валюту  по умолчанию
                if ( $this->props['user_currency_default'] == $key ) {
                    $this->user_currency = array($key=>$val) + $this->user_currency;
                } else {
                    $this->user_currency[$key] = $val;
                }
            }
        }
    }

  	/**
     * Возвращает счета пользователя
     * @return void
     */
	public function initUserAccounts()
	{
        $sql = "SELECT a.* , t.*, (SELECT SUM(o.money) FROM operation o WHERE o.user_id=a.user_id AND o.account_id=a.account_id) AS total_sum
            FROM accounts a
            LEFT JOIN account_types t ON t.account_type_id = a.account_type_id
            WHERE a.user_id=?";
        $this->user_account= array();
        $accounts = $this->db->select($sql, $this->getId());
        foreach ($accounts as $val) {
            $val['account_currency_name'] = Core::getInstance()->currency[$val['account_currency_id']]['abbr'];
            $this->user_account[$val['account_id']] = $val;
        }
	}

    /**
     * Возвращает все теги пользователя
     * @return void
     */
    public function initUserTags()
    {
        $sql = "SELECT name, COUNT(name) as cnt FROM tags WHERE user_id = ? GROUP BY name ORDER BY COUNT(name) DESC";
        $array = $this->db->select($sql, $this->getId());
        $this->user_tags = array();
        foreach ($array as $v) {
            $this->user_tags[$v['name']] = $v['cnt'];
        }
    }

    /**
     * Возвращает фин. цели
     * @return void
     */
    public function initUserTargets()
    {
        $this->user_targets = array();
        $this->user_targets['user_targets'] = $this->db->select("SELECT id, category_id as category, title, amount,
            DATE_FORMAT(date_begin,'%d.%m.%Y') as start, DATE_FORMAT(date_end,'%d.%m.%Y') as end, percent_done,
            forecast_done, visible, photo,url, comment, target_account_id AS account, amount_done, close
            FROM target WHERE user_id = ? ORDER BY date_end ASC LIMIT ?d,?d;",
            $this->getId(), 0, 5);
        
        $this->user_targets['pop_targets'] = $this->db->select("SELECT t.title, COUNT(t.id) AS cnt, SUM(`close`) AS
            cl FROM target t WHERE t.visible=1 GROUP BY t.title,
            t.`close` ORDER BY cnt DESC, t.title ASC LIMIT ?d, ?d;", 0, 10);
    }

    /**
     * Возвращает пользовательские категории
     * @return array mixed
     */
    function getUserCategory()
    {
        return $this->user_category;
    }
    /**
     * Возвращает пользовательские валюты
     * @return array mixed
     */
    function getUserCurrency()
    {
        return $this->user_currency;
    }

    /**
     * Возвращает пользовательские счета
     * @return array mixed
     */
    function getUserAccounts()
    {
        return $this->user_account;
    }

    /**
     * Возвращает все теги пользователя
     * @param bool $cloud Если установлено true, то возвратит с частотой использования для составления облаков
     * @example $user->getUserTags() возвратит array('мама','папа','я')
     * @example $user->getUserTags(true) возвратит array('мама'=>7,'папа'=>10,'я'=>3)
     * @return array mixed
     */
    function getUserTags($cloud = false)
    {
        if ($cloud) {
            return $this->user_tags;
        } else {
            return array_keys($this->user_tags);
        }
    }

    /**
     * Возвращает фин.цели пользователя и популярные фин.цели
     * @FIXME Возвращает ТОЛЬКО последние изменения. Не использовать для страницы фин.целей!
     * @return array mixed
     */
    function getUserTargets()
    {
        return $this->user_targets;
    }

    /**
     * Получить свойство пользователя
     * @param $prop string
     *      int user_id
     *      string user_name
     *      string user_login
     *      string user_pass //WTF???
     *      string user_mail
     *      date user_created (%d.%m.%Y)
     *      int user_active  0 - аккаунт неактивен
     * @return mixed
     */
    function getUserProps($prop)
    {
        if (isset($this->props['$prop'])) {
            return $this->props['$prop'];
        }
    }

}