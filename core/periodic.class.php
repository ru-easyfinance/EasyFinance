<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс для управления периодическими транзакциями
 * @author korogen
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * SVN $Id$
 */
class Periodic
{
    /**
     * Хранит ссылка на базу данных
     * @var DbSimple_Mysql
     */
    private $db = null;

    /**
     * Хранит ссылку на класс User
     * @var User
     */
    private $user = null;

    /**
     * Конструктор
     * @param  $db
     * @param User $user
     * @return bool
     */
    function __construct()
    {
        $this->db      = Core::getInstance()->db;
        $this->user    = Core::getInstance()->user;
        return true;
    }

    function savePeriodic($data)
    {
        $user_id = $this->user_id;

        $sql = "INSERT INTO `periodic`
					(`id`, `user_id`, `bill_id`, `cat_id`, `money`, `drain`, `date_from`, `period`, `povtor`, `povtor_num`, `insert`, `remind`, `remind_num`, `comment`)
				VALUES
					('', '".$user_id."', '".$data['bill_id']."', '".$data['cat_id']."', '".$data['money']."', '".$data['drain']."', '".$data['date_from']."', '".$data['period']."', '".$data['povtor']."', '".$data['povtor_num']."', '".$data['insert']."', '".$data['remind']."', '".$data['remind_num']."', '".$data['comment']."')
				";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в cохранении транзакции!', '', __LINE__, __FILE__, $sql);
        }
        else
        {
            return true;
        }
    }

    /**
     *
     * @return unknown_type
     */
    function getInsertPeriodic()
    {
        $date = date("Y-m-d");
        $user_oper = $this->getPeriodic();

        if ($user_oper) {

            $j = 0;
            $k = 0;
            $c = 0;
            $m = 0;

            for ($i=0; $i<count($user_oper); $i++) {
                if ($user_oper[$i]['povtor'] == '1') {
                    $k = $user_oper[$i]['povtor_num'];
                    $any = false;
                } else {
                    $user_oper[$i]['povtor_num'] = $this->foundPovtor($user_oper[$i]['date_from'],$date, $user_oper[$i]['period'], 0);
                    $any = true;
                }

                $tmp_date = $user_oper[$i]['date_from'];

                for ($j=0; $j<$user_oper[$i]['povtor_num']; $j++) {
                    if ($user_oper[$i]['period'] == '1') {
                        $tmp_date = $this->next_1day($tmp_date);
                    }
                    if ($user_oper[$i]['period'] == '7') {
                        $tmp_date = $this->next_7day($tmp_date);
                    }
                    if ($user_oper[$i]['period'] == '30') {
                        $tmp_date = $this->next_1month($tmp_date);
                    }
                    if ($user_oper[$i]['period'] == '90') {
                        $tmp_date = $this->next_3month($tmp_date);
                    }
                    if ($j == 0 && $any == false) {
                        $tmp_date = $user_oper[$i]['date_from'];
                    }
                    if ($tmp_date <= $date) {
                        $c++;
                        $periodic[$c]['bill_id'] = $user_oper[$i]['bill_id'];
                        $periodic[$c]['cat_id'] = $user_oper[$i]['cat_id'];
                        $periodic[$c]['money'] = $user_oper[$i]['money'];
                        $periodic[$c]['drain'] = $user_oper[$i]['drain'];
                        $periodic[$c]['date'] = $tmp_date;
                        if ($user_oper[$i]['povtor'] == '1')
                        {
                            $k--;
                            if ($k == '0')
                            {
                                $m++;
                                $updatePeriodic[$m]['date'] = $tmp_date;
                                $updatePeriodic[$m]['povtor_num'] = $k;
                                $updatePeriodic[$m]['id'] = $user_oper[$i]['id'];
                            }
                        }else{
                            $k=0;
                        }
                    }else{
                        $m++;
                        $updatePeriodic[$m]['date'] = $tmp_date;
                        $updatePeriodic[$m]['povtor_num'] = $k;
                        $updatePeriodic[$m]['id'] = $user_oper[$i]['id'];
                        $j = $user_oper[$i]['povtor_num'];
                    }
                }
            }
        }

        if (!empty($periodic)) {
            for ($i=1; $i<=count($periodic); $i++) {
                //TODO Оптимизировать запрос!!!!
                $sql = "INSERT INTO money (user_id, `date`, cat_id, bill_id, drain) VALUES (?, ?, ?, ?, ?);";
                $this->db->query($sql, $this->user->getId(), $periodic[$i]['money'], $periodic[$i]['date'],
                    $periodic[$i]['cat_id'], $periodic[$i]['bill_id'], $periodic[$i]['drain']);
            }
        }

        if (!empty($updatePeriodic)) {
            for ($i=1; $i<=count($updatePeriodic); $i++) {
                $sql = "UPDATE periodic SET date_from = ?, povtor_num = ?  WHERE id = ? AND user_id = ?;";
                $this->db->query($sql, $updatePeriodic[$i]['date'], $updatePeriodic[$i]['povtor_num'],
                    $updatePeriodic[$i]['id'], $this->user_id);
                return true;
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

    function foundPovtor($date_from, $date_now, $period, $i)
    {
        $i++;

        if ($period == '1')
        {
            $date_from = $this->next_1day($date_from);
        }
        if ($period == '7')
        {
            $date_from = $this->next_7day($date_from);
        }
        if ($period == '30')
        {
            $date_from = $this->next_1month($date_from);
        }
        if ($period == '90')
        {
            $date_from = $this->next_3month($date_from);
        }
        if ($date_from <= $date_now)
        {
            return $this->foundPovtor($date_from, $date_now, $period,$i);
        }else{
            return $i;
        }
    }

    function date_to_int($date)
    {
        return mktime(0, 0, 0, $date[3].$date[4], $date[0].$date[1], $date[6].$date[7].$date[8].$date[9]);
    }

    /**
     * Возвращает периодические транзакции
     * @return array mixed
     */
    function getPeriodic()
    {
        $rows = $this->db->select("SELECT * FROM periodic WHERE user_id = ? AND date_from <= CURRENT_DATE();", $this->user->getId());
        return $rows;
    }

    function getAllPeriodic()
    {
        $sql = "select p.*, DATE_FORMAT( p.`date_from`,'%d.%m.%Y') as date_from, c.cat_name, acc.bill_name from periodic p
				left join category c on c.cat_id = p.cat_id
				left join bill acc on acc.bill_id = p.bill_id and acc.user_id='".$this->user_id."'
				where p.`user_id` = '".$this->user_id."' order by acc.bill_name, p.`date_from`";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в cохранении транзакции!', '', __LINE__, __FILE__, $sql);
        }
        return $this->db->sql_fetchrowset($result);
    }

    function getSelectPeriodic($id)
    {
        $sql = "select p.*, DATE_FORMAT( p.`date_from`,'%d.%m.%Y') as date_from from periodic p
							where p.`id` = '".$id."' and user_id = '".$this->user_id."'";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в cохранении транзакции!', '', __LINE__, __FILE__, $sql);
        }
        return $this->db->sql_fetchrow($result);
    }

    function updatePeriodic($data)
    {
        $sql = "UPDATE `periodic` SET
					`bill_id` = '".$data['bill_id']."',
					`cat_id` = '".$data['cat_id']."',
					`money` = '".$data['money']."',
					`drain` = '".$data['drain']."',
					`date_from` = '".$data['date_from']."',
					`period` = '".$data['period']."',
					`povtor_num` = '".$data['povtor_num']."',
					`povtor` = '".$data['povtor']."',
					`comment` = '".$data['comment']."'
				WHERE `id` = '".$data['id']."'
				";
        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в изменении транзакции!', '', __LINE__, __FILE__, $sql);
        }else{
            return true;
        }
    }

    function deletePeriodic($id)
    {
        $user_id = $this->user->getId();

        $sql = "DELETE FROM `periodic` WHERE `id` = '".$id."' and `user_id` = '".$user_id."'";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в удалении транзакции!', '', __LINE__, __FILE__, $sql);
        }
        else
        {
            return true;
        }
    }
}