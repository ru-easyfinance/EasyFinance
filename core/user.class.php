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
    private $props = Array();

    /**
     * Массив, хранит категории пользователя
     * @var <array> mixed
     */
    private $user_category = Array();

    /**
     * Массив, хранит счета пользователя
     * @var <array> mixed
     */
    private $user_account  = Array();

    /**
     * Массив, хранит валюты пользователя
     * @var <array> mixed
     */
    private $user_currency = Array();

    /**
     * Ссылка на экземпляр DBSimple
     * @var <DbSimple_Mysql>
     */
    private $db;

    /**
     * Массив с недостающими параметрами пользователя
     * @var <array> 
     */
    private $wizard = array();

    /**
     * Конструктор
     * @return void
     */
    public function __construct()
    {
        $this->db = Core::getInstance()->db;

        // Если соединение пользователя защищено, то пробуем авторизироваться
        //if (isset($_SERVER['HTTPS'])) {
            // Если есть кук с авторизационными данными, то пробуем авторизироваться
            //if (isset($_COOKIE[COOKIE_NAME])) {
                //$array = decrypt($_COOKIE[COOKIE_NAME]);
                //$this->initUser($array[0],$array[1]);
            //}
        // иначе, переходим в защищённое соединение, и снова пробуем авторизироваться
        //} else {
            //header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            //exit;
        //}

        $this->load(); //Пробуем загрузить из сессии данные
    }

    /**
     * Возвращает Id пользователя
     * @return <int> || false
     */
    public function getId()
    {
        if (isset($this->props['id']) && !empty($this->props['id'])) {
            return $this->props['id'];
        } else {
            return false;
        }
    }

    /**
     * Иниализирует пользователя, достаёт из базы некоторые его свойства
     * @param <string> $login
     * @param <string> $pass  MD5 пароля
     * @return bool
     */
    public function initUser($login, $pass)
    {
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
        if ($this->props['user_type'] == 0){
            if (!$this->init($this->getId())) {
               //@TODO Вызывать мастера настройки счетов, категорий и валют
            }
            $_SESSION['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
            return $this->save();
        }
        else if ($this->props['user_type'] == 1)
        {
            $_SESSION['user']            = $this->props;
            $_SESSION['REMOTE_ADDR']     = '/experts/';
            $_SESSION['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
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
        return true;
    }

    /**
     * Вызывает инициализацию пользовательских категорий, счетов, денег
     * @param $id string user_id
     * @return bool 
     */
    public function init($id)
    {
        $this->initUserCurrency();
        $this->initUserCategory();
        $this->initUserAccounts();
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
     * @return void
     */
    public function initUserCurrency ()
    {
        $currency = unserialize($this->props['user_currency_list']);
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
        $sql = "SELECT a.*, act.* FROM accounts a
            LEFT JOIN account_types act
                ON act.account_type_id = a.account_type_id
            WHERE user_id= ? ";
        $this->user_account= array();
        $accounts = $this->db->select($sql, $this->getId());
        foreach ($accounts as $val) {
            $val['account_currency_name'] = Core::getInstance()->currency[$val['account_currency_id']]['abbr'];
            $this->user_account[$val['account_id']] = $val;
        }
	}

    /**
     * Возвращает массив с профилем пользователя, с полями : ид, имя, логин, почта
     * @param $id int
     * @return array mixed
     */
    function getProfile($id)
    {
        $sql = "SELECT `id`, `user_name`, `user_login`, `user_mail` FROM `users` WHERE `id` = ? ;";
        return $this->db->selectRow($sql, $id);
    }

    /**
     * Обновляет профиль пользователя
     * @param $user_pass string Текущий пароль пользователя
     * @param $new_passwd string
     * @param $user_name string
     * @param $user_mail string
     * @param $user_login string
     * @return bool
     */
    function updateProfile($user_pass, $new_passwd, $user_name, $user_mail, $user_login)
    {
        //XXX Сделать шифрование пароля в SHA1
        if (!empty($new_passwd)) {
            $sql = "UPDATE users SET user_name = ?, user_mail = ?,user_pass = ?
                        WHERE id = ? AND user_pass = ? LIMIT 1;";
            $this->db->query($sql, $user_name, $user_mail, MD5($new_passwd), $this->getId(), MD5($user_pass));
            return $this->initUser($user_login, $new_passwd);
        }else{
            $sql = "UPDATE users SET user_name = ?, user_mail = ?
                        WHERE id = ? AND user_pass = ? LIMIT 1;";
            $this->db->query($sql, $user_name, $user_mail, $this->getId(), MD5($user_pass));
            return $this->initUser($user_login, $user_pass);
        }
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