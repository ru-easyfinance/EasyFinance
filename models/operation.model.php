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
        // - Финансовая цель
        } elseif($valid['type'] == 4) {
            if (isset ($_POST['close'])) {
                $valid['close'] = 1;
            } else {
                $valid['close'] = 0;
            }
            // target
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
	 * @return bool true - Регистрация прошла успешно
	 */
	function add($money = 0, $date = '', $category = 0, $drain = 0, $comment = '', $account = 0, $tags = null)
	{
        
        // Если есть теги, то добавляем и их тоже
        if ($tags) {
            $sql = "INSERT INTO `operation` (`user_id`, `money`, `date`, `cat_id`, `account_id`, `drain`, `comment`, `tags`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $this->db->query($sql, $this->user->getId(), $money, $date, $category, $account, $drain, $comment, implode(', ', $tags));
            $last_id = mysql_insert_id();
            $sql = "INSERT INTO `tags` (`user_id`, `oper_id`, `name`) VALUES (?, ?, ?)";
            foreach ($tags as $tag) {
                $this->db->query($sql, $this->user->getId(), $last_id, $tag);
            }
        } else {
            $sql = "INSERT INTO `operation` (`user_id`, `money`, `date`, `cat_id`, `account_id`, `drain`, `comment`) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $this->db->query($sql, $this->user->getId(), $money, $date, $category, $account, $drain, $comment, $tags);
        }
        // Обновляем данные о счетах пользователя
        Core::getInstance()->user->initUserAccounts();
        //$this->selectMoney($user_id);
        $this->save();
        return '[]';
	}

    /**
     * Добавляет трансфер с одного на другой счёт
     * @param $money float Деньги
     * @param $convert Конвертированные в нужную валюту деньги
     * @param $date Дата, когда совершаем трансфер
     * @param $from_account Из счёта
     * @param $to_account В счёт
     * @param $comment Комментарий
     * @return bool
     */
	function addTransfer($money, $convert, $date, $from_account, $to_account, $comment)
	{
        $drain_money = $money * -1;

		$sql = "INSERT INTO operation
                    (user_id, money, date, cat_id, account_id, drain, comment, transfer)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, $this->user->getId(), $drain_money, $date, -1, $from_account, 1, $comment, $to_account);

        $sql = "INSERT INTO operation
                    (user_id, money, date, cat_id, account_id, drain, comment, transfer, tr_id)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, $this->user->getId(), $convert, $date, -1, $to_account, 0, $comment, $from_account, mysql_insert_id());
        $this->user->initUserAccount();
        $this->user->save();
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
            $sql = "SELECT o.id, o.user_id, o.money, DATE_FORMAT(o.date,'%d.%m.%Y') as `date`, ".
            "o.cat_id, o.account_id, o.drain, o.comment, o.transfer, o.tr_id, 0 AS virt, o.tags ".
            "FROM operation o ".
            "WHERE o.account_id = ? ".
                "AND o.user_id = ? ".
                "AND (o.`date` BETWEEN ? AND ?) ";
                if (!empty($currentCategory)) {
                    $sql .= " AND (o.cat_id = '{$currentCategory}') ";
                }
            $accounts = Core::getInstance()->user->getUserAccounts();
            $operations = $this->db->select($sql, $currentAccount, $this->user->getId(), $dateFrom, $dateTo);
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
     * @param string $bill_id Ид счёта
     * @return int
     */
    function getTotalSum($bill_id)
    {
        $sql = "SELECT SUM(money) as sum FROM money WHERE user_id = ? AND bill_id = ?";
        $this->total_sum = $this->db->selectCell($sql, $this->user->getId(), $bill_id);
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