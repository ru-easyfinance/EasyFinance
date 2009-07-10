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
     * @var <DbSimple_Mysql>
     */
    private $db = NULL;
    
    /**
     * Массив со ссылками на ошибки. Ключ - имя поля, значение массив текста ошибки
     * @example array('date'=>array('Не указана дата'), 'time'=> array('Не указано время'));
     * @var <array>
     */
    private $errorData = array();

    /**
     * Конструктор
     * @return void
     */
    function  __construct()
    {
        $this->db = Core::getInstance()->db;
    }

    /**
     * Проверяет <b>$_POST</b> на ошибки, при добавлении или редактировании события, все ошибки записывает в переменную <code>$this->errorData</code> в виде массива
     * @param <array> $params Массив с параметрами, которые нужно проверить. Если массив пустой или параметр не указан, проверяются все значения
     * @example array('id','title','near_date');
     * @return <bool> false - если есть ошибки
     */
    function checkData($params = array())
    {
        
        $valid = array();
        if (isset($params['id']) || count($params) == 0) {
            $valid['id'] = (int)@$_POST['key'];
            if ($valid['id'] === 0) {
                $this->errorData['id'][] = 'Не указан id события';
            }
        }
        if (isset($params['title']) || count($params) == 0) {
            $valid['title'] = trim(htmlspecialchars(@$_POST['title']));
            if (empty ($valid['title'])) {
                $this->errorData['title'][] = 'Не указан заголовок события';
            }
        }
        if (isset($params['near_date']) || count($params) == 0) {
            $valid['near_date'] = formatRussianDate2MysqlDate(@$_POST['date']);
            //@TODO Проверять валидность mysql даты
            if (!$valid['near_date']) {
                $this->errorData['near_date'][] = 'Не верно указана дата';
            }
        }
        if (isset($params['start_date']) || count($params) == 0) {
            $valid['start_date'] = formatRussianDate2MysqlDate(@$_POST['start_date']);
            //@TODO Проверять валидность mysql даты
            if (!$valid['start_date']) {
                $this->errorData['start_date'][] = 'Не верно указана дата начала';
            }
        }
        if (isset($params['last_date']) || count($params) == 0) {
            $valid['last_date'] = formatRussianDate2MysqlDate(@$_POST['last_date']);
            //@TODO Проверять валидность mysql даты
            if (!$valid['last_date']) {
                $this->errorData['last_date'][] = 'Не верно указана дата окончания';
            }
        }
        if (isset($params['start_date']) && isset($params['last_date'])  || count($params) == 0) {
            if ((!isset($this->errorData['start_date']) && !isset($this->errorData['last_date'])
                    && strtotime($valid['start_date']) < strtotime($valid['last_date']))) {
                $this->errorData['start_date'][] = 'Дата начала не может быть меньше даты окончания';
                $this->errorData['last_date'][] = 'Дата начала не может быть меньше даты окончания';
            }
        }
        if (isset($params['type_repeat']) || count($params) == 0) {
            $valid['type_repeat'] = (int)@$_POST['repeat'];
        }
        if (isset($params['count_repeat']) || count($params) == 0) {
            $valid['count_repeat'] = (int)@$_POST['count'];
        }
        if (isset($params['comment']) || count($params) == 0) {
            $valid['comment'] = trim(htmlspecialchars(@$_POST['comment']));
        }
        if (count($this->errorData) > 0) {
            return false;
        }
        return $valid;
    }

    /**
     * Добавляем новое событие
     * @return <bool>
     */
    function add()
    {
        $array = array('title','near_date','start_date','last_date','type_repeat','count_repeat','comment');
        $array = $this->checkData($array);
        if (!$array) {
            die(json_encode($this->errorData));
        }
        $sql = "INSERT INTO calendar
            (user_id,title,near_date,start_date,last_date,type_repeat,count_repeat,comment,dt_create)
            VALUES (?,?,?,?,?,?,?,?,NOW())";
        $this->db->query($sql, Core::getInstance()->user->getId(), $array['title'], $array['near_date'],
            $array['start_date'],$array['last_date'], $array['type_repeat'], $array['count_repeat'], $array['comment']);
        return mysql_insert_id();
    }
    
    /**
     * Редактируем событие
     * @@return <bool>
     */
    function edit()
    {
        $array = $this->checkData();
        if (!$array) {
            die(json_encode($this->errorData));
        }
        $sql = "UPDATE calendar SET
            title = ?,
            near_date = ?,
            start_date = ?,
            last_date = ?,
            type_repeat = ?,
            count_repeat = ?,
            comment = ?
            WHERE user_id=? AND id=?";
        $this->db->query($sql, $array['title'], $array['near_date'], $array['start_date'], $array['last_date'],
            $array['type_repeat'],$array['count_repeat'], $array['comment'], Core::getInstance()->user->getId(), $array['id']);
        return $array['id'];
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
        return $array;
    }
}