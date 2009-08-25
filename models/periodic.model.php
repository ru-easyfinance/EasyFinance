<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Модель для управления периодическими транзакциями
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @category periodic
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Periodic_Model
{

    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    private $db = null;

    /**
     * Массив с ошибками
     * @var array mixed
     */
    public $error = null;

    /**
     * Конструктор
     * @return void
     */
    function __construct()
    {
        $this->db = Core::getInstance()->db;
        $this->user = Core::getInstance()->user;
    }

    /**
     * Возвращает массив со всеми периодическими транзакциями пользователя
     * @return array mixed
     */
    function getList()
    {
        $sql = "SELECT id, category, account, drain, title, DATE_FORMAT(date,'%d.%m.%Y') AS `date`,
                    `amount`, type_repeat AS `repeat`, count_repeat AS `counts`, `comment`,
                    `infinity` FROM periodic  WHERE user_id = ?";
        $array = $this->db->select($sql, Core::getInstance()->user->getId());
        $ret = array();
        foreach ($array as $val) {
            if ($val['drain'] == 1) {
                $type = -1;
            } else {
                $type = 0;
            }
            $ret[$val['id']] = array(
                'id'        => $val['id'],
                'category'  => $val['category'],
                'account'   => $val['account'],
                'type'      => $type,
                'title'     => $val['title'],
                'date'      => $val['date'],
                'amount'    => $val['amount'],
                'repeat'    => $val['repeat'],
                'counts'    => $val['counts'],
                'comment'   => $val['comment'],
                'infinity'  => $val['infinity']
            );
        }
        return $ret;
    }

    /**
     * Добавляет новую периодическую транзакцию
     * @return void
     */
    function add($account, $amount, $category, $comment, $counts, $date, $infinity, $repeat, $title, $drain)
    {
        $sql = "INSERT periodic(user_id, category, account, drain, title, date, amount, type_repeat,
            count_repeat, comment, dt_create, infinity) VALUES(?,?,?,?,?,?,?,?,?,?,NOW(),?)";
        $this->db->query($sql, Core::getInstance()->user->getId(), $category, $account, $drain,
            $title,$date, $amount, $repeat, $counts, $comment, $infinity);

        return array();
    }

    /**
     * Удаляет периодическую транзакцию
     * @param int $id
     */
    function del($id = 0) 
    {
        $sql = "DELETE FROM periodic WHERE user_id=? AND id=?";
        $this->db->query($sql, Core::getInstance()->user->getId(), $id);
        // @FIXME Дописать удаление транзакций из календаря
        return array();
    }

    /**
     * Редактирует периодическую транзакцию
     * @return void
     */
    function edit($id)
    {
        $periodic = array();
        $periodic['comment']    = htmlspecialchars($_POST['periodic']['comment']);
        $periodic['date_from']  = formatRussianDate2MysqlDate($_POST['periodic']['date_from']);
        $periodic['drain']      = (int)$_POST['periodic']['drain'];
        $periodic['cat_id']     = (int)$_POST['periodic']['cat_id'];
        $periodic['bill_id']    = (int)$_POST['periodic']['bill_id'];
        $periodic['remind']     = (int)$_POST['periodic']['remind'];
        $periodic['remind_num'] = (int)$_POST['periodic']['remind_num'];
        $periodic['period']     = (int)$_POST['periodic']['period'];
        $periodic['povtor']     = (int)$_POST['periodic']['povtor'];
        $periodic['povtor_num'] = (int)$_POST['periodic']['povtor_num'];
        $periodic['insert']     = htmlspecialchars($_POST['periodic']['insert']);

        if ($periodic['povtor'] === -1) {
            $periodic['povtor_num'] = '0';
        }

        if ($periodic['drain'] === 1) {
            $p_periodic['money'] = abs($periodic['money']) * -1; 
        }

        if($this->updatePeriodic($periodic)) {
            $_SESSION['good_text'] = "Регулярная транзакция изменена!";
            header("Location: /periodic/");
        }
    }

    /**
     * Проверяет корректность данных
     * @return array mixed
     */
    function checkData()
    {
        $this->error = array();
        $array = array();

        $array['account'] = abs((int)@$_POST['account']);
        if ($array['account'] <= 0) {
            $this->error['account'] = 'Необходимо указать счёт для транзакции';
        }

        $array['amount'] = abs((int)@$_POST['amount']);
        if ($array['amount'] == 0) {
            $this->error['amount'] = 'Указанная сумма не должна быть нулём';
        }

        $array['category'] = abs((int)@$_POST['category']);
        if ($array['category'] == 0) {
            $this->error['category'] = 'Необходимо указать категорию';
        }

        $array['comment'] = htmlspecialchars(@$_POST['comment']);

        $array['counts'] = abs((int)@$_POST['counts']);

        $array['date'] = formatRussianDate2MysqlDate(@$_POST['date']);
        if (!$array['date']) {
            $this->error['date'] = 'Необходимо правильно указать дату';
        }

        if (abs((int)@$_POST['infinity']) == 0) {
            $array['infinity'] = 1;
        } else {
            $array['infinity'] = 0;
        }
        
        $array['repeat'] = abs((int)@$_POST['repeat']);

        $array['title'] = htmlspecialchars(@$_POST['title']);
        if (empty($array['title'])) {
            $this->error['title'] = 'Необходимо заполнить заголовок';
        }

        if ((int)@$_POST['type'] < 0) {
            $array['drain'] = 1;
        } else {
            $array['drain'] = 0;
        }
        return $array;
    }

    /**
     * Сохраняет периодическую транзакцию
     * @param array $data mixed
     * @return bool
     */
    function savePeriodic($data)
    {
        if (!is_array($data)) {
            return false;
        }

        $sql = "INSERT INTO periodic (user_id, bill_id, cat_id, money, drain, date_from, period, povtor,
            povtor_num, `insert`, remind, remind_num, comment) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->db->query($sql, $this->user->getId(), $data['bill_id'], $data['cat_id'], $data['money'],
            $data['drain'], $data['date_from'], $data['period'],$data['povtor'], $data['povtor_num'],
            $data['insert'], $data['remind'], $data['remind_num'], $data['comment']);
        return true;
    }

    /**
     * Возвращает периодические транзакции
     * @return array mixed
     */
    function getPeriodic()
    {
        $sql = "SELECT * FROM periodic WHERE user_id = ? and date_from <= CURDATE()";
        $rows = $this->db->select($sql, $this->user->getId());
        return $rows;
    }

    /**
     *
     * @return unknown_type
     */
    function getInsertPeriodic($id)
    {
        //FIXME переписать функцию, слишком мутная
        $this->user_id = $id;
        $date = date("Y-m-d");
        $user_oper = $this->getPeriodic();
        if (is_array($user_oper) && count($user_oper) > 0) {
            $j = 0;
            $k = 0;
            $c = 0;
            $m = 0;
            foreach ($user_oper as $val) {
                if ($val['povtor'] == '1') {
                    $k = $val['povtor_num'];
                    $any = false;
                } else {
                    $val['povtor_num'] = $this->foundPovtor($val['date_from'], $date, $val['period'], 0);
                    $any = true;
                }

                $tmp_date = $val['date_from'];

                for ($j=0; $j<$val['povtor_num']; $j++) {
                    if ($val['period'] == '1') {
                        $tmp_date = $this->next_1day($tmp_date);
                    }
                    if ($val['period'] == '7') {
                        $tmp_date = $this->next_7day($tmp_date);
                    }
                    if ($val['period'] == '30') {
                        $tmp_date = $this->next_1month($tmp_date);
                    }
                    if ($val['period'] == '90') {
                        $tmp_date = $this->next_3month($tmp_date);
                    }
                    if ($j == 0 && $any == false) {
                        $tmp_date = $val['date_from'];
                    }

                    if ($tmp_date <= $date) {
                        $c++;
                        $periodic[$c]['bill_id'] = $val['bill_id'];
                        $periodic[$c]['cat_id'] = $val['cat_id'];
                        $periodic[$c]['money'] = $val['money'];
                        $periodic[$c]['drain'] = $val['drain'];
                        $periodic[$c]['date'] = $tmp_date;
                        if ($val['povtor'] == '1') {
                            $k--;
                            if ($k == '0') {
                                $m++;
                                $updatePeriodic[$m]['date'] = $tmp_date;
                                $updatePeriodic[$m]['povtor_num'] = $k;
                                $updatePeriodic[$m]['id'] = $val['id'];
                            }
                        } else {
                            $k=0;
                        }
                    } else {
                        $m++;
                        $updatePeriodic[$m]['date'] = $tmp_date;
                        $updatePeriodic[$m]['povtor_num'] = $k;
                        $updatePeriodic[$m]['id'] = $val['id'];
                        $j = $val['povtor_num'];
                    }
                }
            }
        }

        if (!empty($periodic)) {
            //TODO Проверить что работает PREPARE/EXECUTE, иначе собрать единичный запрос вручную
            foreach ($periodic as $val) {
                $sql = "INSERT INTO money (user_id, money, `date`, cat_id, bill_id, drain, comment)
                    VALUES (?,?,?,?,?,?,?);";
                $this->db->query($sql, $this->user->getId(), $val['money'], $val['date'],
                    $val['cat_id'],$val['bill_id'], $val['drain'], $val['comment']);
            }
        }

        if (!empty($updatePeriodic)) {
            foreach ($updatePeriodic as $val) {
                $sql = "UPDATE periodic SET date_from = ?, povtor_num = ?
                    WHERE id = ? AND user_id = ?";
                $this->db->query($sql, $val['date'], $val['povtor_num'], $val['id'], $this->user->getId());
            }
        }
    }

    function next_1day($date)
    {
        list($year,$month,$day) = explode("-", $date);
        //$result  = mktime(0, 0, 0, date("m", $date),date("d", $date)+1, date("Y", $date));
        $result  = mktime(0, 0, 0, $month, $day+1, $year);
        return date("Y-m-d", $result);
    }

    function next_7day($date)
    {
        list($year,$month,$day) = explode("-", $date);

        $result  = mktime(0, 0, 0, $month, $day+7, $year);
        //date("m",mktime(0, 0, 0, $month-1, date("d"), $year));

        return date("Y-m-d", $result);
    }

    function next_1month($date)
    {
        list($year,$month,$day) = explode("-", $date);
        //$result = mktime(0, 0, 0, date("m", $date)+1, date("d", $date),   date("Y", $date));
        $result  = mktime(0, 0, 0, $month+1, $day, $year);
        return date("Y-m-d", $result);
    }

    function next_3month($date)
    {
        list($year,$month,$day) = explode("-", $date);
        //$result = mktime(0, 0, 0, date("m", $date)+3, date("d", $date),   date("Y", $date));
        $result  = mktime(0, 0, 0, $month+3, $day, $year);
        return date("Y-m-d", $result);
    }

    /**
     * Ищет следующую дату повторения транзакции
     * FIXME срочно переписать, подобной разманной по исходнику дури я давно не видел, использовать ADDDATE или что-то подобное
     * @param $date_from
     * @param $date_now
     * @param $period
     * @param $i
     * @return unknown_type
     */
    function foundPovtor($date_from, $date_now, $period, $i)
    {
        $i++;
        switch ($period) {
            case 1:
                $date_from = $this->next_1day($date_from);
                break;
            case 7:
                $date_from = $this->next_7day($date_from);
                break;
            case 30:
                $date_from = $this->next_1month($date_from);
                break;
            case 90:
                $date_from = $this->next_3month($date_from);
                break;
        }

        if ($date_from <= $date_now) {
            return $this->foundPovtor($date_from, $date_now, $period, $i);
        }else{
            return $i;
        }
    }

    function date_to_int($date)
    {
        return mktime(0, 0, 0, $date[3].$date[4], $date[0].$date[1], $date[6].$date[7].$date[8].$date[9]);
    }


    /**
     * Возвращает все периодические транзакции
     * @return array mixed
     */
    function getAllPeriodic()
    {
        //TODO разобраться в запросе, проставить индексы (если их не хватает)
        $sql = "SELECT p.*, DATE_FORMAT( p.date_from,'%d.%m.%Y') AS date_from, c.cat_name, acc.bill_name FROM periodic p
				LEFT JOIN category c
				    ON c.cat_id = p.cat_id
				LEFT JOIN bill acc
				    ON acc.bill_id = p.bill_id
				        AND acc.user_id=p.user_id
				WHERE p.user_id = ?
				    ORDER BY acc.bill_name, p.date_from";
        return $this->db->select($sql, $this->user->getId());
    }

    /**
     * Возвращает периодическую транзакцию
     * @param int $id Ид транзакции
     * @return array
     */
    function getSelectPeriodic($id)
    {
        $id = (int)$id;
        $sql = "SELECT *, DATE_FORMAT(date_from,'%d.%m.%Y') AS date_from
            FROM periodic p WHERE p.id = ? AND user_id = ?";
        return $this->db->selectRow($sql, $id, $this->user->getId());
    }

    /**
     * Обновляет периодическую транзакцию
     * @param $data array mixed
     * @return bool
     */
    function updatePeriodic($data)
    {
        $id = $data['id'];
        unset($data['id']);
//bill_id = '".$data['bill_id']."',
//cat_id = '".$data['cat_id']."',
//money = '".$data['money']."',
//drain = '".$data['drain']."',
//date_from = '".$data['date_from']."',
//period = '".$data['period']."',
//povtor_num = '".$data['povtor_num']."',
//povtor = '".$data['povtor']."',
//comment = '".$data['comment']."'
        return $this->db->query("UPDATE periodic SET ?a WHERE id = ?", $data, $id);
    }

    /**
     * Удаляет периодическую транзакцию
     * @param $id int
     * @return unknown_type
     */
    function deletePeriodic($id)
    {
        $id = (int)$id;
        return $this->db->query("DELETE FROM periodic WHERE id = ? AND user_id = ?", $id, $this->user->getId());
    }
}

?>