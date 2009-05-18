<?
/**
 * file: user.class.php
 * author: Roman Korostov
 * date: 23/01/07
 **/

class User
{
    var $props = Array();
    var $user_category = Array();
    var $user_account = Array();
    var $user_currency = Array();

    private $db;

    /**
     * Конструктор
     * @param DbSimple_Mysql $db
     * @return void
     */
    function __construct(DbSimple_Mysql $db)
    {
        $this->db = $db;
        $this->load();
    }

    /**
     *
     *
     */
    function initUser($login, $pass)
    {
        $sql = "
            SELECT `user_id`, `user_name`, `user_login`, `user_pass`, `user_mail`,
                DATE_FORMAT(user_created,'%d.%m.%Y') as user_created, `user_active` FROM `users`
            WHERE `user_login` = ?
                AND `user_pass` = ?
                AND `user_new` = 0";
        $row = $this->db->selectRow($sql,  str_replace("\\'", "''", $login), $pass);
        //print_r($this->db);
        if ($row['user_active'] == 0) {
            trigger_error('Ваш профиль был заблокирован!', E_USER_WARNING);
            return false;
        }

        $this->props = $row;

        if ( $this->init($row['user_id']) ) {
            $this->save();
            return true;
        }
        trigger_error("Не верно введён логин или пароль", E_USER_WARNING);
    }

    function save () {
        $_SESSION['user'] = $this->props;
        $_SESSION['user_category'] = $this->user_category;
        $_SESSION['user_account'] = $this->user_account;
        $_SESSION['user_currency'] = $this->user_currency;
    }

    function init($id) {

        if ($this->initUserCategory($id) &&
        $this->initUserAccount($id) &&
        $this->initUserCurrency($id))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Загружает ранее объявленные параметры пользователя из сессии
     * @return void
     */
    function load()
    {
        $this->props = $_SESSION['user'];
        $this->user_category = $_SESSION['user_category'];
        $this->user_account = $_SESSION['user_account'];
        $this->user_currency = $_SESSION['user_currency'];
    }

    /**
     * Инициализирует пользовательские категории
     * @param string $id хэш-MD5 ид пользователя
     * @return array mixed
     */
    function initUserCategory ($id)
    {
        $id = (int)$id;
        $sql = "SELECT `cat_id`, `cat_name`, `cat_parent`, `cat_active` FROM `category`
                    WHERE `user_id` = ? AND `cat_active` = '1' ORDER BY `cat_name`;";
        $this->user_category = $this->db->selectRow($sql, $id);
        return true;
    }

    /**
     * Получает список валют
     * @return array mixed
     */
    function initUserCurrency ()
    {
        $sql = "SELECT `cur_id`, `cur_name` from `currency` ORDER BY `cur_id` ";
        $this->user_currency = $this->db->select($sql, $id);
        return true;
    }

    /**
     *
     *
     */
    function initUserAccount ($id)
    {
        $id = (int)$id;
        if (IS_DEMO == 'demo') {
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
            $sql = "SELECT ROUND(SUM(m.`money`),2) AS `sum`,
                    b.`bill_id` AS `id`,
                    b.`bill_name` AS `name`,
                    b.`bill_type` AS `type`,
                    b.`bill_currency` AS `currency`,
                    c.`cur_name` AS `currency_name`
                FROM `bill` b
                    LEFT JOIN `money` m
                        ON m.`bill_id` = b.`bill_id`
                    LEFT JOIN `currency` c
                        ON c.`cur_id` = b.`bill_currency`
                WHERE b.`user_id` = ?
                GROUP BY b.`bill_id`, b.`bill_type` ORDER BY b.`bill_name`";
        }
        $this->user_account = $this->db->selectRow($sql, $id);
        return true;
    }

    function getId()
    {
        if (isset($this->props['user_id'])) {
            return $this->props['user_id'];
        }
        return false;
    }

    function restoreCategory($id)
    {
        $sql = "SELECT `cat_id`, `cat_parent` from `category`
				WHERE `user_id` = '".$this->getId()."' AND `cat_id` = ".$id."";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в получении категории!', '', __LINE__, __FILE__, $sql);
        }else{
            $row = $this->db->sql_fetchrow($result);

            if ($row['cat_parent'] > 0)
            {
                $id = $row['cat_parent'];
            }
        }


        $sql = "UPDATE `category` SET
					`cat_active` = '1'
				WHERE `user_id` = '".$this->getId()."' and (`cat_id` = '".$id."' or `cat_parent` = '".$id."')
				";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в cохранении категории!', '', __LINE__, __FILE__, $sql);
        }
        else
        {
            $this->initUserCategory($this->getId());
            $this->save();

            return true;
        }
    }

    function getDemoOperations($user_id)
    {
        $lnk = mysql_connect('localhost', 'homemone', 'lw0Hraec') or die ('Not connected : ' . mysql_error());
        mysql_select_db('homemoney', $lnk) or die ('Can\'t use foo : ' . mysql_error());
        mysql_query("SET NAMES utf8;");

        if (IS_DEMO)
        {
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

    function getCategory($user_id)
    {
        $lnk = mysql_connect('localhost', 'homemone', 'lw0Hraec') or die ('Not connected : ' . mysql_error());
        mysql_select_db('homemoney', $lnk) or die ('Can\'t use foo : ' . mysql_error());
        mysql_query("SET NAMES utf8;");

        /*if (IS_DEMO)
         {
         $q = "select * from money where user_id='9e08f78840c8fefd7882ffa03813e6d1'";
         $res = mysql_query($q);
         while($row = mysql_fetch_array($res))
         {
         $m_row[] = $row;
         }
         $m_cnt = count($m_row);

         $q = "select * from budget where user_id='9e08f78840c8fefd7882ffa03813e6d1'";
         $res = mysql_query($q);
         while($row = mysql_fetch_array($res))
         {
         $b_row[] = $row;
         }
         $b_cnt = count($b_row);
         }*/

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
                        }
                    }
                }
            }
        }

        /*if (IS_DEMO)
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
         $sql = "INSERT INTO `bill` VALUES ('', '".$rows[$i]['bill_name']."', '".$user_id."', '".$rows[$i]['bill_type']."', '".$rows[$i]['bill_currency']."')";
         $this->db->sql_query($sql);
         $next_id = $this->db->sql_nextid();

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
         if ($m_row[$k]['bill_id'] == $rows[$i]['bill_id'])
         {
         $sql = "INSERT INTO `money` VALUES ('', '".$user_id."', '".$m_row[$k]['money']."', '".$m_row[$k]['date']."',
         '".$m_row[$k]['new_cat_id']."', '".$next_id."', '".$m_row[$k]['drain']."',
         '".$m_row[$k]['comment']."','".$m_row[$k]['transfer']."','".$m_row[$k]['tr_id']."',
         '".$m_row[$k]['imp_date']."', '".$m_row[$k]['imp_id']."')";
         $this->db->sql_query($sql);
         }
         }
         }

         }*/

        /*
         $sql = "INSERT INTO `category` VALUES ('', 0, '".$user_id."', 'Коммунальные услуги', '1')";
         $this->db->sql_query($sql);
         $sql = "INSERT INTO `category` VALUES ('', 0, '".$user_id."', 'Личные расходы', '1')";
         $this->db->sql_query($sql);
         $sql = "INSERT INTO `category` VALUES ('', 0, '".$user_id."', 'Обучение', '1')";
         $this->db->sql_query($sql);
         $sql = "INSERT INTO `category` VALUES ('', 0, '".$user_id."', 'Одежда', '1')"
         $this->db->sql_query($sql);
         $sql = "INSERT INTO `category` VALUES ('', 0, '".$user_id."', 'Отдых и развлечение', '1')";
         $this->db->sql_query($sql);
         $sql = "INSERT INTO `category` VALUES ('', 0, '".$user_id."', 'Продукты', '1')";
         $this->db->sql_query($sql);
         $sql = "INSERT INTO `category` VALUES ('', 0, '".$user_id."', 'Подарки', '1')";
         $this->db->sql_query($sql);
         $sql = "INSERT INTO `category` VALUES ('', 0, '".$user_id."', 'Работа', '1')"
         $this->db->sql_query($sql);
         $sql = "INSERT INTO `category` VALUES ('', 0, '".$user_id."', 'Семейные расходы', '1')";
         $this->db->sql_query($sql);
         $sql = "INSERT INTO `category` VALUES ('', 0, '".$user_id."', 'Телефон', '1')";
         $this->db->sql_query($sql);
         $sql = "INSERT INTO `category` VALUES ('', 0, '".$user_id."', 'Транспорт', '1')";
         $this->db->sql_query($sql);
         $sql = "INSERT INTO `category` VALUES ('', 0, '".$user_id."', 'Хозяйственные расходы', '1')";
         $this->db->sql_query($sql);
         */
        //pre($arr,true);

        /*for($i=0; $i<=count($arr); $i++)
         {
         $sql = $arr[$i];
         //echo $sql;
         $result = $this->db->sql_query($sql);
         }*/

        $this->initUserCategory($user_id);
        $this->save();

        return true;
    }

    /**
     * Возвращает количество активных пользователей
     * return int
     */
    function getCountusers ()
    {
        return $this->db->selectCell("SELECT count(user_id) FROM users WHERE user_active='1';");
    }

    /**
     * Возвращает количество всех денех
     * return int
     */
    function getAllTransaction ()
    {
        return $this->db->selectCell("SELECT count(money) FROM money;");
    }

    function getProfile($id)
    {
        $sql = "
				SELECT `user_id`, `user_name`, `user_login`, `user_mail` from `users` where `user_id` = '".$id."'
			   ";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка получния профиля!', '', __LINE__, __FILE__, $sql);
        }

        $row = $this->db->sql_fetchrow($result);
        return $row;
    }

    function updateProfile($new_passwd, $user_name, $user_mail, $user_login)
    {
        if (!empty($new_passwd))
        {
            $user_passwd = ", `user_pass` = '".$new_passwd."'";
        }else{
            $user_passwd = "";
        }
        $user_id = $this->getId();

        $sql = "UPDATE `users` SET
					`user_name` = '".$user_name."',
					`user_mail` = '".$user_mail."'
					".$user_passwd."
				WHERE `user_id` = '".$user_id."'
				";
        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в cохранении профиля!', '', __LINE__, __FILE__, $sql);
        }
        else
        {
            $sql2 = "
					SELECT `user_id`, `user_name`, `user_login`, `user_pass`, `user_mail`, `user_created`,
							`user_active` FROM `users`
					WHERE `user_id` = '".$user_id."' and `user_login` = '".$user_login."'
				   ";
            if ( $result2 = $this->db->sql_query($sql2) )
            {
                if( $row2 = $this->db->sql_fetchrow($result2) )
                {
                    $this->props = $row2;
                    $this->save();
                }
            }

            return true;
        }
    }

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
} //end class
?>