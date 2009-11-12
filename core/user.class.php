<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс для управления пользователями
 * @author korogen
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://easyfinance.ru/
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
     * Массив с событиями 
     * @var array mixed
     */
    private $user_events = array();

    /**
     * Массив с бюджетом пользователя
     * @var array mixed
     */
    private $user_budget = array();

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
        //if ($_SERVER['SERVER_PORT'] == 443) {
             //Если есть кук с авторизационными данными, то пробуем авторизироваться
            if (isset($_COOKIE[COOKIE_NAME])) {
                if (isset($_COOKIE['PHPSESSID'])) {
                    if (!isset($_SESSION)) {
                        session_start();
                    }
                    $this->load(); //Пробуем загрузить из сессии данные
                }
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
//        } else {
//            header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//            exit;
//        }
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
        session_start();
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
            trigger_error('Не верный логин или пароль!', E_USER_WARNING);
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
        $this->initUserEvents();
        $this->initUserBudget();
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

        if (isset ($_SESSION['user_events']) && is_array($_SESSION['user_events']) ) {
            $this->user_events = $_SESSION['user_events'];
        } else {
            $this->user_events = array();
        }

        if (isset ($_SESSION['user_budget']) && is_array($_SESSION['user_budget']) ) {
            $this->user_budget = $_SESSION['user_budget'];
        } else {
            $this->user_budget = array();
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
        $sql = "SELECT DISTINCT c.*,
            (SELECT count(id) FROM operation o WHERE o.cat_id=c.cat_id AND c.user_id=o.user_id) AS howoften
            FROM category AS c
            WHERE c.user_id = ? AND c.cat_active = '1' AND c.visible='1'
            ORDER BY c.cat_parent, c.cat_name;";
        $this->user_category = array();
        $category = $this->db->select($sql, $this->getId());
        foreach ($category as $val) {
            $val['cat_name'] = stripslashes($val['cat_name']);
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
            /*$sql = "SELECT a.* , t.*, (SELECT SUM(o.money) FROM operation o WHERE o.user_id=a.user_id AND o.account_id=a.account_id) AS total_sum
                , (SELECT COUNT(o.money) FROM operation o WHERE o.user_id=a.user_id AND o.account_id=a.account_id) AS o_count
                FROM accounts a
                LEFT JOIN account_types t ON t.account_type_id = a.account_type_id
                WHERE a.user_id=? ORDER BY o_count DESC";*/
            $sql = "SELECT a.* , t.*, (SELECT SUM(o.money) FROM operation o WHERE o.user_id=a.user_id AND transfer = 0 AND o.account_id=a.account_id) AS total_sum
                , (SELECT COUNT(o.money) FROM operation o WHERE o.user_id=a.user_id AND o.account_id=a.account_id) AS o_count
                FROM accounts a
                LEFT JOIN account_types t ON t.account_type_id = a.account_type_id
                WHERE a.user_id=? ORDER BY o_count DESC";
            $accounts = $this->db->select($sql, $this->getId());

            $sql = "SELECT (SELECT SUM(-o.money) FROM operation o WHERE o.user_id=a.user_id AND transfer != 0 AND o.account_id=a.account_id) AS total_sum
            , (SELECT COUNT(o.money) FROM operation o WHERE o.user_id=a.user_id AND o.account_id=a.account_id) AS o_count
            FROM accounts a
            LEFT JOIN account_types t ON t.account_type_id = a.account_type_id
            WHERE a.user_id=? ORDER BY o_count DESC";
            $accounts2 = $this->db->select($sql, $this->getId());

            $sql = "SELECT (SELECT SUM(o.money) FROM operation o WHERE o.user_id=a.user_id AND transfer = a.account_id) AS total_sum
            , (SELECT COUNT(o.money) FROM operation o WHERE o.user_id=a.user_id AND o.account_id=a.account_id) AS o_count
            FROM accounts a
            LEFT JOIN account_types t ON t.account_type_id = a.account_type_id
            WHERE a.user_id=? ORDER BY o_count DESC";
            $accounts3 = $this->db->select($sql, $this->getId());

            $this->user_account= array();
            foreach ($accounts as $key=>$val) {
                $val['total_sum'] += $accounts2[$key]['total_sum']+$accounts3[$key]['total_sum'];
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
        $sql = "SELECT name, COUNT(name) as cnt FROM tags WHERE user_id = ? GROUP BY name ORDER BY cnt DESC";
        $array = $this->db->select($sql, $this->getId());
        $this->user_tags = $array;
//        foreach ($array as $v) {
//            $this->user_tags[$v['name']] = $v['cnt'];
//        }
    }

    /**
     * Возвращает фин. цели
     * @return void
     */
    public function initUserTargets()
    {
        $this->user_targets = array();
        $this->user_targets['user_targets'] = $this->db->select("SELECT t.id, t.category_id as category, t.title, t.amount,
            DATE_FORMAT(t.date_begin,'%d.%m.%Y') as start, DATE_FORMAT(t.date_end,'%d.%m.%Y') as end, t.percent_done,
            t.forecast_done, t.visible, t.photo,t.url, t.comment, t.target_account_id AS account, t.amount_done, t.close, t.done
            ,(SELECT b.money FROM target_bill b WHERE b.target_id = t.id ORDER BY b.dt_create ASC LIMIT 1) AS money
            FROM target t WHERE t.user_id = ? ORDER BY t.date_end ASC LIMIT ?d,?d;", $this->getId(), 0, 20);
        
        $this->user_targets['pop_targets'] = $this->db->select("SELECT t.title, COUNT(t.id) AS cnt, SUM(`close`) AS
            cl FROM target t WHERE t.visible=1 GROUP BY t.title,
            t.`close` ORDER BY cnt DESC, t.title ASC LIMIT ?d, ?d;", 0, 10);
    }

    /**
     * Возвращает Текущие События пользователя
     */
    public function initUserEvents()
    {
        $array = $this->db->select("SELECT c.id, c.chain, c.title, DATE_FORMAT(c.near_date,'%d.%m.%Y') as date,
            c.comment, c.event, c.amount, c.category, DATEDIFF(NOW(),c.near_date) AS diff, IFNULL(p.account,0) AS account, 
            IFNULL(p.drain,0) AS drain
            FROM calendar c
            LEFT JOIN periodic p ON c.chain = p.id
            WHERE c.close=0 AND c.near_date < NOW() AND c.user_id=?  ORDER BY date DESC", $this->getId());
        $this->user_events = array();
        foreach ($array as $var) {
            $this->user_events[$var['id']] = $var;
        }
    }
    
    /**
     * Возвращает бюджет пользователя на текущий месяц
     */
    public function initUserBudget()
    {
        $this->user_budget = Budget_Model::loadBudget(null, null, $this->getId(), $this->getUserCategory());
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
            foreach ($this->user_tags as $v) {
                $user_tags[$v['name']] = $v['cnt'];
            }
            return array_keys($user_tags);
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
     * Возвращает текущие события пользователя
     */
    public function getUserEvents()
    {
        return $this->user_events;
    }

    /**
     * Возвращает пользовательский бюджет
     * @return array
     */
    public function getUserBudget()
    {
        return $this->user_budget;
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