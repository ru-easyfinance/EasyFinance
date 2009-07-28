<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс для управления транзакциями
 * @author korogen
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * @category money
 * @version SVN $Id$
 */
class Money
{
    /**
     * Ссылка на экземпляр класса базы данных
     * @var <DbSimple_Mysql>
     */
    private $db             = null;

    /**
     * Ссылка на экземпляр класса пользователя
     * @var <User>
     */
    private $user           = null;

    /**
     * Хрень какая-то
     * @var <type>
     * @FIXME 
     */
    private $cat_id         = 0;

    /**
     *
     * @var <type>
     */
    private $current_date   = 0;

    /**
     *
     * @var <type>
     */
    private $account_money  = 0;

    /**
     *
     * @var <type>
     */
    private $user_money     = Array();

    /**
     *
     * @var <type>
     */
    private $total_sum      = 0;

    /**
     * Конструктор
     * @return bool
     */
    function __construct()
    {
        $this->db = Core::getInstance()->db;
        $this->user = Core::getInstance()->user;
        $this->current_date = date('d.m.Y');

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
        $_SESSION['user_money'] = $this->user_money;
        $_SESSION['account_money'] = $this->account_money;
        $_SESSION['total_sum'] = $this->total_sum;
    }

    /**
     * Получение списка транзакций
     * @param <date> $dateFrom
     * @param <date> $dateTo
     * @param <int> $currentCategory
     * @param <int> $currentAccount
     * @return <array> mixed
     */
    function getOperationList($dateFrom, $dateTo, $currentCategory, $currentAccount)
    {
        //@FIXME Оптимизировать запросы, если возможно
        $order = "AND (m.`date` BETWEEN '{$dateFrom}' AND '{$dateTo}')";
        $order1 = "AND (t.`date` BETWEEN '{$dateFrom}' AND '{$dateTo}')";
        if (!empty($currentCategory)) {
            $order .= " AND (m.cat_id = {$currentCategory} OR c.cat_parent = {$currentCategory})";
            $order1 .= " AND (cy.cat_id = {$currentCategory} OR cy.cat_parent = {$currentCategory})";
        }

        $limit = "";

        if (IS_DEMO) {
            $sql = "SELECT m.id, m.user_id, m.money, DATE_FORMAT(m.date,'%d.%m.%Y') as `date`,
                       m.cat_id, m.bill_id, c.cat_name, c.cat_parent, b.bill_name, m.drain, m.comment,
                       b.bill_currency, cu.cur_name, m.transfer, m.tr_id,
                       bt.bill_name as cat_transfer
                    FROM money m
                    LEFT JOIN category c on c.cat_id = m.cat_id
                    LEFT JOIN bill b on b.bill_id = m.bill_id and b.user_id = ?
                    LEFT JOIN bill bt on bt.bill_id = m.transfer and bt.user_id = ?
                    LEFT JOIN currency cu on cu.cur_id = b.bill_currency
                    WHERE m.bill_id = ?
                           AND m.user_id = ?
                           ".$order."
                    ORDER BY m.`date` DESC, m.id DESC " . $limit;
            return $this->db->select($sql, $this->user->getId(), $this->user->getId(), $currentAccount, $this->user->getId());
        }else{
            $sql = "SELECT m.id, m.user_id, m.money, DATE_FORMAT(m.date,'%d.%m.%Y') as `date`,
                    m.cat_id, m.bill_id, c.cat_name, c.cat_parent, b.bill_name, m.drain, m.comment,
                    b.bill_currency, cu.cur_name, m.transfer, m.tr_id,
                    bt.bill_name as cat_transfer, 0 AS virt
                FROM money m
                    LEFT JOIN category c on c.cat_id = m.cat_id
                    LEFT JOIN bill b on b.bill_id = m.bill_id
                    LEFT JOIN bill bt on bt.bill_id = m.transfer
                    LEFT JOIN currency cu on cu.cur_id = b.bill_currency
                WHERE m.bill_id = ?
                    AND m.user_id = ?
                    {$order}
                UNION
                    SELECT t.id, t.user_id, t.money, DATE_FORMAT(t.date,'%d.%m.%Y'), tt.id,
                    t.bill_id, (IF(LENGTH(tt.title)>30,CONCAT(LEFT(tt.title, 30),'..'),tt.title)), cy.cat_parent, bl.bill_name , 0, t.comment, bl.bill_currency, '', '', '', '', 1 AS virt
                    FROM target_bill t
                        LEFT JOIN target tt ON t.target_id=tt.id
                        LEFT JOIN bill bl ON t.bill_id=bl.bill_id
                        LEFT JOIN category cy ON cy.cat_id = tt.category_id
                    WHERE t.user_id= ? {$order1}
                ORDER BY `date` DESC, `id` DESC";
            $a = $this->db->select($sql, $currentAccount, $this->user->getId(), $this->user->getId());
            die(var_dump($a));
            return $a;
        }
    }

    /**
     * Возвращает все деньги пользователя по определённому счёту
     * @param <string> $bill_id Ид счёта
     * @return <int>
     */
    function getTotalSum($bill_id)
    {
        $sql = "SELECT SUM(money) as sum FROM money WHERE user_id = ? AND bill_id = ?";
        $this->total_sum = $this->db->selectCell($sql, $this->user->getId(), $bill_id);
        return $this->total_sum;
    }

/**
 * Операции с операциями
 */

    /**
     * Возвращает список из последних 30 транзакций или за указанный период (неделя, месяц, день)
     * @param $bill_id Ид счёта
     * @return void
     */
    function selectMoney($bill_id)
    {
        $g_order = trim($_GET['order']);
        if (!empty($g_order)) {
            if ($g_order == "today") {
                $order = "AND (m.`date` = '".date("Y.m.d")."' or m.`date` = '0000.00.00')";
            }
            if ($g_order == "month") {
                $order = "AND (m.`date` BETWEEN '".date("Y.m.01")."' AND '".date("Y.m.31")."' or m.`date` = '0000.00.00')";
            }
            if ($g_order == "week") {
                $begin_week = (date('d')+1) - date('w');
                $order = "AND (m.`date` BETWEEN '".date("Y.m.$begin_week")."' AND '".date("Y.m.d")."' or m.`date` = '0000.00.00')";
            }
        }else{
            $limit = "LIMIT 0, 30";
        }

        $sql = "SELECT m.id, m.user_id, m.money, DATE_FORMAT(m.date,'%d.%m.%Y') as `date`,
                   m.cat_id, m.bill_id, c.cat_name, b.bill_name, m.drain, m.comment,
                   b.bill_currency, cu.cur_name, m.transfer, m.tr_id,
                   bt.bill_name as cat_transfer
                FROM money m
                LEFT JOIN category c on c.cat_id = m.cat_id
                LEFT JOIN bill b on b.bill_id = m.bill_id
                LEFT JOIN bill bt on bt.bill_id = m.transfer
                LEFT JOIN currency cu on cu.cur_id = b.bill_currency
                    WHERE m.bill_id = ? AND m.user_id = ? ".
                $order.
                "ORDER BY m.`date` DESC, m.id DESC ".$limit;

        $this->user_money = $this->db->select($sql, $bill_id, $this->user->getId());
        $this->account_money = $bill_id;
        $this->getTotalSum($bill_id);
        $this->save();
    }

    /**
     * Возвращает операцию со всеми параметрами
     * @param $id
     * @return unknown_type
     */
    function getMoney($id)
    {
        $sql = "SELECT m.id, m.user_id, m.money, m.tr_id,m.transfer,
            DATE_FORMAT(m.date,'%d.%m.%Y') as `date`, m.cat_id, m.bill_id, m.drain, m.comment,
            c.cat_name as cat_restore
            FROM money m
            LEFT JOIN category c on m.cat_id = c.cat_id
            WHERE m.id = ? AND m.user_id = ?";
        $row = $this->db->selectRow($sql, $id, $this->user->getId());
        if ($row['drain'] == 1) {
            $row['money'] = ABS($row['money']);
        }
        return $row;
    }

    /**
     * Регистрирует новую транзакцию
     *
     * @param integer $cat_type 1-Добавлять новую категорию, иначе - не добавлять
     * @param string $cat_name Название новой категории.
     * @param string $cat_id Код новой категории
     * @param float $money Сумма транзакции
     * @param string $date Дата транзакции в формате Y.m.d
     * @param integer $drain Доход или расход. Устаревшее, но на всякий случай указывать над
     * @param string $comment Комментарий транзакции
     * @param integer $bill_id Код счета
     * @param string $impID Код импорта. Опционеле
     * @param string $impDate Дата импорта в формате Y.m.d. Опционально
     * @return boolean true - Регистрация прошла успешно
     */
    function saveMoney($cat_type, $cat_name, $cat_id, $money, $date, $drain, $comment, $bill_id, $impID='',$impDate='')
    {
        //Если создаём новую категорию
        if ($cat_type == 1) {
            //Если это субкатегория
            if ($cat_id) {
                $sql = "INSERT INTO category (cat_name, cat_parent, user_id, cat_active) VALUES (?, ?, ?, '1')";
                $this->db->query($sql, $cat_name, $cat_id, $this->user->getId());
            // Если категория первого уровня
            } else {
                $sql = "INSERT INTO category (cat_name, user_id, cat_active) VALUES (?, ?, '1')";
                $this->db->query($sql, $cat_name, $this->user->getId());
            }
            $this->user->initUserCategory($this->user->getId());
            $this->user->save();
            $cat_id = mysql_insert_id();
        }

        if ($impID && $impDate) {
            $sql = "INSERT INTO money (user_id, money, `date`, cat_id, bill_id, drain, comment,
                imp_id, imp_date) VALUES (?,?,?,?,?,?,?,?,?)";
            $this->db->query($sql, $this->user->getId(), $money, $date, $cat_id, $bill_id, $drain,
                $comment, $impID, $impDate);
        } else {
            $sql = "INSERT INTO money (user_id, money, `date`, cat_id, bill_id, drain, comment) VALUES (?,?,?,?,?,?,?)";
            $this->db->query($sql, $this->user->getId(), $money, $date, $cat_id, $bill_id, $drain, $comment);
        }
        //TODO Убрать отсюда использование сессий
        $_SESSION['user']['POS_ID'] = mysql_insert_id();
        $_SESSION['account'] = "reload";
        $this->selectMoney($this->user->getId());
        $this->save();

        return true;
    }

    /**
     * Удаляет операцию
     * @param $id int Ид операции
     * @return bool
     */
    function deleteMoney($id)
    {
        $this->db->query("DELETE FROM money WHERE id = ? and user_id = ?", $id, $this->user->getId());

        $_SESSION['account'] = "reload";
        $this->selectMoney($this->account_money);
        $this->save();

        return true;
    }

    /**
     * Редактирует операцию
     * @param $id
     * @param $cat_type
     * @param $cat_name
     * @param $cat_id
     * @param $money
     * @param $date
     * @param $drain
     * @param $comment
     * @param $bill_id
     * @return bool
     */
    function updateMoney($id,$cat_type, $cat_name, $cat_id, $money, $date, $drain, $comment, $bill_id)
    {
        if ($cat_type == 1) {
            $sql = "INSERT INTO category (cat_name, cat_parent, user_id, cat_active) VALUES (?,?,?,1)";
            $this->db->query($sql, $cat_name, $cat_id, $this->user->getId());
            $this->user->initUserCategory($this->user->getId());
            $this->user->save();
            $cat_id = mysql_insert_id();
        }

        $sql = "UPDATE money SET user_id = ?, money = ?, `date` = ?, cat_id = ?, bill_id = ?,
                    drain = ?, comment = ? WHERE id = ?";
        $this->db->query($sql, $this->user->getId(), $money, $date, $cat_id, $bill_id, $drain, $comment, $id);

        $_SESSION['account'] = "reload";
        $this->selectMoney($bill_id);
        $this->save();

        return true;
    }

    /**
     * Выбирает операцию для редактирования
     * @param $id int Ид операции
     * @param $cat_type
     * @param $cat_name
     * @param $cat_id
     * @param $money
     * @param $date
     * @param $drain
     * @param $comment
     * @param $bill_id
     * @return bool
     */
    function editOperation($id, $cat_type, $cat_name, $cat_id, $money, $date, $drain, $comment, $bill_id)
    {
        $sql = "SELECT id, tr_id FROM money WHERE id = ?";
        $row = $this->db->selectRow($sql, $id);
        if (!empty($row['tr_id'])) {
            $this->deleteOperationTransfer($row['tr_id']);
            $this->saveMoney($cat_type, $cat_name, $cat_id, $money, $date, $drain, $comment, $bill_id);
        }else{
            $this->updateMoney($id, $cat_type, $cat_name, $cat_id, $money, $date, $drain, $comment, $bill_id);
        }
    }

/**
 * Трансферты
 */

    /**
     * Выбирает трансферт для редактирования
     * @param $id
     * @param $convert
     * @param $sum
     * @param $date
     * @param $to_account
     * @param $bill_id
     * @param $comment
     * @return
     */
    function editOperationTransfer($id, $convert, $sum, $date, $to_account, $bill_id, $comment)
    {
        $sql = "SELECT id, tr_id FROM money WHERE id = ?";
        $row = $this->db->selectRow($sql, $id);
        if (empty($row['tr_id'])) {
            $this->deleteMoney($id);
            $this->addOperationTransfer($sum, $convert, $date, $bill_id, $to_account, $comment);
        }else{
            $this->updateOperationTransfer($sum, $convert, $date, $bill_id, $to_account, $row['tr_id'], $comment);
        }
    }
    /**
     * Редактирует трансферт
     * @param $money
     * @param $convert
     * @param $date
     * @param $from_account
     * @param $to_account
     * @param $tr_id
     * @param $comment
     * @return bool
     */
    function updateOperationTransfer($money, $convert, $date, $from_account, $to_account, $tr_id, $comment)
    {
        //$drain_money = "-$money";
        $drain_money = -1 * $money;

        $sql = "UPDATE money SET money = ?, `date` = ?, transfer = ?, comment = ?
                    WHERE bill_id = ? AND user_id = ? AND drain = '1' AND tr_id = ?";
        $this->db->query($sql, $drain_money, $date, $to_account, $comment, $from_account,
            $this->user->getId(), $tr_id);

        $sql = "UPDATE money SET money = ?, `date` = ?, bill_id = ?, comment = ?
                    WHERE transfer = ? AND user_id = ? AND drain = '0' AND tr_id = ?";
        $this->db->query($sql, $convert, $date, $to_account, $comment, $from_account,
            $this->user->getId(), $tr_id);

        $_SESSION['user_money'] = "reload";

        $this->user->initUserAccount($this->user->getId());
        $this->user->save();

        return true;
    }


    /**
     * Удаляет трансферт
     * @param $id int Ид операции (трансферта)
     * @return bool
     */
    function deleteOperationTransfer($id)
    {

        $sql = "DELETE FROM money WHERE tr_id = ? and user_id = ?";
        $this->db->query($sql, $id, $this->user->getId());

        $_SESSION['user_money'] = "reload";
        $this->user->initUserAccount($this->user->getId());
        $this->user->save();

        return true;
    }

    /**
     * Добавляет трансферт с одного на другой счёт
     * @param $money float Деньги
     * @param $convert Конвертированные в нужную валюту деньги
     * @param $date Дата, когда совершаем трансфер
     * @param $from_account Из счёта
     * @param $to_account В счёт
     * @param $comment Комментарий
     * @return bool
     */
    function addOperationTransfer($money, $convert, $date, $from_account, $to_account, $comment)
    {
        $tr_id = md5($this->user->getId()."+".date("d-m-Y H-i-s"));
        $drain_money = $money * -1; // 0 - доход, 1- расход

        $sql = "INSERT INTO money (user_id, money, `date`, cat_id, bill_id, drain, comment,transfer,
            tr_id) VALUES (?,?,?,?,?,?,?,?,?),(?,?,?,?,?,?,?,?,?)";
        $this->db->query($sql,
            $this->user->getId(), $drain_money, $date, '-1', $from_account, '1',$comment, $to_account, $tr_id,
            $this->user->getId(), $convert, $date, '-1', $to_account, '0', $comment , $from_account, $tr_id);

        $_SESSION['user_money'] = "reload";

        $this->user->initUserAccount($this->user->getId());
        $this->user->save();

        return true;
    }

/**
 * Устаревшие, если на них не будет найдено ссылок - их нужно будет удалить
 * @deprecated
 */

    /**
     * Подготавливает массив дат от начала интервала до его конца
     * @deprecated
     * @param array $dateFrom Массив с датой начала диапазона, день месяц год
     * @param array $dateTo Массив с датой окончания диапазона, день месяц год
     *
     * @return array Массив месяцев с разбивкой по годам
     *
     * @throws Exception
     */
    private function _prepareInterval($dateFrom,$dateTo) {
        $res = array();
        $yearFrom = (int) $dateFrom[2];
        $yearTo = (int) $dateTo[2];
        $monthFrom = (int) $dateFrom[1];
        $monthTo = (int) $dateTo[1];

        // Перебираем годы
        $y=$yearFrom;
        while ($y <= $yearTo) {
            // Внутри года перебираем месяцы
            // Месяц начала диапазона. Если год начала диапазона, то и месяц начала диапазона
            $ms = ($y == $yearFrom)?$monthFrom:1;
            // Месяц окончания диапазона. Если год окончания диапазона, то и месяц окончания диапазона
            $me = ($y == $yearTo)?$monthTo:12;
            // Создаем массив месяцев
            for ($m=$ms;$m<=$me;$m++) $res[$y][$m] = 0;
            $y++;
        } // годы

        return $res;
    } // _prepareInterval

    /**
     * Преоразует интервал из формата [Год][Месяц_Числовой]=>Значение в формат ["Название месяца,Год"]=>Значение
     * @deprecated
     * @param array $interval Интервал для преобразования
     *
     * @return array Преобразованный массив
     *
     * @throws Exception
     */
    private function _transformInterval($interval) {
        // Русский перевод месяцев
        $monthNames = array(
       '1'   => 'Январь',
       '2'   => 'Февраль',
       '3'   => 'Март',
       '4'   => 'Апрель',
       '5'   => 'Май',
       '6'   => 'Июнь',
       '7'   => 'Июль',
       '8'   => 'Август',
       '9'   => 'Сентябрь',
       '10'  => 'Октябрь',
       '11'  => 'Ноябрь',
       '12'  => 'Декабрь',
        );

        $res =array();
        foreach ($interval as $year=>$months) {
            foreach ($months as $month=>$value) {
                $monthName = $monthNames[$month];
                $res["$monthName,$year"] = $value;
            } // Месяцы
        } // годы
        return $res;
    } // _transformInterval

    /**
     * Добавляет операцию к депозитному счёту
     * @deprecated
     * @param $bill_id
     * @param $date
     * @param $sum
     * @param $to_account
     * @param $convert
     * @return unknown_type
     */
    private function addOperationDeposit($bill_id, $date, $sum, $to_account, $convert)
    {
        $sql = "select `balance_for_percent`, `total_sum` from `account_deposit_list` where `account_id` = '".$bill_id."' order by `date_operation`";
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);

        $balance_for_percent = $row['balance_for_percent'] + $sum;
        $total_sum = $row['total_sum'] + $sum;

        $sql = "INSERT INTO `account_deposit_list`
                    (`account_id`, `date_operation`, `sum_operation`, `balance_for_percent`, `accrued_interest`, `added_interest`, `description`, `total_sum`)
                VALUES
                    ('".$bill_id."', '".$date."', '".$sum."', '".$balance_for_percent."', '0', '0', 'Пополнение депозита', '".$total_sum."')
                ";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в cохранении финансов!', '', __LINE__, __FILE__, $sql);
        }
        $this->addOperationTransfer($sum, $convert, $date, $to_account, $bill_id, 'Перевод на депозит');
    }

    /**
     * Возвращает список транзакций пользователя по указанным счетам за указанный период
     * @deprecated
     * @param string $userID Код пользователя
     * @param array $accounts Список кодов счетов
     * @param string $dateFrom Дата начала периода
     * @param string $dateTo Дата окончания периода
     * @param object Account $account Объект контроллера счетов
     * @param object Category $category Объект
     *
     * @return array Список транзакций
     * @throws Exception
     * @access public
     */
    private function getTransactions($userID,$accounts=array(),$dateFrom='',$dateTo='',$account,$category) {
        if (count($accounts)==0) throw new Exception('Не указаны счета',1);
        if (!is_a($account,'Account')) throw new Exception('Объект Account неверного типа',1);
        if (!is_a($category,'Category')) throw new Exception('Объект Category неверного типа',1);

        $userAccounts = $account->getUserAccounts($userID);
        $userCategories = $category->getUserCategories($userID);

        // Создаем часть запроса, ограничивающую список счетов
        $accQ = " in ('".join("','",$accounts)."') ";

        //Создаем ограничение по датам

        // Преобразуем даты в формат SQL
        list($day,$month,$year) = explode(".", $dateFrom);
        $dateFrom = $year.".".$month.".".$day;

        list($day,$month,$year) = explode(".", $dateTo);
        $dateTo = $year.".".$month.".".$day;

        $dateQ = " BETWEEN '$dateFrom' AND '$dateTo' ";

        // Окончательный запрос
        $sql = "SELECT m.money, DATE_FORMAT(m.date,'%d.%m.%Y') as d, m.comment, m.cat_id,m.transfer,m.bill_id FROM money m WHERE m.user_id='$userID' AND (m.bill_id $accQ) AND (m.date $dateQ) ORDER BY m.date";

        //pre($sql);


        if ( !($result = $this->db->sql_query($sql)) )
        {
            trigger_error('Ошибка при получении списка счетов пользователя', E_USER_ERROR);
        }
        else
        {
            $rows = $this->db->sql_fetchrowset($result);
            //pre($rows);

            $myRes = array();
            foreach ($rows as $r) {
                $cn = ($r['money']>=0)?'перевод со счета':'перевод на счет';
                $tr[]=array(
                    'amount'=>$r['money'],
                    'date'=>$r['d'],
                    'comment'=>preg_replace('/[\n\r]/','',$r['comment']),
                    'category'=>($r['cat_id']==-1)?$cn:$userCategories[$r['cat_id']],
                    'receiver_account'=>$userAccounts[$r['transfer']],
                    'payer_account'=>$userAccounts[$r['bill_id']],
                );
            } // while
            //pre($myRes);
            return $tr;
        }   // if result
    } // getTransactions

    /**
     * Возвращает список всех проведенных импортов с точностью до минуты
     * @deprecated
     * @param string $userID Код пользователя
     *
     * @return array Список импортов. Значение - дата импорта
     * @throws Exception
     * @access public
     */
    private function getImportsList($userID) {
        $sql = "select distinct imp_id, DATE_FORMAT(imp_date,'%d.%m.%Y %H:%i') as idt from money where imp_id is not null and user_id='$userID'";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            throw new Exception('Ошибка при получении списка импортов',1);
        }
        else
        {
            $rows = $this->db->sql_fetchrowset($result);
            //pre($rows);

            $myRes = array();
            if (count($rows)>0) {
                foreach ($rows as $r) {
                    $tr[] = array(
                    'imp_id' => $r['imp_id'],
                    'imp_date' => $r['idt'],
                    );
                } // while
            } // if count > 0
            //pre($myRes);
            return $tr;
        }   // if result
    } // getImportsList

    /**
     * Откатывает указанный импорт
     * @deprecated
     * @param string $impID Код импорта
     * @param string $userID Код пользователя
     *
     * @return void
     * @throws Exception
     * @access public
     */
    private function rollbackImport($impID,$userID) {
        // Отыскиваем счет, в который был сделан импорт, чтобы потом обновить куки по этому счету
        $sql = "select bill_id from money where imp_id='$impID'";
        if ( !($result = $this->db->sql_query($sql)) )
        {
            throw new Exception('Не удалось определить счет, на который производился импорт. Операция невозможна.',2);
        }
        else
        {
            $r = $this->db->sql_fetchrow($result);
            //pre($rows);
            $accountID = $r['bill_id'];
        }   // if result

        // Удаляем все записи импорта
        $sql = "delete from money where imp_id='$impID' AND user_id='$userID'";
        //pre($sql);

        if ( !($result = $this->db->sql_query($sql)) )
        {
            throw new Exception('Ошибка в запросе отката импорта',1);
        }


        // Обновляем куки
        $_SESSION['account'] = "reload";
        $this->selectMoney($accountID);
        $this->save();
    } // rollbackImport

    /**
     * Выводит данные о доходе пользователя за период с разбивкой по категориям доходов
     * @deprecated
     * @param array $rpd Параметры отчета
     * userID - код пользователя
     * dateFrom - дата начала периода в формате дд.мм.ггг
     * dateTo - дата окончания периода в формате дд.мм.ггг
     * account - код счета
     * currency_rates - курсы валют по отношению к рублю. Хэш. Код=>Курс
     * currency - Код валюты, в которой показывать сумму
     * months - названия месяцев. Хэш. Номер=>Название
     * @param object Category $category Объект контроллера категорий
     *
     * @return array Данные о доходе. Хэш.
     * ключ - название категории
     * значение - величина дохода
     * @throws Exception
     * @access public
     */
    private function getProfit($rpd,$category) {
        $userID = $rpd['userID'];
        $currencyRates = $rpd['currency_rates'];
        $currencySelected = $rpd['currency'];

        if (!is_a($category,'Category')) throw new Exception('Объект Category неверного типа',1);

        $userCategories = $category->getUserCategories($userID);

        // Преобразуем даты в формат SQL
        list($day,$month,$year) = explode(".", $rpd['dateFrom']);
        $dateFrom = $year.".".$month.".".$day;

        list($day,$month,$year) = explode(".", $rpd['dateTo']);
        $dateTo = $year.".".$month.".".$day;

        $dateQ = " AND date BETWEEN '$dateFrom' AND '$dateTo' ";

        // Если указан код счета, то делаем дополнительную фильтрацию по нему
        $accID = $rpd['account'];
        $accQ = ($accID)?" AND m.bill_id='$accID' ":'';

        $sql = "select count(*) as cn ,cat_id,sum(money) as s,b.bill_currency as crn from money m,bill b where b.bill_id=m.bill_id AND b.user_id='$userID' AND money>0 AND cat_id>0 and m.user_id='$userID' and date $dateQ $accQ group by cat_id order by s desc";
        //pre($sql);

        $profits = array();
        if ( !($result = $this->db->sql_query($sql)) )
        {
            throw new Exception('Ошибка при получении списка доходов',1);
        }
        else
        {
            $rows = $this->db->sql_fetchrowset($result);
            //pre($rows);

            $totalSum = 0;

            if (count($rows)>0) {
                foreach ($rows as $r) {
                    $sum = $r['s'];
                    $catID = $r['cat_id'];



                    $currencyID = $r['crn'];
                    // Переводим сумму из валюты счета в валюту, выбранную пользователем
                    // Сначала переводим из валюты счета в рубли
                    $sum = $sum * $currencyRates[$currencyID];
                    // А затем из рублей в валюту, выбранную пользователем
                    $sum = $sum / $currencyRates[$currencySelected];

                    // Сразу считаем максимальную сумму, чтобы посчитать долю
                    $totalSum += $sum;

                    //$profits[$userCategories[$catID]] = $sum;
                    //$profits[$catID] = $sum;
                    $profits[$catID] = array(
                    'catName' => $userCategories[$catID],
                    'sum' => $sum,
                    'cn' => $r['cn'],
                    );

                } // while
            } // if count > 0

            // Еще раз пробегаемся по массиву, чтобы посчитать долю каждого элемент
            foreach ($profits as $catID => $catData) {
                $profits[$catID]['part'] = ($profits[$catID]['sum']*100)/$totalSum;
            } // foreach

            return $profits;
        }   // if result


        //$profits = array('Зарплата'=>30000, 'Home-money'=>10000);
        //return $profits;
    } // getProfit

    /**
     * Выводит данные о доходах пользователя за период с разбивкой по категориям доходов
     * @deprecated
     * @param array $rpd Параметры отчета
     * userID - код пользователя
     * dateFrom - дата начала периода в формате дд.мм.ггг
     * dateTo - дата окончания периода в формате дд.мм.ггг
     * account - код счета
     * currency_rates - курсы валют по отношению к рублю. Хэш. Код=>Курс
     * currency - Код валюты, в которой показывать сумму
     * months - названия месяцев. Хэш. Номер=>Название
     *
     * @return array Данные о доходе. Хэш.
     * название категории => разбивка дохода по дням и счетам. Хэш
     *  дата => список счетов. Хэш
     *      счет => величина дохода в рублях?
     * @throws Exception
     * @access public
     */
    private function getDetailedProfit($rpd) {
        $userID = $rpd['userID'];
        $currencyRates = $rpd['currency_rates'];
        $currencySelected = $rpd['currency'];

        // Преобразуем даты в формат SQL
        if ($rpd['dateFrom']) {
            list($day,$month,$year) = explode(".", $rpd['dateFrom']);
            $dateFrom = "'".$year.".".$month.".".$day."'";
        }
        else $dateFrom = '(NOW() - INTERVAL 1 MONTH)';

        if ($rpd['dateTo']) {
            list($day,$month,$year) = explode(".", $rpd['dateTo']);
            $dateTo = "'".$year.".".$month.".".$day."'";
        }
        else $dateTo = 'NOW()';
        $dateQ = " AND (m.date BETWEEN $dateFrom AND $dateTo) ";

        // Если указан код счета, то делаем дополнительную фильтрацию по нему
        $accID = $rpd['account'];
        $accQ = ($accID)?" AND m.bill_id='$accID' ":'';

        // Получаем список
        $sql = "SELECT c.cat_name  as cn, m.cat_id as cid, date(date) as d,b.bill_name as bn, sum(money) as s, b.bill_currency as crn FROM money m,bill b, category c WHERE money>=0 AND m.cat_id > 0 AND m.user_id='$userID' AND b.bill_id=m.bill_id AND b.user_id='$userID' AND c.cat_id=m.cat_id $dateQ $accQ group by m.cat_id,date(m.date),m.bill_id";
        //pre($sql);

        $profit = array();
        if ( !($result = $this->db->sql_query($sql)) )
        {
            throw new Exception('Ошибка при получении списка доходов',1);
        }
        else
        {
            $rows = $this->db->sql_fetchrowset($result);
            //pre($rows);

            if (count($rows)>0) {
                foreach ($rows as $r) {
                    $categoryName = $r['cn'];
                    // Преобразуем дату в русский формат
                    list($year,$month,$day) = explode('-',$r['d']);
                    $dt = "$day.$month.$year";
                    $accountName = $r['bn'];
                    $sum = $r['s'];
                    $currencyID = $r['crn'];

                    // Переводим сумму из валюты счета в валюту, выбранную пользователем
                    // Сначала переводим из валюты счета в рубли
                    $sum = $sum * $currencyRates[$currencyID];
                    // А затем из рублей в валюту, выбранную пользователем
                    $sum = $sum / $currencyRates[$currencySelected];

                    // Формируем результа
                    $profit[$categoryName]['categoryID'] = $r['cid'];
                    $profit[$categoryName]['dates'][] = array (
                    'day' => $dt,
                    'account' => $accountName,
                    'sum' => $sum,
                    );

                } // foreach
            } // if count > 0
            return $profit;
        }   // if result

    } // getDetailedProfit

    /**
     * Выводит данные о расходах пользователя за период с разбивкой по категориям расходов
     * @deprecated
     * @param array $rpd Параметры отчета
     * userID - код пользователя
     * dateFrom - дата начала периода в формате дд.мм.ггг
     * dateTo - дата окончания периода в формате дд.мм.ггг
     * account - код счета
     * currency_rates - курсы валют по отношению к рублю. Хэш. Код=>Курс
     * currency - Код валюты, в которой показывать сумму
     * months - названия месяцев. Хэш. Номер=>Название
     *
     * @return array Данные о доходе. Хэш.
     * название категории => разбивка дохода по дням и счетам. Хэш
     *  дата => список счетов. Хэш
     *      счет => величина дохода в рублях?
     * @throws Exception
     * @access public
     */
    private function getDetailedLoss($rpd) {
        $userID = $rpd['userID'];
        $currencyRates = $rpd['currency_rates'];
        $currencySelected = $rpd['currency'];

        // Преобразуем даты в формат SQL
        if ($rpd['dateFrom']) {
            list($day,$month,$year) = explode(".", $rpd['dateFrom']);
            $dateFrom = "'".$year.".".$month.".".$day."'";
        }
        else $dateFrom = '(NOW() - INTERVAL 1 MONTH)';

        if ($rpd['dateTo']) {
            list($day,$month,$year) = explode(".", $rpd['dateTo']);
            $dateTo = "'".$year.".".$month.".".$day."'";
        }
        else $dateTo = 'NOW()';
        $dateQ = " AND (m.date BETWEEN $dateFrom AND $dateTo) ";

        // Если указан код счета, то делаем дополнительную фильтрацию по нему
        $accID = $rpd['account'];
        $accQ = ($accID)?" AND m.bill_id='$accID' ":'';

        // Получаем список
        $sql = "SELECT c.cat_name  as cn, m.cat_id as cid, date(date) as d,b.bill_name as bn, sum(money) as s, b.bill_currency as crn FROM money m,bill b, category c WHERE money<0 AND m.cat_id>0 AND m.user_id='$userID' AND b.bill_id=m.bill_id AND b.user_id='$userID' AND c.cat_id=m.cat_id $dateQ $accQ group by m.cat_id,date(m.date),m.bill_id";
        //pre($sql);

        $loss = array();
        if ( !($result = $this->db->sql_query($sql)) )
        {
            throw new Exception('Ошибка при получении списка доходов',1);
        }
        else
        {
            $rows = $this->db->sql_fetchrowset($result);
            //pre($rows);

            if (count($rows)>0) {
                foreach ($rows as $r) {
                    $categoryName = $r['cn'];
                    // Преобразуем дату в русский формат
                    list($year,$month,$day) = explode('-',$r['d']);
                    $dt = "$day.$month.$year";
                    $accountName = $r['bn'];
                    $sum = abs($r['s']);
                    $currencyID = $r['crn'];

                    // Переводим сумму из валюты счета в валюту, выбранную пользователем
                    // Сначала переводим из валюты счета в рубли
                    $sum = $sum * $currencyRates[$currencyID];
                    // А затем из рублей в валюту, выбранную пользователем
                    $sum = $sum / $currencyRates[$currencySelected];

                    // Формируем результа
                    $loss[$categoryName]['categoryID'] = $r['cid'];
                    $loss[$categoryName]['dates'][] = array (
                    'day' => $dt,
                    'account' => $accountName,
                    'sum' => $sum,
                    );

                } // foreach
            } // if count > 0

            return $loss;
        }   // if result

    } // getDetailedLoss

    /**
     * Выводит данные о расходах пользователя за период с разбивкой по категориям расходов
     * @deprecated
     * @param array $rpd Параметры отчета
     * userID - код пользователя
     * dateFrom - дата начала периода в формате дд.мм.ггг
     * dateTo - дата окончания периода в формате дд.мм.ггг
     * account - код счета
     * currency_rates - курсы валют по отношению к рублю. Хэш. Код=>Курс
     * currency - Код валюты, в которой показывать сумму
     * months - названия месяцев. Хэш. Номер=>Название
     * @param object Category $category Объект контроллера категорий
     *
     * @return array Данные о расходе. Хэш.
     * ключ - название категории
     * значение - величина расхода
     * @throws Exception
     * @access public
     */
    private function getLoss($rpd, $category) {
        $userID = $rpd['userID'];
        $currencyRates = $rpd['currency_rates'];
        $currencySelected = $rpd['currency'];

        if (!is_a($category,'Category')) throw new Exception('Объект Category неверного типа',1);

        $userCategories = $category->getUserCategories($userID);

        // Преобразуем даты в формат SQL
        if ($rpd['dateFrom']) {
            list($day,$month,$year) = explode(".", $rpd['dateFrom']);
            $dateFrom = "'".$year.".".$month.".".$day."'";
        }
        else $dateFrom = '(NOW() - INTERVAL 1 MONTH)';

        if ($rpd['dateTo']) {
            list($day,$month,$year) = explode(".", $rpd['dateTo']);
            $dateTo = "'".$year.".".$month.".".$day."'";
        }
        else $dateTo = 'NOW()';

        $dateQ = " AND (date BETWEEN $dateFrom AND $dateTo) ";

        // Если указан код счета, то делаем дополнительную фильтрацию по нему
        $accID = $rpd['account'];
        $accQ = ($accID)?" AND m.bill_id='$accID' ":'';

        $sql = "select count(*) as cn ,cat_id,sum(money) as s,b.bill_currency as crn from money m,bill b where b.bill_id=m.bill_id AND b.user_id='$userID' AND m.cat_id > 0 AND money<0 and m.user_id='$userID' $dateQ $accQ group by cat_id order by s";
        //pre($sql);
        $loss = array();

        if ( !($result = $this->db->sql_query($sql)) )
        {
            throw new Exception('Ошибка при получении списка расходов',1);
        }
        else
        {
            $rows = $this->db->sql_fetchrowset($result);
            //pre($rows);

            $totalSum = 0;

            if (count($rows)>0) {
                foreach ($rows as $r) {
                    $sum = abs($r['s']);
                    $catID = $r['cat_id'];


                    $currencyID = $r['crn'];
                    // Переводим сумму из валюты счета в валюту, выбранную пользователем
                    // Сначала переводим из валюты счета в рубли
                    $sum = $sum * $currencyRates[$currencyID];
                    // А затем из рублей в валюту, выбранную пользователем
                    $sum = $sum / $currencyRates[$currencySelected];

                    $totalSum += $sum;

                    $loss[$catID] = array(
                    'catName' => $userCategories[$catID],
                    'sum' => $sum,
                    'cn' => $r['cn'],
                    );

                } // while
            } // if count > 0

            // Еще раз пробегаемся по массиву, чтобы посчитать долю каждого элемент
            foreach ($loss as $catID => $catData) {
                $loss[$catID]['part'] = ($loss[$catID]['sum']*100)/$totalSum;
            } // foreach

            return $loss;
        } // if result
    } // getLiabilities

    /**
     * Выводит данные о расходах и доходах пользователя за период с разбивкой по месяцам
     * @deprecated
     * @param array $rpd Параметры отчета
     * userID - код пользователя
     * dateFrom - дата начала периода в формате дд.мм.ггг
     * dateTo - дата окончания периода в формате дд.мм.ггг
     * account - код счета
     * currency_rates - курсы валют по отношению к рублю. Хэш. Код=>Курс
     * currency - Код валюты, в которой показывать сумму
     * months - названия месяцев. Хэш. Номер=>Название
     *
     * @return array Данные о расходах и доходах. Хэш
     * profit
     *  "название месяца,номер года" => величина дохода
     * loss
     *  "название месяца,номер года" => величина расхода
     *
     * Количество ключей "месяц, номер года" у profit & loss должно совпадать
     * @throws Exception
     * @access public
     */
    private  function getProfitAndLoss($rpd) {
        $userID = $rpd['userID'];
        $currencyRates = $rpd['currency_rates'];
        $currencySelected = $rpd['currency'];

        // Преобразуем даты в формат SQL
        if ($rpd['dateFrom']) {
            $arDateFrom = list($day,$month,$year) = explode(".", $rpd['dateFrom']);
            $dateFrom = "'".$year.".".$month.".".$day."'";
        }
        else $dateFrom = '(NOW() - INTERVAL 2 MONTH)';

        if ($rpd['dateTo']) {
            $arDateTo = list($day,$month,$year) = explode(".", $rpd['dateTo']);
            $dateTo = "'".$year.".".$month.".".$day."'";
        }
        else $dateTo = 'NOW()';

        // Готовим забитый нулями интервал годов и месяцев
        $intervalProfit = $this->_prepareInterval($arDateFrom,$arDateTo);
        $intervalLoss = $intervalProfit;
        //pre($interval);

        $dateQ = " AND (date BETWEEN $dateFrom AND $dateTo) ";

        // Если указан код счета, то делаем дополнительную фильтрацию по нему
        $accID = $rpd['account'];
        $accQ = ($accID)?" AND m.bill_id='$accID' ":'';

        // Считаем доходы
        $sql = "select YEAR(date) as y,MONTH(date) as m,sum(money) as sm,b.bill_currency as crn from money m,bill b where b.bill_id=m.bill_id AND b.user_id='$userID' AND money>=0 and cat_id>0 and m.user_id='$userID' $dateQ $accQ  group by YEAR(date),MONTH(date) order by YEAR(date),MONTH(date)";
        //pre($sql);


        if ( !($result = $this->db->sql_query($sql)) )
        {
            throw new Exception('Ошибка при получении списка доходов',1);
        }
        else
        {
            $rows = $this->db->sql_fetchrowset($result);
            //pre($rows);

            if (count($rows)>0) {
                foreach ($rows as $r) {
                    $year = $r['y'];
                    $month = $r['m'];
                    $sum = abs($r['sm']);

                    $currencyID = $r['crn'];
                    // Переводим сумму из валюты счета в валюту, выбранную пользователем
                    // Сначала переводим из валюты счета в рубли
                    $sum = $sum * $currencyRates[$currencyID];
                    // А затем из рублей в валюту, выбранную пользователем
                    $sum = $sum / $currencyRates[$currencySelected];
                    $intervalProfit[$year][$month] = $sum;
                } // foreach
            } // if count > 0
        }

        //pre($profit);

        // Считаем расход
        $sql = "select YEAR(date) as y,MONTH(date) as m,sum(money) as sm,b.bill_currency as crn  from money m, bill b where b.bill_id=m.bill_id AND b.user_id='$userID' AND money<0 and cat_id>0 and m.user_id='$userID' $dateQ $accQ group by YEAR(date),MONTH(date) order by YEAR(date),MONTH(date)";
        //pre($sql);





        if ( !($result = $this->db->sql_query($sql)) )
        {
            throw new Exception('Ошибка при получении списка доходов',1);
        }
        else
        {
            $rows = $this->db->sql_fetchrowset($result);
            //pre($rows);

            if (count($rows)>0) {
                foreach ($rows as $r) {
                    $year = $r['y'];
                    $month = $r['m'];
                    $sum = abs($r['sm']);
                    $currencyID = $r['crn'];
                    // Переводим сумму из валюты счета в валюту, выбранную пользователем
                    // Сначала переводим из валюты счета в рубли
                    $sum = $sum * $currencyRates[$currencyID];
                    // А затем из рублей в валюту, выбранную пользователем
                    $sum = $sum / $currencyRates[$currencySelected];

                    $intervalLoss[$year][$month] = $sum;
                } // foreach
            } // if count > 0
        }

        $profit = $this->_transformInterval($intervalProfit);
        $loss = $this->_transformInterval($intervalLoss);
        //pre($profit);
        //pre($loss);

        $res['profit'] = $profit;
        $res['loss'] = $loss;

        // Пример правильного результата
        /*
        $res = array(
        'profit'=> array(
        'Апрель, 2008' => 131,
        'Май, 2008' => 235,
        'Июнь, 2008' => 123
        ),
        'loss'=> array(
        'Апрель, 2008' => 342,
        'Май, 2008' => 586,
        'Июнь, 2008' => 162
        ),
        );
        */
        return $res;
    } // getProfitAndLoss
}