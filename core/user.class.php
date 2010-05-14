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
     * Массив с событиями календаря за три месяца (прошлый, текущий, будущий)
     * @var array mixed
     */
    private $user_events = array();

    /**
     * Массив с событиями на месяц вперёд
     * @var array
     */
    private $user_reminder = array();

    /**
     * Массив с неподтверждёнными событиями
     * @var array
     */
    private $user_overdue = array();

    /**
     * Массив с бюджетом пользователя
     * @var array mixed
     */
    private $user_budget = array();


    private $pop_targets = array();

    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    private $db;

    /**
     * Конструктор
     * @return void
     */
    public function __construct( $login = null, $password = null )
    {

         //Если есть кук с авторизационными данными, то пробуем авторизироваться
        if (isset($_COOKIE[COOKIE_NAME]))
        {

            if (isset($_COOKIE['PHPSESSID'])) {
                if (!session_id()) {
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

        // Для phpunit тестов, и создания юзера по логину и паролю
        } elseif ( $login && $password )  {

            $this->initUser( $login, sha1($password) );

        }
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

    public function getType()
    {
    	$userType = null;

    	if( sizeof($this->props) && array_key_exists('user_type', $this->props) )
    	{
    		$userType = (int)$this->props['user_type'];
    	}

    	return $userType;
    }

    public function getDefaultPage()
    {
    	return '/';
    }

    public function getName()
    {
    	return $this->getUserProps( 'user_name' );
    }

    /**
     * Иниализирует пользователя, достаёт из базы некоторые его свойства
     * @param string $login
     * @param string $pass  SHA1 пароля
     * @return bool
     */
    public function initUser($login, $pass)
    {

        if (is_null(Core::getInstance()->db))
        {
            Core::getInstance()->initDB();
        }

        $this->db = Core::getInstance()->db;

        // Если сохранённые в сессии данные о пользователе не совпадают с текущими
        if (
            ( isset($_SESSION['REMOTE_ADDR']) && isset($_SESSION['HTTP_USER_AGENT']) )
            && ( $_SESSION['REMOTE_ADDR'] !== $_SERVER['REMOTE_ADDR']
            || $_SESSION['HTTP_USER_AGENT'] !== $_SERVER['HTTP_USER_AGENT'] )
        )
        {
            // Уничтожаем пользователя
            $this->destroy();
        }

        //@FIXME Вероятно, стоит подключаться к базе лишь в том случае, если в сессии у нас пусто
        $sql = "SELECT id, user_name, user_login, user_pass, user_mail, getNotify,
            DATE_FORMAT(user_created,'%d.%m.%Y') as user_created, user_active, user_service_mail,
            user_currency_default, user_currency_list, user_type
            FROM users
            WHERE user_login  = ? AND user_pass = ? AND user_new  = 0";

        $this->props = $this->db->selectRow($sql, $login, $pass);

        if (count($this->props) == 0)
        {
            $this->destroy();
            return false;
        }

        // Если пользователь - эксперт: подгружаем дополнительные поля
        if($this->getType() === 1)
        {
            $sql = 'SELECT `user_info_short`, `user_info_full`, `user_img`, `user_img_thumb` FROM `user_fields_expert` WHERE `user_id` = ?';

            $expertProps = $this->db->selectRow( $sql, $this->getId() );

            // Если нет записи в таблице дополнительных свойств
            if( !sizeof( $expertProps) )
            {
                // Создаём её
                $this->db->query( 'INSERT into `user_fields_expert` (`user_id`) VALUES (?)', $this->getId() );

                // И делаем выборку заново
                $expertProps = $this->db->selectRow( $sql, $this->getId() );
            }

            $this->props += $expertProps;
        }

        $_SESSION['user']            = $this->props;
        $_SESSION['HTTP_USER_AGENT'] = @$_SERVER['HTTP_USER_AGENT'];
        $_SESSION['REMOTE_ADDR']     = @$_SERVER['REMOTE_ADDR'];

        // Если профиль пользователя
        if ($this->getType() === 0)
        {
            $this->init();
            if (_Core_Request::getCurrent()->host . '/' != URL_ROOT_PDA) {
                // Если у нас есть неподтверждённые операции, то переходим на них
                if ( count ($this->getUserEvents( 'overdue' ) ) > 0 ) {
                    $_SESSION['REQUEST_URI'] = '/calendar/#list';
                // Иначе переходим на самый первый счёт #1062
                } else {
                    $keys = array_keys($this->user_account);
                    if (count($keys) > 0) {
                        $_SESSION['REQUEST_URI'] = '/operation/#account=' . $keys[0];
                    }
                }
            }
        }
        // Если профиль эксперта
        elseif ($this->getType() === 1)
        {
            $_SESSION['REQUEST_URI']	= '/expert/';
        }

        return $this->save();
    }

    /**
     * Сериализуем данные в сессии
     * @return bool
     */
    public function save ()
    {
        $_SESSION['user']            = $this->props;
        $_SESSION['user_category']   = $this->user_category;
        $_SESSION['user_account']    = $this->user_account;
        $_SESSION['user_currency']   = $this->user_currency;
        $_SESSION['user_tags']       = $this->user_tags;
        $_SESSION['user_targets']    = $this->user_targets;
        $_SESSION['pop_targets']     = $this->pop_targets;
        $_SESSION['user_events']     = $this->user_events;
        $_SESSION['user_overdue']    = $this->user_overdue;
        $_SESSION['user_reminder']   = $this->user_reminder;

        // костыли для интеграции с sf
        $_SESSION['symfony/user/sfUser/authenticated'] = true;
        $_SESSION['symfony/user/sfUser/lastRequest'] = time();

        // дублируем для симфоньки, что бы достать можно было
        $_SESSION['symfony/user/sfUser/attributes']['user']['id'] = $this->props['id'];

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
            session_unset();
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
            $this->user_events   = $_SESSION['user_events'];
            $this->user_overdue  = $_SESSION['user_overdue'];
            $this->user_reminder = $_SESSION['user_reminder'];
        } else {
            $this->user_events   = array();
            $this->user_overdue  = array();
            $this->user_reminder = array();
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
        $this->user_category = array();

        $sql = "SELECT DISTINCT c.*,
            (SELECT count(id) FROM operation o WHERE o.cat_id=c.cat_id AND c.user_id=o.user_id) AS howoften
            FROM category AS c
            WHERE c.user_id = ? AND c.visible='1'
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
        $this->user_currency = array();

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

        if (!is_array($currency))
        {
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
        $this->user_account = array();

        $sql = "SELECT a.* , t.*, (SELECT SUM(o.money) FROM operation o WHERE o.user_id=a.user_id AND o.account_id=a.account_id) AS total_sum
            , (SELECT COUNT(o.money) FROM operation o WHERE o.user_id=a.user_id AND o.account_id=a.account_id) AS o_count
            FROM accounts a
            LEFT JOIN account_types t ON t.account_type_id = a.account_type_id
            WHERE a.user_id=? ORDER BY o_count DESC";
        $accounts = $this->db->select($sql, $this->getId());


        $this->user_account= array();
        foreach ($accounts as $key=>$val) {
            $val['account_currency_name'] = Core::getInstance()->currency[$val['account_currency_id']]['abbr'];
            if ( $val['total_sum'] == null )
                $val['total_sum']=0;
            $this->user_account[$val['account_id']] = $val;
        }
	}

    /**
     * Возвращает все теги пользователя
     * @return void
     */
    public function initUserTags()
    {
        $this->user_tags = array();

        $sql = "SELECT name, COUNT(name) as cnt FROM tags WHERE user_id = ? GROUP BY name ORDER BY cnt DESC";
        $array = $this->db->select($sql, $this->getId());
        $this->user_tags = $array;
    }

    /**
     * Возвращает фин. цели
     * @return void
     */
    public function initUserTargets()
    {
        $this->user_targets = array();
    	// Ежели нет пользователя - всё это не нужно.
    	if(!$this->getId())
    	{
    		return;
    	}

        $this->user_targets = array();
        $this->user_targets['user_targets'] = array();

        $userTargets = $this->db->select("SELECT t.id, t.category_id as category, t.title, t.amount,
            DATE_FORMAT(t.date_begin,'%d.%m.%Y') as start, DATE_FORMAT(t.date_end,'%d.%m.%Y') as end, t.percent_done,
            t.forecast_done, t.visible, t.photo,t.url, t.comment, t.target_account_id AS account, t.amount_done, t.close, t.done
            ,(SELECT b.money FROM target_bill b WHERE b.target_id = t.id ORDER BY b.dt_create ASC LIMIT 1) AS money
            FROM target t WHERE t.user_id = ? ORDER BY t.date_end ASC LIMIT ?d,?d;", $this->getId(), 0, 20);

        while ( list(,$target) = each($userTargets) )
        {
        		$this->user_targets['user_targets'][ $target['id'] ] = $target;
        }
        unset($userTargets);

        $this->user_targets['pop_targets'] = $this->db->select("SELECT t.title, COUNT(t.id) AS cnt, SUM(`close`) AS
            cl FROM target t WHERE t.visible=1 GROUP BY t.title,
            t.`close` ORDER BY cnt DESC, t.title ASC LIMIT ?d, ?d;", 0, 10);
    }

    /**
     * Возвращает Текущие События пользователя
     */
    public function initUserEvents()
    {
        // Получаем данные за текущий месяц
        $start = mktime( null, null, null, date( 'm' ), 1 );
        $end   = mktime( null, null, null, date( 'm' ) + 1, 0 );

        // Загружаем все события по календарю
        $calendar = new Calendar($this);
        $calendar->loadAll($this, $start, $end);
        $this->user_events = $calendar->getArray();

        // Загружаем все просроченные неподтверждённые события
        $calendar = new Calendar($this);
        $calendar->loadOverdue($this);
        $this->user_overdue = $calendar->getArray();

        // Загружаем все будущие события на неделю вперёд
        $calendar = new Calendar($this);
        $calendar->loadReminder($this);
        $this->user_reminder = $calendar->getArray();
    }

    /**
     * Возвращает бюджет пользователя на текущий месяц
     */
    public function initUserBudget()
    {
        $this->user_budget = array();
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
     * возвращает массив. 1ый элемент - сериализуемая строка. 2ой айди валюты по умолчанию
     * @return array mixed
     */
    function getCur()
    {
        $sql = "SELECT user_currency_list AS li, user_currency_default AS def FROM users WHERE id = ?";
        $li = $this->db->query($sql, $this->getId());
        $a = $li[0];

        return $a;
    }

    function getCurrencyByDefault($mas, $def)
    {

        if( !is_array($mas) )
        {
        	$mas = array();
        }

        $mas = "'".implode("','", $mas)."'";
        $sql = "SELECT MAX(currency_date) as last FROM daily_currency";
        $lastdate = $this->db->query($sql);
        $sql = "SELECT c.cur_id as id, dai.currency_sum as value, c.cur_char_code as charCode, c.cur_name as abbr, dai.direction
            FROM currency c, daily_currency dai WHERE dai.currency_id=c.cur_id
            AND dai.currency_from = ? AND currency_date = ?
            AND c.cur_id IN ($mas)";
        $li = $this->db->query($sql, $def, $lastdate[0]['last']);
        $sql = "SELECT $def as id, 1 as value, c.cur_char_code as charCode, c.cur_name as abbr
            FROM currency c WHERE c.cur_id = ?
            ";
        $li2 = $this->db->query($sql, $def);

        $res = array_merge($li, $li2);

        return $res;

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
    	$user_tags = array();

	if ($cloud)
	{
		$user_tags = $this->user_tags;
	}
	else
	{
            	foreach ($this->user_tags as $v)
            	{
			$user_tags[$v['name']] = $v['cnt'];
		}

		$user_tags = array_keys($user_tags);
	}

	return $user_tags;
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
     * @param string $type calendar | overdue | reminder
     * @return array
     */
    public function getUserEvents( $type = 'calendar' )
    {
        if ( $type == 'calendar' ) {
            return $this->user_events;
        } elseif ( $type == 'overdue' ) {
            return $this->user_overdue;
        } elseif ( $type == 'reminder' ) {
            return $this->user_reminder;
        }
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
    public function getUserProps($prop)
    {
        if (isset($this->props[ $prop ])) {
            return $this->props[ $prop ];
        }
    }

}
