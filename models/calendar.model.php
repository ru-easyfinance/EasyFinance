<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления календарём
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * SVN $Id$
 */
class Calendar_Model
{
    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    private $db = NULL;

    /**
     * Добавляем новое событие
     * @return bool
     */
    function add() {
        //count repeat	time

        //$id = (int)@$_POST['key'];
        $title        = htmlspecialchars(@$_POST['title']);
        $near_date    = formatRussianDate2MysqlDate(@$_POST['date']);
        $start_date   = formatRussianDate2MysqlDate(@$_POST['date_start']);
        $last_date    = formatRussianDate2MysqlDate(@$_POST['date_end']);
        $type_repeat  = (int)@$_POST['repeat'];
        $count_repeat = (int)@$_POST['count'];
        $comment      = htmlspecialchars(@$_POST['comment']);
        //@TODO Добавить проверку переменных
        $this->db = Core::getInstance()->db;
        $sql = "INSERT INTO calendar
            (user_id,title,near_date,start_date,last_date,type_repeat,count_repeat,comment,dt_create)
            VALUES (?,?,?,?,?,?,?,NOW())";
        $this->db->query($sql, Core::getInstance()->user->getId(), $title, $near_date, $start_date,
            $last_date, $type_repeat, $count_repeat, $comment);
        return mysql_insert_id();
    }
}