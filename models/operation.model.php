<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления операциями
 * @category operation
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://easyfinance.ru/
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
                $valid['target'] = (int)@$_POST['target'];
                $valid['toAccount'] = (int)@$_POST['toAccount'];
                if (empty ($valid['target']))
                    if (empty ($valid['toAccount']))
                        $this->errorData['category'][] = 'Нужно указать категорию';
            }
        }

        // Проверяем дату
        if (in_array('date', $params) or count($params) == 0) {
            $valid['date'] = trim(formatRussianDate2MysqlDate(@$_POST['date']));
            if ( $valid['date'] == '0000-00-00' )
                $this->errorData['date'][] = 'Не верно указана дата';
            if (empty ($valid['date'])) {
                $this->errorData['date'][] = 'Не верно указана дата';
            }
        }

        $valid['comment'] = trim(htmlspecialchars(@$_POST['comment']));

        // Проверяем теги
        if (!empty ($_POST['tags'])) {
            $valid['tags'] = array();
            $tags = explode(',', trim(@$_POST['tags']));
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (!empty ($tag)) {
                    if (!in_array($tag, $valid['tags'])) {
                        $valid['tags'][] = trim($tag);
                    }
                }
            }
        } else {
            $valid['tags'] = null;
        }

        // Проверяем тип операцииe
        // - Перевод со счёта на счёт
        if ($valid['type'] == 2) {
            $valid['currency'] = (float)$_POST['currency'];
            if ((float)$_POST['currency'] != 0) {
                $valid['convert'] = round($valid['amount'] *  (float)$_POST['currency'], 2);
            } else {
                $valid['convert'] = 0;
            }
            $valid['toAccount'] = (int)@$_POST['toAccount'];
        // - Финансовая цель
        } elseif($valid['type'] == 4) {
            $valid['target'] = $_POST['target'];
            //if (isset ($_POST['close'])) {
            if (($_POST['close'])==1) {
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
        Core::getInstance()->user->save();

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
    function editTransfer($id=0, $money = 0, $convert = 0, $date = '', /*$category = 0, $drain = 0,*/ $account = 0, $toAccount=0, $comment = '', $tags = null){
        if ($tags) {
            $this->db->query('DELETE FROM tags WHERE oper_id=? AND user_id=?',$id, $this->user->getId());

            $sql = "UPDATE operation SET money=?, date=?, account_id=?, transfer=?, comment=?, tags=?
                WHERE user_id = ? AND id = ?";
            $this->db->query($sql, $money, $date, $account, $toAccount, $comment, implode(', ', $tags), $this->user->getId(), $id);

            $sql = "";
            foreach ($tags as $tag) {
                if (!empty($sql)) { $sql .= ','; }
                $sql .= "(". $this->user->getId() . "," . $id . ",'" . htmlspecialchars(addslashes($tag)) . "')";
            }
            $this->db->query("INSERT INTO `tags` (`user_id`, `oper_id`, `name`) VALUES " . $sql);
        } else {
            $sql = "UPDATE operation SET money=?, date=?, account_id=?, transfer=?, comment=?, tags=?, imp_id=?
                WHERE user_id = ? AND id = ?";
            $next = $this->db->query("SELECT id FROM operation WHERE tr_id=?", $id);
            if ($next){//если есть смежная запись, т.е. редактируем перевод
                $this->db->query($sql, -$money, $date, $account, $toAccount, $comment, implode(', ', $tags), NULL, $this->user->getId(), $id);//перевод с
                if ($convert)
                    $this->db->query($sql, $convert, $date, $toAccount, $account, $comment, implode(', ', $tags), $money, $this->user->getId(), $next[0]['id']);//перевод на
                else
                    $this->db->query($sql, $money, $date, $toAccount, $account, $comment, implode(', ', $tags), $money, $this->user->getId(), $next[0]['id']);//перевод на
            } else {// иначе делаем перевод из доходной/расходной операции
                $sql = "UPDATE operation SET money=?, date=?, account_id=?, transfer=?, comment=?, tags=?, imp_id=?, cat_id=0, tr_id=0
                WHERE user_id = ? AND id = ?";
                $this->db->query($sql, -$money, $date, $account, $toAccount, $comment, implode(', ', $tags), NULL, $this->user->getId(), $id);//перевод с
                $sql = "INSERT INTO operation
                    (user_id, money, date, cat_id, account_id, tr_id, comment, transfer, dt_create, imp_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
                $this->db->query($sql, $this->user->getId(), $money, $date, -1, $toAccount, $id,
                    $comment, $account, NULL);
            }
        }
        $this->save();
        return '[]';
    }

    /**
     * Добавляем перевод со счёта на счёт
     * @param type> $money
     * @param <type> $convert
     * @param <type> $date
     * @param <type> $from_account
     * @param <type> $to_account
     * @param <type> $comment
     * @param <type> $tags
     * @return <type>
     */
    function addTransfer($money, $convert, $curr, $date, $from_account, $to_account, $comment, $tags)
    {
        $cur1 = $this->db->query("SELECT account_currency_id AS cur FROM accounts WHERE account_id=?",$from_account);
        $cur2 = $this->db->query("SELECT account_currency_id AS cur FROM accounts WHERE account_id=?",$to_account);
        if ($convert != 0 && ($cur1[0]['cur'] != $cur2[0]['cur']))
        {
            $res = $this->db->query("SELECT account_currency_id FROM accounts WHERE account_id=?",$to_account);
            $acctoCurrency = $res[0]['account_currency_id'];
            $drain_money = $money * -1;
                // tr_id. было drain
		$sql = "INSERT INTO operation
                    (user_id, money, date, cat_id, account_id, tr_id, comment, transfer,  dt_create,
                    exchange_rate)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
            $this->db->query($sql, $this->user->getId(), -$money, $date, -1, $from_account, 0,
            $comment, $to_account, $curr);
            $last_id = mysql_insert_id();
            $sql = "INSERT INTO operation
                (user_id, money, date, cat_id, account_id, tr_id, comment, transfer, dt_create, imp_id, exchange_rate, type)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)";
            $this->db->query($sql, $this->user->getId(), $convert, $date, -1, $to_account, $last_id,
                $comment, $from_account, $money, $curr, $acctoCurrency);

            //$this->db->query("UPDATE operation SET tr_id=? WHERE id = ?", mysql_insert_id(), $last_id);
            
        }else{

        $sql = "INSERT INTO operation
            (user_id, money, date, cat_id, account_id, tr_id, comment, transfer, dt_create)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $last_id = $this->db->query($sql, $this->user->getId(), -$money, $date, -1, $from_account, 0,
            $comment, $to_account);

            $last_id = mysql_insert_id();
            
        $sql = "INSERT INTO operation
            (user_id, money, date, cat_id, account_id, tr_id, comment, transfer, dt_create, imp_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
        $this->db->query($sql, $this->user->getId(), $money, $date, -1, $to_account, $last_id,
            $comment, $from_account, $money);
            $last_id2 = mysql_insert_id();
        //$this->db->query("UPDATE operation SET tr_id=? WHERE id = ?", $last_id2, $last_id);
        // @FIXME Поправить переводы между счетами
        // Закомментированные запросы ещё пригодятся
            
        /*$last_id = mysql_insert_id();
        $sql = "INSERT INTO operation
                (user_id, money, date, cat_id, account_id, tr_id, comment, transfer)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, $this->user->getId(), $money, $date, -1, $to_account, 1,
            $comment, $from_account);//*/

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
        }
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
        //получаем айди дочерней смежной записи
        $tr_id = $this->db->query('SELECT * FROM operation WHERE tr_id = ? AND user_id = ?', $id, Core::getInstance()->user->getId());
        //родительской
        $idsh = $this->db->query('SELECT * FROM operation WHERE id = ? AND user_id = ?', $id, Core::getInstance()->user->getId());
        
            if ($this->db->query("DELETE FROM operation WHERE id= ? AND user_id= ?",$id, Core::getInstance()->user->getId())) {
                //return true;
            } else {
                return false;
            }
        if ( $tr_id[0]['id'] ){
            $this->db->query("DELETE FROM operation WHERE id= ? AND user_id= ?",$tr_id[0]['id'], Core::getInstance()->user->getId());
        }
        if ( $idsh[0]['tr_id'] ){
            $this->db->query("DELETE FROM operation WHERE id= ? AND user_id= ?",$idsh[0]['tr_id'], Core::getInstance()->user->getId());
        }
        return true;
    }

    /**
     *
     * @param <type> $id
     * @return <type>
     */
    function deleteTargetOperation($id=0) {
        //$que = $this->db->query("SELECT target_id FRON target_bill WHERE id=?", $id);
        $this->db->query("DELETE FROM target_bill WHERE id=? AND user_id=?", $id, Core::getInstance()->user->getId());
        Core::getInstance()->user->initUserTargets();
        Core::getInstance()->user->save();
        return true;
        //return $this->staticTargetUpdate($que);
        //return $this->staticTargetUpdate($tr_id);
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
     * @param int $type
     * @param float $sumFrom
     * @param float $sumTo
     * @return array mixed
     */
    function getOperationList($dateFrom, $dateTo, $currentCategory, $currentAccount, $type, $sumFrom, $sumTo)
    {
        if ($sumTo == 0) {
            $sumTo = null;
        }
        // Подготавливаем фильтр по родительским категориям
        $category = Core::getInstance()->user->getUserCategory();
        $cat_in = '';
        foreach ($category as $var) {
            if ($var['cat_parent'] == $currentCategory) {
                if ($cat_in) $cat_in .= ',';
                $cat_in .= $var['cat_id'];
            }
            if ($cat_in) $cat_in .= ',';
            $cat_in .= $currentCategory;
        }
        // imp_id по слухам собрались убирать. тогда понадобится другое поле под конвертацию

        // это операции со счётами
        $sql = "SELECT o.id, o.user_id, o.money, DATE_FORMAT(o.date,'%d.%m.%Y') as `date`, o.date AS dnat, ".
        " o.cat_id, NULL as target_id, o.account_id, o.drain, o.comment, o.transfer, o.tr_id, 0 AS virt, o.tags,
            o.imp_id AS moneydef, o.exchange_rate AS curs, o.type AS accountto_currency_id".
        " FROM operation o ".
        " WHERE o.user_id = " . Core::getInstance()->user->getId();
            if((int)$currentAccount > 0) {
                $sql .= " AND o.account_id = '" . (int)$currentAccount . "' ";
            }
            $sql .= " AND (`date` BETWEEN '{$dateFrom}' AND '{$dateTo}') ";
            if (!empty($currentCategory)) {
                if ($category[$currentCategory]['cat_parent'] == 0) {
                    $sql .= " AND o.cat_id IN ({$cat_in}) ";
                } else {
                    $sql .= " AND o.cat_id = '{$currentCategory}' ";
                }
            }
            if ($type >= 0) {
                if ($type == 0) { //Доход
                    $sql .= " AND o.drain = 0 AND o.transfer = 0 ";
                } elseif ($type == 1) { // Расход
                    $sql .= " AND o.drain = 1 AND o.transfer = 0 ";
                } elseif ($type == 2) { // Перевод со счёт на счёт
                    $sql .= " AND o.transfer > 0 ";
                } elseif ($type == 4) { // Перевод на финансовую цель
                    $sql .= " AND 0 = 1"; // Не выбираем эти операции
                }
            }
            if (!is_null($sumFrom)) {
                $sql .= " AND ABS(o.money) >= " . $sumFrom;
            }
            if (!is_null($sumTo)) {
                $sql .= " AND ABS(o.money) <= " . $sumTo;
            }
        //это переводы на фин цель
        $sql .= " UNION ".
        " SELECT t.id, t.user_id, -t.money, DATE_FORMAT(t.date,'%d.%m.%Y'), t.date AS dnat, ".
        " tt.category_id, t.target_id, tt.target_account_id, 1, t.comment, '', '', 1 AS virt, t.tags, NULL, NULL, NULL ".
        " FROM target_bill t ".
        " LEFT JOIN target tt ON t.target_id=tt.id ".
        " WHERE t.user_id = " . Core::getInstance()->user->getId() . 
            " AND tt.done=0 AND (`date` >= '{$dateFrom}' AND `date` <= '{$dateTo}') ";
            if((int)$currentAccount > 0) {
                $sql .= " AND t.bill_id = '{$currentAccount}' ";
            }
            if (!empty($currentCategory)) {
                $sql .= " AND 0 = 1"; // Не выбираем эти операции, т.к. у финцелей свои категории
            }
            if ($type >= 0) {
                if ($type == 0) { //Доход
                    $sql .= " AND 0 = 1"; // Не выбираем эти операции
                } elseif ($type == 1) { // Расход
                    $sql .= " AND 0 = 1"; // Не выбираем эти операции
                } elseif ($type == 2) { // Перевод со счёт на счёт
                    $sql .= " AND 0 = 1"; // Не выбираем эти операции
                }
            }
            if (!is_null($sumFrom)) {
                $sql .= " AND ABS(t.money) >= " . $sumFrom;
            }
            if (!is_null($sumTo)) {
                $sql .= " AND ABS(t.money) <= " . $sumTo;
            }
        $sql .= " ORDER BY dnat DESC, id ";

        $accounts = Core::getInstance()->user->getUserAccounts();
        $operations = $this->db->select($sql, $currentAccount, $this->user->getId(), $dateFrom,
            $dateTo, $this->user->getId(), $dateFrom, $dateTo, $currentAccount, $currentAccount, $this->user->getId(), $dateFrom,
            $dateTo);
        // Добавляем данные, которых не хватает
        foreach ($operations as $key => $val) {
            $val['cat_name']            = $category[$val['cat_id']]['cat_name'];
            $val['cat_parent']          = $category[$val['cat_id']]['cat_parent'];
            $val['account_name']        = $accounts[$val['account_id']]['account_name'];
            $sql = "SELECT account_name FROM accounts WHERE account_id = ? AND user_id = ?";
            $tr = $this->db->select($sql, $val['transfer'], $this->user->getId());
            $val['transfer_name']       = $tr[0]['account_name'];//имя счёта куда осуществляем перевод.
            $val['account_currency_id'] = $accounts[$val['account_id']]['account_currency_id'];
            //$val['account_currency_id'] = $val['target_account_id'];
            if ( ((int)$val['tr_id'] == 0) && ((int)$val['transfer'] == 0) ) {
                $val['tr_id'] = null;//хак для журнала операций. присылаю tr_id = null для не переводов
	    }
            //если фин цель то перезаписываем тот null что записан.
            if (($val['virt']) == 1){
                $val['account_currency_id'] = $accounts[$val['account_id']]['account_currency_id'];
                if (($val['cat_id']) == 1)
                    $val['cat_name'] = "Квартира";
                if (($val['cat_id']) == 2)
                    $val['cat_name'] = "Автомобиль";
                if (($val['cat_id']) == 3)
                    $val['cat_name'] = "Отпуск";
                if (($val['cat_id']) == 4)
                    $val['cat_name'] = "Фин.подушка";
                if (($val['cat_id']) == 5)
                    $val['cat_name'] = "Свадьба";
                if (($val['cat_id']) == 6)
                    $val['cat_name'] = "Быт. техника";
                if (($val['cat_id']) == 7)
                    $val['cat_name'] = "Компьютер";
                if (($val['cat_id']) == 8)//*/
                    $val['cat_name'] = "Прочее";
            }
            //@todo переписать запрос про финцель, сделать отже account_id и убрать эти строчки. +посмотреть весь код где это может использоваться

            if ( $val['transfer_name'] != null){
                $val['cat_name'] = "Отправлено со счёта";
                if ($val['tr_id']) $val['cat_name'] = "Пришло на счёт";
            }

            $val['cat_transfer']        = $accounts[$val['account_id']]['account_currency_id'];
            //$val['cur_name'] = $accounts[$val['cur_id']]['cur_name'];
            $operations[$key] = $val;
        }
        //bt.account_name as cat_transfer, //@TODO
        $retoper = '';//возвращаемые операции. не возвращаем мусор связанный с удалением счетов
        foreach ($operations as $k => $v){
            if (!($v[account_name] == ''))
                $retoper[$k] = $v;
        }
        return $retoper;
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

        // в счетах отображаем общую сумму как сумму по доходам и расходам. + учесть перевод с нужным знаком.
        $tr = "SELECT SUM(money) as sum FROM operation WHERE user_id = ? ";//AND transfer = 0 AND tr_id is NULL
        if (is_array($account_id) && count($account_id) > 0) {
            $sql = $tr." AND account_id IN (?a) {$dr}";
            $this->total_sum = $this->db->selectCell($sql, $this->user->getId(), $account_id);
        } elseif ((int)$account_id > 0 ) {
            $sql = $tr." AND account_id = ?d  {$dr}";
            $this->total_sum = $this->db->selectCell($sql, $this->user->getId(), $account_id);
        } elseif((int)$account_id == 0) {
            $sql = $tr."  {$dr}";
            $this->total_sum = $this->db->selectCell($sql, $this->user->getId());
        } else {
            trigger_error(E_USER_NOTICE, 'Ошибка получения всей суммы пользователя');
            return 0;
        };
        /*$sql = "SELECT SUM(-money) as sum FROM operation WHERE user_id = ? AND transfer != 0 AND account_id=?";
        $a = $this->db->selectCell($sql, $this->user->getId(), $account_id);
        $this->total_sum+=$a;
        $sql = "SELECT SUM(money) as sum FROM operation WHERE user_id = ? AND transfer = ? AND imp_id is null";
        $a = $this->db->selectCell($sql, $this->user->getId(), $account_id);
        $this->total_sum+=$a;
        $sql = "SELECT SUM(imp_id) as sum FROM operation WHERE user_id = ? AND transfer = ? AND imp_id is not null";
        $a = $this->db->selectCell($sql, $this->user->getId(), $account_id);
        $this->total_sum+=$a;*/
        return $this->total_sum;
    }
    /*
    * Функция возвращает первую операцию по счёту - начальный баланс
     */
    function getFirstOperation($account_id=0)
    {
        $sql = "SELECT money FROM operation WHERE user_id=? AND account_id=? AND comment='Начальный остаток'";
        $first = $this->db->query($sql, $this->user->getId(), $account_id);
        return $first[0]['money'];
    }

    function getComment($account_id=0)
    {
        $sql = "SELECT account_description FROM accounts WHERE user_id=? AND account_id=?";
        $com = $this->db->query($sql, $this->user->getId(), $account_id);
        return $com[0]['account_description'];
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
    /**
     * Возвращает тип операции по айди
     * @param integer $id
     * @return integer
     */
    function getTypeOfOperation($id=0){
        $type = 0;//возвращаемый тип операции
        $sql = "SELECT drain, transfer, count(*) as c FROM operation WHERE id=? AND user_id=? GROUP BY id";
        $res1 = $this->db->query($sql, $id, $this->user->getId());
        $sql = "SELECT count(*) AS c FROM target_bill WHERE id=? AND user_id=?";
        $res2 = $this->db->query($sql, $id, $this->user->getId());
        if ( $res1[0]['c'] != $res2[0]['c'] ){
            if ( $res1[0]['c'] == 1 ){
                if ( $res1[0]['drain'] == 1 )
                    $type = 0;
                if ( $res1[0]['drain'] == 0 )
                    $type = 1;
                if ( $res1[0]['transfer'] != 0 )
                    $type = 2;
            }
            else
                $type = 4;
        }//определили тип, иначе
        else
            return null;//один случай на миллиард. а на деле врят ли произойдёт. случай если есть и операция и перевод на фин целт с одним айди
        return $type;
    }
}
