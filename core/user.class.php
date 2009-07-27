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
     * @var <array> mixed
     *      user_id string ??? //FIXME перейти на INT
     *      user_name string
     *      user_login string
     *      user_pass string //WTF???
     *      user_mail string
     *      user_created date (%d.%m.%Y)
     *      user_active int 0 - аккаунт неактивен
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
     * Конструктор
     * @return void
     */
    public function __construct()
    {
        $this->db = Core::getInstance()->db;
        
        // Если соединение пользователя защищено, то пробуем авторизироваться
        if (isset($_SERVER['HTTPS'])) {
            // Если есть кук с авторизационными данными, то пробуем авторизироваться
            if (isset($_COOKIE[COOKIE_NAME])) {
                $array = decrypt($_COOKIE[COOKIE_NAME]);
                $this->initUser($array[0],$array[1]);
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
     * @return string || false
     */
    public function getId()
    {
        if (isset($this->props['user_id']) && !empty($this->props['user_id'])) {
            return $this->props['user_id'];
        } else {
            return false;
        }
    }

    /**
     * Иниализирует пользователя, достаёт из базы некоторые его свойства
     * @param $login string
     * @param $pass string MD5 пароля
     * @return bool
     */
    public function initUser($login, $pass)
    {
        if (isset($_SESSION['REMOTE_ADDR']) || isset($_SESSION['HTTP_USER_AGENT'])) {
             if ($_SESSION['REMOTE_ADDR'] !== $_SERVER['REMOTE_ADDR'] || $_SESSION['HTTP_USER_AGENT'] !== $_SERVER['HTTP_USER_AGENT']) {
                 $this->destroy();
             }
        }
        //FIXME Вероятно, стоит подключаться к базе лишь в том случае, если в сессии у нас пусто
        $sql = "SELECT user_id, user_name, user_login, user_pass, user_mail,
                    DATE_FORMAT(user_created,'%d.%m.%Y') as user_created, user_active,
                    user_currency_default, user_currency_list
                FROM users
                WHERE user_login  = ?
                    AND user_pass = ?
                    AND user_new  = 0";

        $this->props = $this->db->selectRow($sql, $login, $pass);
        if (count($this->props) == 0) {
            trigger_error('Не верный логин или пароль! ' . $login . ' ' . $pass , E_USER_WARNING);
            return false;
        } elseif ($this->props['user_active'] == 0) {
            trigger_error('Ваш профиль был заблокирован!', E_USER_WARNING);
            return false;
        }

        if ($this->init($this->getId())) {
            $_SESSION['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
            return $this->save();
        } else {
            trigger_error("Не верно введён логин или пароль", E_USER_WARNING);
            return false;
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
     * @return bool Если без сбоев, то true, иначе - false
     */
    public function init($id)
    {
        if ($this->initUserCategory() && $this->initUserAccounts() && $this->initUserCurrency()) {
            return true;
        } else {
            return false;
        }
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
            $this->user_currency = array();
        }

        return true;
    }

    /**
     * Инициализирует пользовательские категории
     * @param string $id хэш-MD5 ид пользователя
     * @return array mixed
     */
    public function initUserCategory ()
    {
        $sql = "SELECT cat_id, cat_name, cat_parent, cat_active FROM category
            WHERE user_id = ? AND cat_active = '1' ORDER BY cat_name;";
        $this->user_category = $this->db->select($sql, $this->getId());
        return true;
    }

    /**
     * Получает список валют
     * @return array mixed
     */
    public function initUserCurrency ()
    {
        if (isset($this->props['user_currency_list'])) {
            if (!is_array($this->props['user_currency_list'])) {
                trigger_error('Ошибка десериализации валют пользователя', E_USER_NOTICE);
                $this->props['user_currency_list'] = array();
            }
            return true;
        } else {
            $this->props['user_currency_list'] = array();
        }
        return true;
    }

    /**
     * Возвращает счета пользователя
     * return array mixed
	 * FIXME убрать этот метод, он для старых счетов нужен был
     */
    public function initUserAccount ()
    {
        if (IS_DEMO) {
            $sql = "SELECT ROUND(SUM(m.`money`),2) AS `sum`,
                b.`bill_id` AS `id`,
                b.`bill_name` AS `name`,
                b.`bill_type` AS `type`,
                b.`bill_currency` AS `currency`,
                c.`cur_name` AS `currency_name`
                FROM `bill` b
                    LEFT JOIN `money` m
                        ON m.`bill_id` = b.`bill_id` and m.user_id = ?
                    LEFT JOIN `currency` c
                        ON c.`cur_id` = b.`bill_currency`
                WHERE b.`user_id` = ?
                GROUP BY b.`bill_id`, b.`bill_type`";
        }else{
            $sql = "SELECT ROUND(SUM(m.money),2) AS sum,
                    b.bill_id AS id,
                    b.bill_name AS name,
                    b.bill_type AS type,
                    b.bill_currency AS currency,
                    c.cur_name AS currency_name
                FROM bill b
                    LEFT JOIN money m
                        ON m.bill_id = b.bill_id
                    LEFT JOIN currency c
                        ON c.cur_id = b.bill_currency
                WHERE b.user_id = ?
                GROUP BY b.bill_id, b.bill_type ORDER BY b.bill_name;";
        }
        return $this->user_account = $this->db->selectRow($sql, $this->getId(), $this->getId());
    }
	
	/**
     * Возвращает счета пользователя
     * return array mixed
     */
	public function initUserAccounts($user_id)
	{
	    /*$sql = "SELECT a.*, 
		        act.account_type_name, 
				afv.string_value
		    FROM accounts a
		    LEFT JOIN account_types act 
			    ON act.account_type_id = a.account_type_id
			LEFT JOIN account_fields af
			    ON af.field_descriptionsfield_description_id = 1 
				AND af.account_typesaccount_type_id = act.account_type_id
			LEFT JOIN account_field_values afv
			    ON afv.account_fieldsaccount_field_id = af.account_field_id
			WHERE a.user_id = ?
			ORDER BY act.account_type_id";*/
			$sql = "SELECT a.*,act.* FROM accounts a 
			LEFT JOIN account_types act 
			    ON act.account_type_id = a.account_type_id
			WHERE user_id= ? ";
		return $this->user_account = $this->db->select($sql, $this->getId(), $this->getId());		
	}

    /**
     * Возвращает массив с профилем пользователя, с полями : ид, имя, логин, почта
     * @param $id int
     * @return array mixed
     */
    function getProfile($id)
    {
        $sql = "SELECT `user_id`, `user_name`, `user_login`, `user_mail` FROM `users` WHERE `user_id` = ? ;";
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
                        WHERE user_id = ? AND user_pass = ? LIMIT 1;";
            $this->db->query($sql, $user_name, $user_mail, MD5($new_passwd), $this->getId(), MD5($user_pass));
            return $this->initUser($user_login, $new_passwd);
        }else{
            $sql = "UPDATE users SET user_name = ?, user_mail = ?
                        WHERE user_id = ? AND user_pass = ? LIMIT 1;";
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
     *      user_id string ??? //FIXME перейти на INT
     *      user_name string
     *      user_login string
     *      user_pass string //WTF???
     *      user_mail string
     *      user_created date (%d.%m.%Y)
     *      user_active int 0 - аккаунт неактивен
     * @return mixed
     */
    function getUserProps($prop)
    {
        if (isset($this->props['$prop'])) {
            return $this->props['$prop'];
        }
    }
/**
 * @deprecated Всякий хлам будет снизу
 */

    /**
     * @deprecated ????
     * @param $id
     * @return bool
     */
    public function restoreCategory($id)
    {
        $sql = "SELECT cat_id, cat_parent from category WHERE user_id = ? AND cat_id = ?";
        $row = $this->db->selectRow($sql, $this->getId(), $id);
        if ($row['cat_parent'] > 0) {
            $id = $row['cat_parent'];
        }

        $sql = "UPDATE category SET cat_active = '1' WHERE user_id = ? AND (cat_id = ? OR cat_parent = ?)";
        $this->db->query($sql, $this->getId(), $id, $id);
        $this->initUserCategory($this->getId());
        $this->save();
        return true;
    }

    /**
     * @deprecated ???
     * @param $user_id
     * @return unknown_type
     */
    public function getDemoOperations($user_id)
    {
        $lnk = mysql_connect('localhost', 'homemone', 'lw0Hraec') or die ('Not connected : ' . mysql_error());
        mysql_select_db('homemoney', $lnk) or die ('Can\'t use foo : ' . mysql_error());
        mysql_query("SET NAMES utf8;");

        if (IS_DEMO) {
            $q = "select * from money where user_id='9e08f78840c8fefd7882ffa03813e6d1'";
            $res = mysql_query($q);
            while($row = mysql_fetch_array($res))
            {
                $m_row[] = $row;
            }
            $m_cnt = count($m_row);

            /*$q = "select * from budget where user_id='9e08f78840c8fefd7882ffa03813e6d1'";
             $res = mysql_query($q);
             while($row = mysql_fetch_array($res))
             {
             $b_row[] = $row;
             }
             $b_cnt = count($b_row);*/

            $q = "select * from periodic where user_id='9e08f78840c8fefd7882ffa03813e6d1'";
            $res = mysql_query($q);
            while($row = mysql_fetch_array($res))
            {
                $p_row[] = $row;
            }
            $p_cnt = count($p_row);
        }

        $sql = "select * from category where user_id='9e08f78840c8fefd7882ffa03813e6d1' and cat_active=1 order by cat_parent, cat_name";
        $result = mysql_query($sql);
        $i = 0;
        while ($row = mysql_fetch_array($result))
        {
            $rows[$i]['cat_name']	= $row['cat_name'];
            $rows[$i]['cat_parent'] = $row['cat_parent'];
            $rows[$i]['cat_id'] = $row['cat_id'];
            $i++;
        }
        $cnt = count($rows);

        for ($i=0; $i<$cnt; $i++)
        {
            if ($rows[$i]['cat_parent'] == 0)
            {
                $sql = "INSERT INTO `category` VALUES ('', '0', '".$user_id."', '".$rows[$i]['cat_name']."', '1')";
                $this->db->sql_query($sql);
                $next_id = $this->db->sql_nextid();
                for ($j=0; $j<$cnt; $j++)
                {
                    if ($rows[$j]['cat_parent'] == $rows[$i]['cat_id'])
                    {
                        $sql = "INSERT INTO `category` VALUES ('', '".$next_id."', '".$user_id."', '".$rows[$j]['cat_name']."', '1')";
                        $this->db->sql_query($sql);
                        if (IS_DEMO)
                        {
                            $next_cat_id = $this->db->sql_nextid();

                            for ($k=0; $k<$m_cnt; $k++)
                            {
                                //$m_row[$k]['new_cat_id'] = 0;
                                if ($rows[$j]['cat_id'] == $m_row[$k]['cat_id'])
                                {
                                    $m_row[$k]['new_cat_id'] = $next_cat_id;
                                }

                                if ($m_row[$k]['cat_id'] == 0)
                                {
                                    $m_row[$k]['new_cat_id'] = 0;
                                }

                                if ($m_row[$k]['cat_id'] == "-1")
                                {
                                    $m_row[$k]['new_cat_id'] = "-1";
                                }
                            }

                            for ($p=0; $p<$p_cnt; $p++)
                            {
                                if ($rows[$j]['cat_id'] == $p_row[$p]['cat_id'])
                                {
                                    $p_row[$p]['new_cat_id'] = $next_cat_id;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (IS_DEMO)
        {
            $sql = "select * from bill where user_id='9e08f78840c8fefd7882ffa03813e6d1'";
            $result = mysql_query($sql);
            $i = 0;
            while ($row = mysql_fetch_array($result))
            {
                $rows[$i]['bill_name'] = $row['bill_name'];
                $rows[$i]['bill_type'] = $row['bill_type'];
                $rows[$i]['bill_id'] = $row['bill_id'];
                $rows[$i]['bill_currency'] = $row['bill_currency'];
                $i++;
            }

            $cnt = count($rows);

            for ($i=0; $i<$cnt; $i++)
            {
                if (!empty($rows[$i]['bill_name']))
                {
                    $sql = "INSERT INTO `bill` VALUES ('".$rows[$i]['bill_id']."', '".$rows[$i]['bill_name']."', '".$user_id."', '".$rows[$i]['bill_type']."', '".$rows[$i]['bill_currency']."')";
                    $this->db->sql_query($sql);
                    //$next_id = $this->db->sql_nextid();
                    $next_id = $rows[$i]['bill_id'];

                    for ($k=0; $k<$m_cnt; $k++)
                    {
                        if ($m_row[$k]['bill_id'] == $rows[$i]['bill_id'] && $m_row[$k]['cat_id'] == 0)
                        {
                            $sql = "INSERT INTO `money` VALUES ('', '".$user_id."', '".$m_row[$k]['money']."', '".$m_row[$k]['date']."',
																	'".$m_row[$k]['new_cat_id']."', '".$next_id."', '".$m_row[$k]['drain']."',
																	'".$m_row[$k]['comment']."','".$m_row[$k]['transfer']."','".$m_row[$k]['tr_id']."',
																	'".$m_row[$k]['imp_date']."', '".$m_row[$k]['imp_id']."')";
                            $this->db->sql_query($sql);
                        }
                    }
                }

                for ($k=0; $k<$m_cnt; $k++)
                {
                    if ($m_row[$k]['bill_id'] == $rows[$i]['bill_id'] && $m_row[$k]['cat_id'] != 0)
                    {
                        $sql = "INSERT INTO `money` VALUES ('', '".$user_id."', '".$m_row[$k]['money']."', '".$m_row[$k]['date']."',
																'".$m_row[$k]['new_cat_id']."', '".$next_id."', '".$m_row[$k]['drain']."',
																'".$m_row[$k]['comment']."','".$m_row[$k]['transfer']."','".$m_row[$k]['tr_id']."',
																'".$m_row[$k]['imp_date']."', '".$m_row[$k]['imp_id']."')";
                        $this->db->sql_query($sql);
                    }
                }

                for ($p=0; $p<$p_cnt; $p++)
                {
                    if ($p_row[$p]['bill_id'] == $rows[$i]['bill_id'])
                    {
                        $sql = "INSERT INTO `periodic` VALUES ('', '".$user_id."', '".$next_id."', '".$p_row[$p]['period']."',
																   '".$p_row[$p]['date_from']."', '".$p_row[$p]['povtor']."',
																   '".$p_row[$p]['insert']."', '".$p_row[$p]['remind']."',
																   '".$p_row[$p]['remind_num']."', '".$p_row[$p]['drain']."',
																   '".$p_row[$p]['money']."', '".$p_row[$p]['new_cat_id']."',
																   '".$p_row[$p]['comment']."', '".$p_row[$p]['povtor_num']."')";
                        $this->db->sql_query($sql);
                    }
                }
            }

        }
    }

    /**
     * Возвращает категории
     * @deprecated ???
     * @param $user_id int
     * @return bool
     */
    function getCategory($user_id)
    {
//        //FIXME WTF??? // Данные пользователя "ДЕМО"
//        $sql = "SELECT * FROM category WHERE user_id='9e08f78840c8fefd7882ffa03813e6d1' AND cat_active = 1 ORDER BY cat_parent, cat_name;";
//        $result = $this->db->select($sql);
//        foreach ($result as $i => $row) {
//            $rows[$i]['cat_name']   = $row['cat_name'];
//            $rows[$i]['cat_parent'] = $row['cat_parent'];
//            $rows[$i]['cat_id']     = $row['cat_id'];
//        }

        $cnt = count($rows); //@FIXME WTF??? Перебор по все колонкам?
        for ($i=0; $i<$cnt; $i++) {
            if ($rows[$i]['cat_parent'] == 0) {
                $sql = "INSERT INTO `category` VALUES ('', '0', ?, ?, '1')";
                $this->db->query($sql, $user_id, $rows[$i]['cat_name']);
                //$next_id = $this->db->sql_nextid();
                for ($j=0; $j<$cnt; $j++) {
                    if ($rows[$j]['cat_parent'] == $rows[$i]['cat_id']) {
                        $sql = "INSERT INTO `category` VALUES ('', ? , ?, ?, '1')";
                        $this->db->query($sql, $next_id, $user_id, $rows[$j]['cat_name']);
                        if (IS_DEMO) {
                            $next_cat_id = $this->db->sql_nextid();

                            for ($k=0; $k<$m_cnt; $k++) {
                                if ($rows[$j]['cat_id'] == $m_row[$k]['cat_id']) {
                                    $m_row[$k]['new_cat_id'] = $next_cat_id;
                                }

                                if ($m_row[$k]['cat_id'] == 0) {
                                    $m_row[$k]['new_cat_id'] = 0;
                                }

                                if ($m_row[$k]['cat_id'] == "-1") {
                                    $m_row[$k]['new_cat_id'] = "-1";
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->initUserCategory($user_id);
        $this->save();

        return true;
    }


    /**
     * @deprecated ???
     * @return unknown_type
     */
    function demoNewUser()
    {
        $login = substr(md5(microtime().uniqid()), 0, 5);

        $this->db->sql_query("select user_id from users where user_login = '".$login."'");
        if ($this->db->sql_numrows() == 1)
        {
            $this->demoNewUser();
        }

        return $login;
    }
}