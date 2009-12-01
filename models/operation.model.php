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
            if ((float)$_POST['currency'] != 0) {
                $valid['convert'] = round($valid['amount'] /  (float)$_POST['currency'], 2);
            } else {
                $valid['convert'] = 0;
            }
            $valid['toAccount'] = (int)@$_POST['toAccount'];
        // - Финансовая цель
        } elseif($valid['type'] == 4) {
            $valid['target'] = (int)@$_POST['target'];
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
    function editTransfer($id=0, $money = 0, $date = '', /*$category = 0, $drain = 0,*/ $account = 0, $toAccount=0, $comment = '', $tags = null){
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
            $sql = "UPDATE operation SET money=?, date=?, account_id=?, transfer=?, comment=?, tags=?
                WHERE user_id = ? AND id = ?";
            $this->db->query($sql, $money, $date, $account, $toAccount, $comment, implode(', ', $tags), $this->user->getId(), $id);
        }
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
    function addTransfer($money, $convert, $date, $from_account, $to_account, $comment, $tags)
    {

        if ($convert != 0)
        {
            $drain_money = $money * -1;
                // tr_id. было drain
		$sql = "INSERT INTO operation
                    (user_id, money, date, cat_id, account_id, tr_id, comment, transfer, imp_id, dt_create)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $this->db->query($sql, $this->user->getId(), $money, $date, -1, $from_account, 1,
            $comment, $to_account, $convert);
        }else{

        $drain_money = $money * -1;

        // tr_id. было drain
        $sql = "INSERT INTO operation
            (user_id, money, date, cat_id, account_id, tr_id, comment, transfer, dt_create)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $last_id = $this->db->query($sql, $this->user->getId(), $money, $date, -1, $from_account, 1,
            $comment, $to_account);

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
        if ($this->db->query("DELETE FROM operation WHERE id= ? AND user_id= ?",$id, Core::getInstance()->user->getId())) {
            return true;
        } else {
            return false;
        }
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
        " o.cat_id, o.account_id, o.drain, o.comment, o.transfer, o.tr_id, 0 AS virt, o.tags, o.imp_id ".
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
        " SELECT t.id, t.user_id, t.money, DATE_FORMAT(t.date,'%d.%m.%Y'), t.date AS dnat, ".
        " tt.category_id, tt.target_account_id, 1, t.comment, '', '', 1 AS virt, t.tags, NULL ".
        " FROM target_bill t ".
        " LEFT JOIN target tt ON t.target_id=tt.id ".
        " WHERE t.user_id = " . Core::getInstance()->user->getId() . 
            " AND (`date` >= '{$dateFrom}' AND `date` <= '{$dateTo}') ";
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
            //если фин цель то перезаписываем тот null что записан.
            if (($val['virt']) == 1){
                $val['account_currency_id'] = $accounts[$val['target_account_id']]['account_currency_id'];
                if (($val['cat_id']) == 1)
                    $val['cat_name'] = "Квартира";
                if (($val['cat_id']) == 2)
                    $val['cat_name'] = "Автомобиль";
                if (($val['cat_id']) == 3)
                    $val['cat_name'] = "Отпуск";
                if (($val['cat_id']) == 4)
                    $val['cat_name'] = "Фин.подушка";
                if (($val['cat_id']) == 5)//*/
                    $val['cat_name'] = "Прочее";
            }
            //@todo переписать запрос про финцель, сделать отже account_id и убрать эти строчки. +посмотреть весь код где это может использоваться

            $val['cat_transfer']        = $accounts[$val['account_id']]['account_currency_id'];
            //$val['cur_name'] = $accounts[$val['cur_id']]['cur_name'];
            $operations[$key] = $val;
        }
        //bt.account_name as cat_transfer, //@TODO
        return $operations;
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
        $tr = "SELECT SUM(money) as sum FROM operation WHERE user_id = ? AND transfer = 0 AND tr_id is NULL";
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
        $sql = "SELECT SUM(-money) as sum FROM operation WHERE user_id = ? AND transfer != 0 AND account_id=?";
        $a = $this->db->selectCell($sql, $this->user->getId(), $account_id);
        $this->total_sum+=$a;
        $sql = "SELECT SUM(money) as sum FROM operation WHERE user_id = ? AND transfer = ? AND imp_id is null";
        $a = $this->db->selectCell($sql, $this->user->getId(), $account_id);
        $this->total_sum+=$a;//*/
        $sql = "SELECT SUM(imp_id) as sum FROM operation WHERE user_id = ? AND transfer = ? AND imp_id is not null";
        $a = $this->db->selectCell($sql, $this->user->getId(), $account_id);
        $this->total_sum+=$a;
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