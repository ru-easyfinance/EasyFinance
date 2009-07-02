<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления календарём
 * @category calendar
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Calendar_Model
{
    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    private $db = NULL;
    
    /**
     * Конструктор
     * @return void
     */
    function  __construct() {
        $this->db = Core::getInstance()->db;
    }

    /**
     * Добавляем новое событие
     * @return bool
     */
    function add()
    {
        //$id = (int)@$_POST['key']
        $title        = htmlspecialchars(@$_POST['title']);
        $near_date    = formatRussianDate2MysqlDate(@$_POST['date']);
        $start_date   = formatRussianDate2MysqlDate(@$_POST['date_start']);
        $last_date    = formatRussianDate2MysqlDate(@$_POST['date_end']);
        $type_repeat  = (int)@$_POST['repeat'];
        $count_repeat = (int)@$_POST['count'];
        $comment      = htmlspecialchars(@$_POST['comment']);
        //@TODO Добавить проверку переменных
        $sql = "INSERT INTO calendar
            (user_id,title,near_date,start_date,last_date,type_repeat,count_repeat,comment,dt_create)
            VALUES (?,?,?,?,?,?,?,?,NOW())";
        $this->db->query($sql, Core::getInstance()->user->getId(), $title, $near_date, $start_date,
            $last_date, $type_repeat, $count_repeat, $comment);
        return mysql_insert_id();
    }

    /**
     * Возвращает массив событий в формате JSON за указанный период
     * @param <int> $start
     * @param <int> $end
     * @return JSON
     */
    function getEvents($start, $end)
    {
        //@TODO Сделать проверку и установку свойств по умолчанию
        $array = $this->getEventsArray($start, $end);
        foreach ($array as $key => $val) {
            $array[$key]['className'] = 'yellow';
            $array[$key]['draggable'] = true;
            $array[$key]['date'] = (int)$val['date'];
            $array[$key]['start_date'] = (int)$val['start_date'];
            $array[$key]['last_date'] = (int)$val['last_date'];
//            $array[$key]['showTime'] = false;
        }
        return json_encode($array);
//                    showTime => false,
//                    className => 'green' ,
//                    className => 'red',
//                    className => 'blue',

    }

    /**
     * Возвращает массив событий за указанный период
     * @param <int> $start
     * @param <int> $end
     * @return array mixed
     */
    private function getEventsArray($start, $end)
    {
        $sql = "SELECT 
            id, title, UNIX_TIMESTAMP(near_date) AS `date`,
            UNIX_TIMESTAMP(start_date) AS `start_date`, UNIX_TIMESTAMP(last_date) AS `last_date`,
            type_repeat AS `repeat`,
            count_repeat AS `count`, comment
            #, dt_create, dt_edit
        FROM calendar WHERE user_id=?";
        $array = $this->db->select($sql, Core::getInstance()->user->getId());
        //die(var_dump($this->db));
        return $array;
    }
}