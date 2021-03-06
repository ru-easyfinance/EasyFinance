<?php

/**
 * Класс модели для управления календарём
 * @category targets
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
if ( get_magic_quotes_gpc() )
{
    $_GET     = stripslashes_deep($_GET);
    $_POST    = stripslashes_deep($_POST);
    $_COOKIE  = stripslashes_deep($_COOKIE);
    $_REQUEST = stripslashes_deep($_REQUEST);
    $_SESSION = stripslashes_deep($_SESSION);
    $_SERVER  = stripslashes_deep($_SERVER);
    $_FILES   = stripslashes_deep($_FILES);
    $_ENV     = stripslashes_deep($_ENV);
}

function stripslashes_deep($value)
{
    if( is_array($value) )
    {
      $value = array_map('stripslashes_deep', $value);
    }
    elseif ( !empty($value) && is_string($value) )
    {
      $value = stripslashes($value);
    }
    return $value;
}

class Targets_Model {

    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql $db
     */
    private $db = NULL;

     /**
     * Ссылка на экземпляр класса пользователя
     * @var User
     */
    private $user = null;

    /**
     * Массив со ссылками на ошибки. Ключ - имя поля, значение массив текста ошибки
     * @example array('date'=>array('Не указана дата'), 'time'=> array('Не указано время'));
     * @var array
     */
    private $errorData = array();

    /**
     * Правка ошибок
     * @var array
     */
    private $errors = array();

    /**
     * Количество целей, показываемых на отдельной странице
     * @var int
     */
    private $limitFull = 10;

    /**
     * Количество целей, показываемых на совмёщённой странице
     * @var int
     */
    private $limitCommon = 10;


    /**
     * Конструктор
     * @return void
     */
    function  __construct() {
        $this->db = Core::getInstance()->db;
        $this->user = Core::getInstance()->user;
    }

    /**
     * Возвращает массив из ближайших к окончанию целей
     * @param int $index С какой цели начать возвращать
     * @param int $limit Количество лимитируемых записей возвращаемых из БД
     * @return array mixed
     */
    public function getLastList($index = 0, $limit = 0) {
        if ((int)$limit == 0) {
            $limit = $this->limitCommon;
        }

        if ((int)$index == 0) {
            $start = 0;
        } else {
            $start = (((int)$index - 1) * $limit);
        }

        $list = $this->db->selectPage( $total, "SELECT
                t.id,
                t.category_id as category,
                t.title,
                t.amount,
                DATE_FORMAT(t.date_begin,'%d.%m.%Y') as start,
                DATE_FORMAT(t.date_end,'%d.%m.%Y') as end,
                t.percent_done,
                t.forecast_done,
                t.visible,
                t.photo,t.url,
                t.comment,
                t.target_account_id AS account,
                t.amount_done,
                t.close,
                t.done as done
                ,(
                    SELECT b.money
                    FROM target_bill b
                    WHERE b.target_id = t.id AND b.accepted=1 ORDER BY b.dt_create ASC LIMIT 1) AS money
            FROM target t
            WHERE t.user_id = ? ORDER BY t.date_end ASC ;",
            Core::getInstance()->user->getId() );
        if (!is_array($list)) $list = array();//*/

        return $list;
    }

    /**
     * Возвращает массив из пяти популярных целей
     * @param int $index С какой цели возвращать
     * @return array mixed
     */
    public function getPopList($index = 0) {
        $limit = $start = 0;

        if ((int)$index == 0) {
            $limit = $this->limitCommon;
            $start = 0;
        } else {
            $limit = $this->limitFull;
            $start = (((int)$index ) * $limit);//-1
        }
        $list = $this->db->selectPage($total, "
            SELECT t.category_id AS category, COUNT(t.id) AS cnt, SUM(`close`) AS cl,
             CASE t.category_id
              WHEN 1 THEN 'Квартира'
              WHEN 2 THEN 'Автомобиль'
              WHEN 3 THEN 'Отпуск'
              WHEN 4 THEN 'Финансовая подушка'
              WHEN 5 THEN 'Другое'
              WHEN 6 THEN 'Свадьба'
              WHEN 7 THEN 'Бытовая техника'
              WHEN 8 THEN 'Компьютер'
             END AS title
            FROM target t
            WHERE t.category_id BETWEEN 1 AND 8
            GROUP BY category
            ORDER BY cnt DESC;
        ", $start, $limit);
        $array = array();
        foreach ($list as $k => $v) {
            //@FIXME Дописать работу с системными категориями
            $array[] = array(
                'cat_id' => $v['category'],//category_id
                'title'    => $v['title'],
                'count'    => $v['cnt'],
                'cat_name' => 'АБА-ХАБА',
                'cl' => $v['cl']
            );
        }
        return array(
            'options'=>array(
                'total'=>$total,
                'index'=>$index,
                'limit'=>$limit),
            'list' => $array
        );
    }

    public function getClosedList(){
        $sql = "SELECT id, title, category_id, amount_done, target_account_id
            FROM target
            WHERE close=1 AND done=0 AND user_id=?";
        $result = $this->db->select($sql, Core::getInstance()->user->getId());

        return $result;
    }


    /**
     * Закрывает финцель
     *
     * @param int   $targetId
     * @param int   $targetCat
     * @param float $amount
     * @param int   $account
     * @return int ИД расходной операции
     */
    public function CloseOp($targetId = 0, $targetCat = 0, $amount = 0, $account = 0) {
        // Обновляем данные финцели
        $userId   = Core::getInstance()->user->getId();
        $sql      = "UPDATE target SET done=1 WHERE id = ? AND user_id = ?";
        $result   = $this->db->select($sql, $targetId, $userId);

        $sql      = "SELECT * FROM target WHERE id = ? AND user_id = ?";
        $target   = $this->db->select($sql, $targetId, $userId);
        $title    = addslashes($target[0]['title']);
        $comment  = "Закрытие финансовой цели \'$title\'";

        $category = null;
        $date = date('Y-m-d');

        // Делаем фактическую операцию перевода на финцель.
        $operation   = new Operation_Model(Core::getInstance()->user);
        $operationId = $operation->add(0, -$amount, $date,
            $category, $comment, $account, null, false);

        return $operation->getOperation($userId, $operationId);
    }


    public function countTargetsOnAccount($acc_id){
        $sql = "SELECT count(*) AS co
            FROM target
            WHERE target_account_id=? AND user_id=?";
        $result = $this->db->select($sql, $acc_id, Core::getInstance()->user->getId());
        return $result[0]['co'];
    }
    public function countClose(){
        $sql = "SELECT count(*) AS co
            FROM target
            WHERE close=1 AND done=1 AND user_id=?";
        $result = $this->db->select($sql, Core::getInstance()->user->getId());

        return $result[0]['co'];
    }
    /**
     * Возвращает массив с содержимым финансовой цели
     * @param int $id
     * @return array  mixed
     */
    function getTarget ($id)
    {
        $sql = "SELECT id, category_id as category, title, type, amount,
            DATE_FORMAT(date_begin, '%d.%m.%Y') as start, DATE_FORMAT(date_end, '%d.%m.%Y') as end,
            visible, photo, url, comment, target_account_id as account
            FROM target WHERE id = ?";
        $a = $this->db->selectRow($sql, $id);
            $this->staticTargetUpdate($a['id']);
        return $this->db->selectRow($sql, $id);
    }

    /**
     * Проверяет данные и возвращает ассоциативный массив, если успешно. False - при ошибке
     * @return array mixed
     */
    public function checkData() {
        $data = array();
        $data['id'] = (int)@$_POST['id'];

        if (@$_POST['type'] == 'd') {
            $data['type'] = 'd';
        } else {
            $data['type'] = 'r';
        }

        $data['category'] = (int)@$_POST['category'];
        if ($data['category'] == 0) {
            $this->errorData['category'] = "Категория цели";
        }

        $data['title'] = htmlspecialchars(@$_POST['title']);
        if (empty($data['title'])) {
            $this->errorData['title'] = "Наименование цели";
        }

        if (is_numeric((float)$_POST['amount'])) {
            $data['amount'] = (float)$_POST['amount'];
        } else {
            $data['amount'] = 0;
        }

        if (is_numeric((float)$_POST['money'])) {
            $data['money'] = (float)$_POST['money'];
        } else {
            $data['money'] = 0;
        }

        $data['start'] = formatRussianDate2MysqlDate(@$_POST['start']);
        if (!$data['start']) {
            $this->errorData['start'] = "Дата начала";
        }

        $data['end'] = formatRussianDate2MysqlDate(@$_POST['end']);
        if (!$data['end']) {
            $this->errorData['end'] = "Дата окончания";
        }

        if ( strtotime( $data['start'] . ' 00:00:00') > strtotime( $data['end'] . ' 00:00:00') ) {
            $this->errorData['end'] = "Неверно указана дата окончания";
        }

        $data['photo']   = htmlspecialchars(@$_POST['photo']);
        $data['url']     = htmlspecialchars(@$_POST['url']);
        $data['comment'] = htmlspecialchars(@$_POST['comment']);
        $data['account'] = (int)@$_POST['account'];
        //$data['comment'] = htmlspecialchars( $data['comment'] , ENT_NOQUOTES);
        //$data['title'] = htmlspecialchars( $data['title'] , ENT_NOQUOTES);

        if ((int)$_POST['visible']){
            $data['visible'] = 1;
        }else{
            $data['visible'] = 0;
        }

        return $data;
    }

    /**
     * Добавляем новую цель
     * @return
     */
    function add() {
        $data = array();
        if (isset($_POST['title'])) {
            $data = $this->checkData();

            // Если есть ошибки, или не все обязательные поля заполнены
            if (count($this->errorData) > 0) {
                return json_encode($this->errorData);
            // Добавляем цель в БД
            } else {
                $this->db->query("INSERT INTO target(`user_id`, `category_id`, `title`, `type`,
                    `amount`,`date_begin`, `date_end`, `target_account_id`, `visible`,
                    `photo`,`url`, `comment`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
                    Core::getInstance()->user->getId(), $data['category'], $data['title'], $data['type'],
                    $data['amount'], $data['start'] , $data['end'], $data['account'], $data['visible'],
                    $data['photo'], $data['url'], $data['comment']);
                $tid = mysql_insert_id();
                //$this->addTargetOperation($data['account'], $tid, $data['money'], 'Начальный баланс', date('Y-m-d'), 0);
                $this->staticTargetUpdate($tid);
                Core::getInstance()->user->initUserTargets();
                Core::getInstance()->user->save();
                return Core::getInstance()->user->getUserTargets();
            }
        }
    }

    /**
     * Редактируем событие
     * @return json Массив в формате json. Если пустой, значит успешно отредактировано, если со
     * значениями - значит ошибка. И в них содержится информация о том, что введено не верно.
     */
    function edit() {
        $data = $this->checkData();
        // Если у нас есть ошибки при заполнении формы
        if (count($this->errors) > 0) {
            return json_encode($this->errors);
        } else {
            $this->db->query("UPDATE target SET
                `category_id`=?, `title`=?, `type`=?, `amount`=?, `date_begin`=?, `date_end`=?,
                `visible`=?, `photo`=?, `url`=?, `comment`=? WHERE user_id=? AND id=?;",
                $data['category'], $data['title'], $data['type'], $data['amount'], $data['start'],
                $data['end'], $data['visible'], $data['photo'], $data['url'], $data['comment'],
                Core::getInstance()->user->getId(), $data['id']);
            //$this->editTargetOperation(0, $data['account'], $data['id'], $data['money'], $data['comment'], date('Y-m-d'), $data['close']);
            $this->staticTargetUpdate($data['id']);
            Core::getInstance()->user->initUserTargets();
            Core::getInstance()->user->save();
            //Core::getInstance()->user->getUserTargets();
            return Core::getInstance()->user->getUserTargets();
        }
    }

    /**
     * Удаляет указанное событие
     * @return json Массив в формате json. Если пустой, значит успешно удалено, если со значениями -
     * значит ошибка. И в них содержится ошибка.
     */
    function del($id=0) {
        //$id    = (int)@$_POST['id'];
        $this->db->query("DELETE FROM target WHERE id=? AND user_id=?", $id, Core::getInstance()->user->getId());
        Core::getInstance()->user->initUserTargets();
        Core::getInstance()->user->save();
        return true;
    }

    /**
     * Обновляет статистику для указанной финансовой цели, или указанного или текущего пользователя
     * @param $target_id int
     * @param $user_id string
     * @return bool
     */
    function staticTargetUpdate ($target_id = 0, $user_id = 0) {
        if ( ( int ) $target_id == 0 ) {
            trigger_error( "Для обновления статистики указана не существующая цель", E_USER_WARNING );
            return false;
        }

        if ( ( int ) $user_id == 0 ) {
            $user_id = Core::getInstance()->user->getId();
        }

        $this->db->query("UPDATE target SET
            amount_done   = IFNULL((SELECT SUM(money) FROM target_bill WHERE target_id = target.id AND accepted=1 LIMIT 1), 0)
            , percent_done  = IFNULL(ROUND(amount_done / (amount / 100), 2),0)
            , forecast_done = IFNULL(ROUND((DATEDIFF(ADDDATE(date_begin,(amount / (amount_done / DATEDIFF(CURRENT_DATE(), date_begin)))), date_begin) / DATEDIFF(date_end, date_begin)) * 100, 2),0)
            WHERE user_id=? AND id=? ", $user_id, $target_id);
        return true;
    }

    /**
     * Добавляет массив с финцелями
     * @param Calendar_Event $event
     * @return bool
     */
    public function addSomeTargetOperation( $operations_array ) {

        $targets = Core::getInstance()->user->getUserTargets();
        $operations = New Operation_model;

        foreach ( $operations_array as $value ) {

            // Если счёт финцели = счёту операции
            if ( $targets['user_targets'][$value['target']]['account'] == $value['account'] ) {

                $sql = "INSERT INTO target_bill ( `bill_id`, `target_id`, `user_id`, `money`, `comment`,
                    `date`, `chain_id`, `accepted`, `dt_create` ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ? )";

                $this->db->query( $sql,
                    $value['account'],
                    $value['toAccount'],
                    Core::getInstance()->user->getId(),
                    $value['amount'],
                    strip_tags( $value['comment'] ),
                    $value['date'],
                    $value['chain'],
                    $value['accepted'],
                    date('Y-m-d H:i:s')
                );

            } else {
                // Добавляем перевод с указанного счёта на счёт финцели
                $transfer_array = array( array (

                    'account'    => $value['account'],
                    'amount'     => $value['amount'],
                    'category'   => $value['category'],
                    'date'       => $value['date'],
                    'comment'    => 'Перевод на счёт финцели',
                    'tags'       => null,
                    'convert'    => $value['convert'],
                    'currency'   => $value['currency'],
                    'toAccount'  => $value['toAccount'],
                    'target'     => $value['target'],
                    'accepted'   => 0,
                    'chain'      => $value['chain'],

                ) );

                $operations->addSomeTransfer( $transfer_array );

                $sql = "INSERT INTO target_bill
                    ( `bill_id`, `target_id`, `user_id`, `money`, `comment`, `date`, `chain_id`, `accepted`, `dt_create` )
                    VALUES ( ?, ?, ?, ?, ?, ? , ?, ?, ?);";

                //Добавим перевод на фин цель со счёта фин цели
                $this->db->query (
                    $sql,
                    $value['account'],
                    $value['target'],
                    Core::getInstance()->user->getId(),
                    $value['amount'],
                    $value['comment'],
                    $value['date'],
                    $value['chain'],
                    $value['accepted'],
                    date('Y-m-d H:i:s')
                );

            }

            // Если было сказано что нужно закрыть, закрываем финцель
            if ( $value['close'] == 1 ) {

                $this->db->query( "UPDATE target SET close=1 WHERE user_id=? AND id=?",
                    Core::getInstance()->user->getId(), $value['target'] );

            }

            // Обновляем статистику переводов на финцель
            $this->staticTargetUpdate( $value['target'] );

        }
    }

    /**
     * Пополняет финансовую цель, переводя в резерв субсчёта из основного счёта
     *
     * @param $accountId int Ид счёта
     * @param $targetId int Ид фин.цели
     * @param $user_id string Ид пользователя
     * @param $money float
     * @param $dt date
     * @return bool
     */
    public function addTargetOperation($accountId, $targetId, $money, $comment, $date, $close) {

        $targetAccountId = $this->db->selectCell("SELECT target_account_id FROM `target` WHERE id = ? ", $targetId);
        $userId = Core::getInstance()->user->getId();

        // Если счёт финцели равен текущему счёту
        if ($targetAccountId == $accountId) {

            $sql = "INSERT INTO
                        target_bill (`bill_id`, `target_id`, `user_id`, `money`, `comment`, `date`, `dt_create`)
                    VALUES ( ?, ?, ?, ?, ?, ?, ?);";

            $this->db->query($sql, $accountId, $targetId, $userId, $money, strip_tags($comment), $date, date('Y-m-d H:i:s'));

            if ($close == 1) {
                $sql = "UPDATE target SET close=1 WHERE user_id=? AND id=?";
                $this->db->query($sql, $userId, $targetId);
            }

            // Обновляем статистику по финцели

            return $this->staticTargetUpdate($targetId);

        } else {

            $mod = New Operation_model;
            $mod->addTransfer($money, 0, 0, $date, $accountId, $targetAccountId, 'Перевод на счёт финцели');

            $convert = $this->_convertAmount($accountId, $targetAccountId, $money, 0);

            //а теперь добавим перевод на фин цель со счёта фин цели!
            $this->db->query("INSERT INTO target_bill (`bill_id`, `target_id`, `user_id`, `money`, `dt_create`, `comment`, `date`)
                VALUES(?,?,?,?,?,?,?);",$targetAccountId, $targetId, Core::getInstance()->user->getId(), $convert, date('Y-m-d H:i:s'), $comment, $date);
            if (!empty($close)) {
                $this->db->query("UPDATE target SET close=1 WHERE user_id=? AND id=?", Core::getInstance()->user->getId(), $targetId);
            }

            return $this->staticTargetUpdate($targetId);
        }
    }

    /**
     * Редактирует финансовую операцию
     * @param $target_bill_id int Ид операции фин.цели
     * @param $bill_id int Ид счёта
     * @param $target_id int ИД фин.цели
     * @param $money decimal(10,2) Деньги
     * @param $comment string Комментарий
     * @return bool
     */
    //$array['id'], $array['amount'], $array['date'], $array['target'],$array['account'], /*$array['id'],*/  $array['comment'], $array['close']
    public function editTargetOperation($target_bill_id, $money, $date, $target_id, $bill_id, $comment,  $close) {
        if ((int)$target_bill_id == 0) {
            $sql = "SELECT b.id FROM target_bill b WHERE b.target_id =? ORDER BY b.dt_create ASC LIMIT 1";
            $target_bill_id = $this->db->selectCell($sql, $target_id);
        }

        if ((int)$bill_id == 0){
            trigger_error("Указанный счёт '{$bill_id}' не существует. ", E_USER_ERROR);
            return false;
        }
        if ((int)$target_id == 0) {
            trigger_error("Указанная финансовая цель '{$target_id}' не существует. ", E_USER_ERROR);
            return false;
        }
        if (!is_float($money) && (int)$money == 0 ) {
            trigger_error("Указано не число", E_USER_WARNING);
            return false;
        }
        $comment = strip_tags($comment);
        //$date = formatRussianDate2MysqlDate($date);
        $this->db->query("UPDATE target_bill SET bill_id=?, money=?, date=?, comment=?
            WHERE id=? AND user_id=? LIMIT 1;", $bill_id, $money, $date, $comment, $target_bill_id, Core::getInstance()->user->getId());
        if (!empty($close)) {
            $this->db->query("UPDATE target SET close=1 WHERE user_id=? AND id=?", Core::getInstance()->user->getId(), $target_id);
        }
        $this->staticTargetUpdate($target_id);
        return true;
    }

    /**
     * Удаляет цель пользователя и все привязанные к ней операции
     * @param $target_id int
     * @return bool
     */
    public function delTarget($target_id = 0) {
        $this->db->query("DELETE FROM target WHERE user_id=? AND id=?",
            Core::getInstance()->user->getId(), $target_id);
        $this->db->query("DELETE FROM target_bill WHERE user_id=? AND target_id=?;",
            Core::getInstance()->user->getId(), $target_id);
        return true;
    }

    /**
     * Удаляет операцию финансовой цели
     * @param $tar_oper_id int Ид операции
     * @param $tr_id int Ид финансовой цели
     * @return bool
     */
    public function delTargetOperation($tar_oper_id = 0, $tr_id = 0) {
        $this->db->query("DELETE FROM target_bill WHERE user_id=? AND id=?;", Core::getInstance()->user->getId(), $tar_oper_id);
        return $this->staticTargetUpdate($tr_id);
    }

    /**
     * Возвращает операцию по финансовой цели из базы данных
     * @param $target_id int
     * @return array mixed
     */
    public function getTargetOperation($target_id) {
        return $this->db->selectRow("SELECT tb.id, tb.user_id, tb.money, t.category_id as cat_id, '' as transfer,
            DATE_FORMAT(tb.date,'%d.%m.%Y') as date, t.id as tr_id, tb.bill_id, '' as drain, tb.comment as comment,
            t.title as title, t.close
        FROM `target_bill` tb
        LEFT JOIN target t on tb.target_id = t.id
        WHERE tb.id = ? AND tb.`user_id` = ?", $target_id, Core::getInstance()->user->getId());
    }

    /**
     * Конвертирует сумму операции
     *
     * @param   int   $fromAccount
     * @param   int   $toAccount
     * @param   float $amount
     * @param   float $convert
     * @return  float
     */
    function _convertAmount($fromAccount, $toAccount, $amount, $convert)
    {
        $accounts    = $this->user->getUserAccounts();

        $curFromId   = $accounts[$fromAccount]['account_currency_id'];
        $curTargetId = $accounts[$toAccount]['account_currency_id'];

        // Если перевод мультивалютный
        if ($curFromId != $curTargetId) {

            // Если не указана сконвертированная сумма (в ПДА такое может быть)
            if ($convert == 0) {

                $currensys = $this->user->getUserCurrency();

                // приводим сумму к пром. валюте
                $convert = $amount / $currensys[$curTargetId]['value'];
                // .. и к валюте целевого счёта
                $convert = $convert * $currensys[$curFromId]['value'];
            }
        }

        if ($convert == 0) {
            $convert = $amount;
        }

        return abs($convert);
    }
}
