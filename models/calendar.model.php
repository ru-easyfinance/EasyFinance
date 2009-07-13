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
        $this->errorData = array();
        $valid = array();

        if (in_array('id', $params) or count($params) == 0) {
            $valid['id'] = (int)@$_POST['key'];
            if ($valid['id'] === 0) {
                print 'id,';
                $this->errorData['id'][] = 'Не указан id события';
            }
        }

        if (in_array('title', $params) or count($params) == 0) {
            $valid['title'] = trim(htmlspecialchars(@$_POST['title']));
            if (empty ($valid['title'])) {
                print 'title,';
                $this->errorData['title'][] = 'Не указан заголовок события';
            }
        }
        if (in_array('near_date', $params) or count($params) == 0) {
            $valid['near_date'] = formatRussianDate2MysqlDate(@$_POST['date']);
            //@TODO Проверять валидность mysql даты
            if (!$valid['near_date']) {
                print 'date,';
                $this->errorData['near_date'][] = 'Не верно указана дата';
            }
        }
        if (in_array('start_date', $params) || count($params) == 0) {
            $valid['start_date'] = formatRussianDate2MysqlDate(@$_POST['start_date']);
            //@TODO Проверять валидность mysql даты
            if (!$valid['start_date']) {
                $this->errorData['start_date'][] = 'Не верно указана дата начала';
            }
        }
        if (in_array('last_date', $params) || count($params) == 0) {
            $valid['last_date'] = formatRussianDate2MysqlDate(@$_POST['date_end']);
            //@TODO Проверять валидность mysql даты
            if (!$valid['last_date']) {
                $this->errorData['last_date'][] = 'Не верно указана дата окончания';
            }
        }
/*
        if (in_array('start_date', $params) && in_array('last_date', $params)  || count($params) == 0) {
            if ((!isset($this->errorData['start_date']) && !isset($this->errorData['last_date'])
                    && strtotime($valid['start_date']) < strtotime($valid['last_date']))) {
                $this->errorData['start_date'][] = 'Дата начала не может быть меньше даты окончания';
                $this->errorData['last_date'][] = 'Дата начала не может быть меньше даты окончания';
            }
        }
 */
        if (in_array('type_repeat', $params) or count($params) == 0) {
            $valid['type_repeat'] = (int)@$_POST['repeat'];
        }
        if (in_array('count_repeat', $params) or count($params) == 0) {
            $valid['count_repeat'] = (int)@$_POST['count'];
        }
        if (in_array('comment', $params) or count($params) == 0) {
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
        // Проверяем корректность отправленных данных
        $array = array('title','near_date','last_date','type_repeat','count_repeat','comment');
        $array = $this->checkData($array);
        // Если есть ошибки, то возвращаем их пользователю в виде массива
        if ($array == false) {
            die(json_encode($this->errorData));
        }
        $sql = "INSERT INTO calendar
            (user_id,title,near_date,start_date,last_date,type_repeat,count_repeat,comment,dt_create)
            VALUES (?,?,?,?,?,?,?,?,NOW())";
        // Дата начала и текущая (ближайшая) дата - тут равны
        $last_id = $this->db->query($sql, Core::getInstance()->user->getId(), $array['title'], $array['near_date'],
            $array['near_date'],$array['last_date'], $array['type_repeat'], $array['count_repeat'], $array['comment']);
        // Если у нас есть повторения события, то добавляем и их тоже
        if($array['count_repeat'] > 0) {
            switch ($array['type_repeat']) {
                case 1: // 1 - Ежедневно,
                    $type = ' DAY '; break;
                case 3: // 3 - Каждый Пн., Ср. и Пт.,
                    break;
                case 4: // 4 - Каждый Вт. и Чт.,
                    break;
                case 5: // 5 - По будням,
                    break;
                case 6: // 6 - По выходным,
                    break;
                case 7: // 7 - Еженедельно,
                    $type = ' WEEK '; break;
                case 30: // 30 - Ежемесячно,
                    $type = ' MONTH '; break;
                    break;
                case 90: // 90 - Ежеквартально,
                    $type = ' QUARTER '; break;
                    break;
                case 365: // 365 - Ежегодно
                    $type = ' YEAR '; break;
                    break;
                default: // 0 - Без повторения
                    return '[]';
            }
            // Повторов должно быть на 1 меньше, так как первая дата - оригинал это уже часть повтора
            for ($i = 1 ; $i < $array['count_repeat'] ; $i++) {
                $this->db->query("INSERT INTO calendar (`user_id`,`main`,`title`,`start_date`,`last_date`,".
                    "`type_repeat`,`count_repeat`, `comment`, `dt_create`, `dt_edit`, `near_date`) ".
                    "SELECT `user_id`,`main`,`title`,`start_date`,`last_date`,`type_repeat`,`count_repeat`,".
                    "`comment`,`dt_create`,`dt_edit`, DATE_ADD(start_date, INTERVAL ?d WEEK) AS `near_date` FROM calendar ".
                    "WHERE id=?d AND user_id=?", $i, $last_id, Core::getInstance()->user->getId());
            }
        }
        return '[]';
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