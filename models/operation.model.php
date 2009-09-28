<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления операциями
 * @category operation
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Operation_Model {
    /**
     * Ссылка на экземпляр класса базы данных
     * @var DbSimple_Mysql
     */
    private $db = null;

    /**
     * Ссылка на экземпляр класса пользователя
     * @var User
     */
    private $user = null;

    /**
     *
     * @var 
     */
    private $account_money = 0;

    /**
     *
     * @var type
     */
    private $user_money = Array();

    /**
     * 
     * @var type
     */
    private $total_sum = 0;

    /**
     * Массив со списком ошибок, появляющимися при добавлении, удалении или редактировании (если есть)
     * @var array mixed
     */
    public $errorData = array();

    /**
     * Конструктор
     * @return bool
     */
    function __construct()
    {
        $this->db = Core::getInstance()->db;
        $this->user = Core::getInstance()->user;
        $this->load();
        return true;
    }

    /**
     * Загрузка данных из сессии
     * @return void
     */
    function load()
    {
        if (isset($_SESSION['user_money'])) {
            $this->user_money = $_SESSION['user_money'];
        } else {
            $this->user_money = array();
        }
        $this->account_money = (int)@$_SESSION['account_money'];
        $this->total_sum = (int)@$_SESSION['total_sum'];
    }

    /**
     * Сохранение данных в сессии
     * @return void
     */
    function save()
    {
        $_SESSION['user_money']    = $this->user_money;
        $_SESSION['account_money'] = $this->account_money;
        $_SESSION['total_sum']     = $this->total_sum;
    }
    /**
     * Проверяет валидность введённых данных
     * @param <mixed> $params
     * @return <bool>
     */
    function checkData($params = '')
    {
        $valid = array();
        $this->errorData = array();

        // Проверяем ID
        if (in_array('id', $params) or count($params) == 0) {
            $valid['id'] = (int)@$_POST['id'];
            if ($valid['id'] === 0) {
                $this->errorData['id'][] = 'Не указан id события';
            }
        }

        // Проверяем тип операции
        if (in_array('type', $params) or count($params) == 0) {
            $valid['type'] = (int)@$_POST['type'];
        }

        // Проверяем счёт
        if (in_array('account', $params) or count($params) == 0) {
            $valid['account'] = (int)@$_POST['account'];
            if ($valid['account'] === 0) {
                $this->errorData['account'][] = 'Не выбран счёт';
            }
        }

        // Проверяем сумму
        if (in_array('amount', $params) or count($params) == 0) {
            $valid['amount'] = (float)@$_POST['amount'];
            if (empty ($valid['amount'])) {
                $this->errorData['amount'][] = 'Сумма не должна быть равной нулю.';
            }
        }

        // Проверяем категорию
        if (in_array('category', $params) or count($params) == 0) {
            $valid['category'] = (int)@$_POST['category'];
            if (empty ($valid['category'])) {
                $this->errorData['category'][] = 'Нужно указать категорию';
            }
        }

        // Проверяем дату
        if (in_array('date', $params) or count($params) == 0) {
            $valid['date'] = trim(formatRussianDate2MysqlDate(@$_POST['date']));
            if (empty ($valid['date'])) {
                $this->errorData['date'][] = 'Не верно указана дата';
            }
        }

        $valid['comment'] = trim(htmlspecialchars(@$_POST['comment']));

        // Проверяем теги
        if (!empty ($_POST['tags'])) {
            $tags = explode(',', trim(@$_POST['tags']));
            foreach ($tags as $tag) {
                if (!empty ($tag)) {
                    if (!in_array(trim($tag), $valid['tags'])) {
                        $valid['tags'][] = trim($tag);
                    }
                }
            }
        } else {
            $valid['tags'] = null;
        }


        // Проверяем тип операции
        // - Перевод со счёта на счёт
        if ($valid['type'] == 2) {
            $valid['convert'] = round($valid['amount'] /  (float)$_POST['currency'], 2);
            $valid['toAccount'] = (int)@$_POST['toAccount'];
        // - Финансовая цель
        } elseif($valid['type'] == 4) {
            $valid['target'] = (int)@$_POST['target'];
            if (isset ($_POST['close'])) {
                $valid['close'] = 1;
            } else {
                $valid['close'] = 0;
            }
        }


        //currency  toAccount
        return $valid;
    }

    /**
	 * Регистрирует новую транзакцию
	 * @param float  $money      Сумма транзакции
	 * @param string $date       Дата транзакции в формате Y.m.d
	 * @param int    $drain      Доход или расход. Устаревшее, но на всякий случай указывать надо 0 - расход, 1 - доход
	 * @param string $comment    Комментарий транзакции
	 * @param int    $account_id Ид счета
     * 
	 * @return int $id Если
	 */
	function add($money = 0, $date = '', $category = 0, $drain = 0, $comment = '', $account = 0, $tags = null)
    {
        // Если есть теги, то добавляем и их тоже
        if ($tags) {
            $sql = "INSERT INTO `operation` (`user_id`, `money`, `date`, `cat_id`, `account_id`,
                `drain`, `comment`, `tags`, `dt_create`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $this->db->query($sql, $this->user->getId(), $money, $date, $category, $account, $drain,
                $comment, implode(', ', $tags));
            $last_id = mysql_insert_id();
            $sql = "";
            foreach ($tags as $tag) {
                if (!empty($sql)) { $sql .= ','; }
                $sql .= "(". $this->user->getId() . "," . (int)$last_id . ",'" . htmlspecialchars(addslashes($tag)) . "')";
            }
            $this->db->query("INSERT INTO `tags` (`user_id`, `oper_id`, `name`) VALUES " . $sql);
        } else {
            $sql = "INSERT INTO `operation` (`user_id`, `money`, `date`, `cat_id`, `account_id`,
                `drain`, `comment`, `dt_create`) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $this->db->query($sql, $this->user->getId(), $money, $date, $category, $account, $drain, $comment);
            $last_id = mysql_insert_id();
        }
        // Обновляем данные о счетах пользователя
        Core::getInstance()->user->initUserAccounts();
        //$this->selectMoney($user_id);
        $this->save();
        return $last_id;
	}

    /**
     * Добавляет трансфер с одного на другой счёт
     * @param $money        float     Деньги
     * @param $convert      float     Конвертированные в нужную валюту деньги
     * @param $date         string    Дата, когда совершаем трансфер
     * @param $from_account int       Со счёта
     * @param $to_account   int       На счёт
     * @param $comment      string    Комментарий
     * @param $tags         array     Тег
     * @return bool
     */
	function addTransfer($money, $convert, $date, $from_account, $to_account, $comment, $tags)
	{
        $drain_money = $money * -1;
                // tr_id. было drain
		$sql = "INSERT INTO operation
                    (user_id, money, date, cat_id, account_id, tr_id, comment, transfer)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, $this->user->getId(), $drain_money, $date, -1, $from_account, 1,
            $comment, $to_account);

        $last_id = mysql_insert_id();
            $sql = "INSERT INTO operation
                    (user_id, money, date, cat_id, account_id, tr_id, comment, transfer)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, $this->user->getId(), $money, $date, -1, $to_account, 1,
            $comment, $from_account);

        /*$last_id = mysql_insert_id();
        $sql = "INSERT INTO operation
                    (user_id, money, date, cat_id, account_id, drain, comment, transfer, tr_id)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, $this->user->getId(), $convert, $date, -1, $to_account, 0, $comment,
            $from_account, $last_id);

        $last_id = mysql_insert_id();
        $sql = "INSERT INTO operation
                    (user_id, money, date, cat_id, account_id, drain, comment, transfer, tr_id)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, $this->user->getId(), $convert, $date, -1, $from_account, 0, $comment,
            $to_account, $last_id);*/

        $this->user->initUserAccounts();
        $this->user->save();
        return $last_id;
	}

    /**
	 * Редактирует транзакцию
     * @param int    $id         Ид транзакции
	 * @param float  $money      Сумма транзакции
	 * @param string $date       Дата транзакции в формате Y.m.d
	 * @param int    $drain      Доход или расход. Устаревшее, но на всякий случай указывать надо 0 - расход, 1 - доход
	 * @param string $comment    Комментарий транзакции
	 * @param int    $account_id Ид счета
     *
	 * @return bool true - Регистрация прошла успешно
	 */
	function edit($id=0, $money = 0, $date = '', $category = 0, $drain = 0, $comment = '', $account = 0, $tags = null)
	{
        // Если есть теги, то добавляем и их тоже
        if ($tags) {
            $this->db->query('DELETE FROM tags WHERE oper_id=? AND user_id=?',$id, $this->user->getId());

            $sql = "UPDATE operation SET money=?, date=?, cat_id=?, account_id=?, drain=?, comment=?, tags=?
                WHERE user_id = ? AND id = ?";
            $this->db->query($sql, $money, $date, $category, $account, $drain, $comment, implode(', ', $tags), $this->user->getId(), $id);

            $sql = "";
            foreach ($tags as $tag) {
                if (!empty($sql)) { $sql .= ','; }
                $sql .= "(". $this->user->getId() . "," . $id . ",'" . htmlspecialchars(addslashes($tag)) . "')";
            }
            $this->db->query("INSERT INTO `tags` (`user_id`, `oper_id`, `name`) VALUES " . $sql);
        } else {
            $sql = "UPDATE operation SET money=?, date=?, cat_id=?, account_id=?, drain=?, comment=?
                WHERE user_id = ? AND id = ?";
            $this->db->query($sql, $money, $date, $category, $account, $drain, $comment, $this->user->getId(), $id);
        }
        // Обновляем данные о счетах пользователя
        Core::getInstance()->user->initUserAccounts();
        //$this->selectMoney($user_id);
        $this->save();
        return '[]';
	}

    /**
     * Удаляет указанную операцию
     * @param int id
     * @return bool
     */
    function deleteOperation($id = 0)
    {
        if ($this->db->query("DELETE FROM operation WHERE id= ? AND user_id= ?",$id, Core::getInstance()->user->getId())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получает сумму всех счетов пользователя
     * @param int $account_id Ид счёта
     * @param string $period Период
     * @return 
     */
	function selectMoney($id, $period = '')
	{
		if (!empty($period)) {
			if ($period == "today") {
				$order = "AND `date` = '".date("Y.m.d")."'";
			}
			if (html($_GET['order']) == "month") {
				$order = "AND (m.`date` BETWEEN '".date("Y.m.01")."' AND '".date("Y.m.31")."' or m.`date` = '0000.00.00')";
			}
			if (html($_GET['order']) == "week") {
				$begin_week = (date('d')+1) - date('w');
				$order = "AND (m.`date` BETWEEN '".date("Y.m.$begin_week")."' AND '".date("Y.m.d")."' or m.`date` = '0000.00.00')";
			}
		}else{
			$limit = "LIMIT 0,30";
		}
        $sql = "SELECT m.`id`, m.`user_id`, m.`money`, DATE_FORMAT(m.date,'%d.%m.%Y') as date,
                       m.`cat_id`, m.`bill_id`, c.`cat_name`, b.`bill_name`, m.`drain`, m.`comment`,
                       b.`bill_currency`, cu.`cur_name`, m.`transfer`, m.`tr_id`,
                       bt.`bill_name` as `cat_transfer`
                    FROM `money` m
                    LEFT JOIN `category` c on c.`cat_id` = m.`cat_id`
                    LEFT JOIN `bill` b on b.`bill_id` = m.`bill_id`
                    LEFT JOIN `bill` bt on bt.`bill_id` = m.`transfer`
                    LEFT JOIN `currency` cu on cu.`cur_id` = b.`bill_currency`
                        WHERE m.`bill_id` = '".$id."'
                               AND m.`user_id` = '".$this->user_id."'
                               ".$order."
                        ORDER BY m.`date` DESC, m.`id` DESC ".$limit;

        $this->user_money = $row;
        $this->account_money = $id;
        $this->getTotalSum($id);
        $this->save();
	}

    /**
     * Получение списка транзакций
     * @param date $dateFrom
     * @param date $dateTo
     * @param int $currentCategory
     * @param int $currentAccount
     * @return array mixed
     */
    function getOperationList($dateFrom, $dateTo, $currentCategory, $currentAccount)
    {
        if (IS_DEMO) { //@TODO DEMO
        }else{
/*
    UNION
        SELECT t.id, t.user_id, t.money, DATE_FORMAT(t.date,'%d.%m.%Y'), tt.id,
        t.bill_id, (IF(LENGTH(tt.title)>30,CONCAT(LEFT(tt.title, 30),'..'),tt.title)), cy.cat_parent, bl.bill_name , 0, t.comment, bl.bill_currency, '', '', '', '', 1 AS virt
        FROM target_bill t
            LEFT JOIN target tt ON t.target_id=tt.id
            LEFT JOIN bill bl ON t.bill_id=bl.bill_id
            LEFT JOIN category cy ON cy.cat_id = tt.category_id
        WHERE t.user_id= ? {$order1}
    ORDER BY `date` DESC, `id` DESC";
 */

            $category = Core::getInstance()->user->getUserCategory();
            $cat_in = '';
            foreach ($category as $var) {
                if ($var['cat_parent'] == $currentCategory) {
                    if ($cat_in) $cat_in .= ',';
                    $cat_in .= $var['cat_id'];
                }
            }

            $sql = "SELECT o.id, o.user_id, o.money, DATE_FORMAT(o.date,'%d.%m.%Y') as `date`, ".
            "o.cat_id, o.account_id, o.drain, o.comment, o.transfer, o.tr_id, 0 AS virt, o.tags ".
            "FROM operation o ".
            "WHERE o.account_id = ? ".
                "AND o.user_id = ? ".
                "AND (o.`date` BETWEEN ? AND ?) ";
                if (!empty($currentCategory)) {
                    if ($cat[$currentCategory]['cat_parent'] == 0) {
                        $sql .= " AND (o.cat_id IN ({$cat_in})) ";
                    } else {
                        $sql .= " AND (o.cat_id = '{$currentCategory}') ";
                    }
                }
            $sql .= " UNION ".
            " SELECT t.id, t.user_id, t.money, DATE_FORMAT(t.date,'%d.%m.%Y'), ".
            " tt.category_id, tt.target_account_id, 1, t.comment, '', '', 1 AS virt, t.tags ".
            " FROM target_bill t ".
            " LEFT JOIN target tt ON t.target_id=tt.id ".
            " WHERE t.user_id = ? ".
                " AND (t.`date` BETWEEN ? AND ?) ";
                if (!empty($currentCategory)) {
                    if ($cat[$currentCategory]['cat_parent'] == 0) {
                        $sql .= " AND (tt.category_id IN ({$cat_in})) ";
                    } else {
                        $sql .= " AND (tt.category_id = '{$currentCategory}') ";
                    }
                }
            $sql .= " ORDER BY `date` DESC, id ";

            $accounts = Core::getInstance()->user->getUserAccounts();
            $operations = $this->db->select($sql, $currentAccount, $this->user->getId(), $dateFrom, 
                $dateTo, $this->user->getId(), $dateFrom, $dateTo);
            // Добавляем данные, которых не хватает
            foreach ($operations as $key => $val) {
                $val['cat_name']            = $category[$val['cat_id']]['cat_name'];
                $val['cat_parent']          = $category[$val['cat_id']]['cat_parent'];
                $val['account_name']        = $accounts[$val['account_id']]['account_name'];
                $val['account_currency_id'] = $accounts[$val['account_id']]['account_currency_id'];
                $val['cat_transfer']        = $accounts[$val['account_id']]['account_currency_id'];
                //$val['cur_name'] = $accounts[$val['cur_id']]['cur_name'];
                $operations[$key] = $val;
            }
            //bt.account_name as cat_transfer, //@TODO
            return $operations;
        }
    }

    /**
     * Возвращает все деньги пользователя по определённому счёту
     * @param int|array|0 $account_id Ид счёта.
     * Если $account_id = 0, то будем считать по всем счетам пользователя
     * Если (int)$account_id > 0 значит будем считать только по этому счёту пользователя
     * Если $account_id = array(123, 234, 345) значит будем считать по всем счетам пользователя указанным в массиве
     * @param int $drain = 1 - расход, 0 - доход, null - по расходу и доходу
     * @return float
     */
    function getTotalSum($account_id = 0, $drain = null)
    {
        if (!is_null($drain)) {
            $dr = " AND drain = '" . (int)$drain . "'";
        } else {
            $dr = '';
        }
        if (is_array($account_id) && count($account_id) > 0) {
            $sql = "SELECT SUM(money) as sum FROM operation WHERE user_id = ? AND account_id IN (?a) {$dr}";
            $this->total_sum = $this->db->selectCell($sql, $this->user->getId(), $account_id);
        } elseif ((int)$account_id > 0 ) {
            $sql = "SELECT SUM(money) as sum FROM operation WHERE user_id = ? AND account_id = ?d  {$dr}";
            $this->total_sum = $this->db->selectCell($sql, $this->user->getId(), $account_id);
        } elseif((int)$account_id == 0) {
            $sql = "SELECT SUM(money) as sum FROM operation WHERE user_id = ?  {$dr}";
            $this->total_sum = $this->db->selectCell($sql, $this->user->getId());
        } else {
            trigger_error(E_USER_NOTICE, 'Ошибка получения всей суммы пользователя');
            return 0;
        }
        return $this->total_sum;
    }

    /**
     *
     */
    function getCurrency()
    {
        $SourceId = (int)$_POST['SourceId']; // Ид счёта на который нужно перевести
        $TargetId = (int)$_POST['TargetId']; // Ид текущего счёта
        $aTarget = $aSource = array();
        $curr = Core::getInstance()->currency;
        foreach (Core::getInstance()->user->getUserAccounts() as $val) {
            if ($val['account_id'] == $SourceId) {
                $aTarget[$val['account_currency_id']] = $curr[$val['account_currency_id']]['name'];
            }
            if ($val['account_id'] == $TargetId) {
                $aSource[$val['account_currency_id']] = $curr[$val['account_currency_id']]['name'];
            }
        }

        if (key($aSource) != key($aTarget)) {
            // Если у нас простое сравнение с рублём
            if (key($aTarget) == 1 || key($aSource) == 1){
                $course = round($curr[key($aSource)]['value'] / $curr[key($aTarget)]['value'],4);
            } else {
                //@FIXME Придумать алгоритм для конвертации между различными валютами
                $course = 0;
            }
            return $course;
        }
    }
}