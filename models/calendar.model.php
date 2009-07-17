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
        /**
         * Массив, в котором хранятся проверенные <b>валидные</b> значения из суперглобального массива $_POST
         * @example $valid['id'] = 109;
         * @var <array> mixed
         */
        $valid = array();
        $this->errorData = array();

        if (in_array('id', $params) or count($params) == 0) {
            $valid['id'] = (int)@$_POST['key'];
            if ($valid['id'] === 0) {
                $this->errorData['id'][] = 'Не указан id события';
            }
        }

        if (in_array('title', $params) or count($params) == 0) {
            $valid['title'] = trim(htmlspecialchars(@$_POST['title']));
            if (empty ($valid['title'])) {
                $this->errorData['title'][] = 'Не указан заголовок события';
            }
        }
        if (in_array('near_date', $params) or count($params) == 0) {
            $valid['near_date'] = formatRussianDate2MysqlDate(@$_POST['date'], @$_POST['time']);
            //@TODO Проверять валидность mysql даты
            if (!$valid['near_date']) {
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
     * @return <json> Массив в формате json. Если пустой, значит успешно добавлено, если со значениями - значит ошибка. И в них содержится информация о том, что введено не верно.
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
        if($array['type_repeat'] > 0) {
            switch ($array['type_repeat']) {
                case 1: // 1 - Ежедневно,
                    $type = ' DAY '; break;
                case 3: // 3 - Каждый Пн., Ср. и Пт.,
                    $type = ' WEEK '; break;
                case 4: // 4 - Каждый Вт. и Чт.,
                    $type = ' WEEK '; break;
                case 5: // 5 - По будням,
                    $type = ' WEEK '; break;
                case 6: // 6 - По выходным,
                    $type = ' WEEK '; break;
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
            $sql = "";
            $repeat_dates = array();
            // Создаём повторяющиеся события определённое количество раз
            if ((int)$_POST['rep_type']== 3) {
                for ($i = 1 ; $i <= $array['count_repeat'] ; $i++) {
                    // 3, 4, 5, 6
                    if ($array['type_repeat'] >= 3 or $array['type_repeat'] <= 6 ) {
                        $dt = formatMysqlDate2UnixTimestamp($array['near_date']);
                        $dw = date  ('w', $dt); // День недели, от 0 (вск) до 6 (суб)
                        for ($j = 0 ; $j < 7 ; $j++) {
                            switch ($array['type_repeat']) {
                                case 3: //3 - Каждый Пн., Ср. и Пт.,
                                    if ($dw == 1 || $dw == 3 || $dw == 5) {
                                        $repeat_dates[] = $dt+(86400 * $j);
                                    }
                                    break;
                                case 4: //4 - Каждый Вт. и Чт.,
                                    if ($dw == 2 || $dw == 4) {
                                        $repeat_dates[] = $dt+(86400 * $j);
                                    }
                                    break;
                                case 5: //5 - По будням,
                                    if ($dw >= 1 && $dw >= 5) {
                                        $repeat_dates[]= $dt+(86400 * $j);
                                    }
                                    break;
                                case 6: //6 - По выходным,
                                    if ($dw == 6 && $dw == 0) {
                                        $repeat_dates[]= $dt+(86400 * $j);
                                    }
                                    break;
                            }
                            if ( $dw == 6) {
                                $dw = 0;
                            } else {
                                $dw++;
                            }
                        }
                        // Перебираем все даты и создаём SQL запрос
                        foreach ($repeat_dates as $v) {
                            if (!empty ($sql)) {
                                $sql .= ',';
                            }
                            $sql .= "('".Core::getInstance()->user->getId()."','{$last_id}','{$array['title']}','{$array['near_date']}',".
                            "'{$array['last_date']}','{$array['type_repeat']}','{$array['count_repeat']}','".
                            addslashes($array['comment'])."', NOW(), DATE_ADD(FROM_UNIXTIME({$v})), INTERVAL {$i} {$type}))";
                        }
                    // 1, 7, 30, 90, 365
                    } else {
                        $sql .= "('".Core::getInstance()->user->getId()."','{$last_id}','{$array['title']}','{$array['near_date']}',".
                        "'{$array['last_date']}','{$array['type_repeat']}','{$array['count_repeat']}','".
                        addslashes($array['comment'])."', NOW(), DATE_ADD('{$array['near_date']}', INTERVAL {$i} {$type}))";
                    }
                }
            // Создаём события до определённой даты
            } else {

            }
            if (!empty($sql)) {
                $this->db->query("INSERT INTO calendar (`user_id`,`chain`,`title`,`start_date`,`last_date`,".
                    "`type_repeat`,`count_repeat`, `comment`, `dt_create`, `near_date`) VALUES " . $sql);
            }
        }
        return '[]';
    }
    
    /**
     * Редактируем событие
     * @return <json> Массив в формате json. Если пустой, значит успешно отредактировано, если со значениями - значит ошибка. И в них содержится информация о том, что введено не верно.
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
     * Удаляет указанное событие
     * @return <json> Массив в формате json. Если пустой, значит успешно удалено, если со значениями - значит ошибка. И в них содержится ошибка.
     */
    function del()
    {
        /**
         * Ид события, которое требуется удалить
         * @var <int>
         */
        $id = (int)@$_POST['id'];

        /**
         * Ид цепочки событий. Если указано, то удаляется вся цепочка событий следующая после id, если false - то только указанное в id
         * @var <bool>
         */
        $chain = (int)@$_POST['chain'];

        // Если сказали удалить всю цепочку, вместе с указанным событием
        if ($chain > 0) {
            $this->db->query("DELETE FROM calendar WHERE (id=?d OR (chain=?d AND id > ?d)) AND user_id=?", $id,
                $chain, $id, Core::getInstance()->user->getId());
            //@TODO Добавить апдейт цепочки событий вверх, до события с указанным Id
            return '[]';
        // Если сказали удалить только выбранное событие
        } else {
            $this->db->query("DELETE FROM calendar WHERE id=?d AND user_id=?", $id, Core::getInstance()->user->getId());
            return '[]';
        }
    }

    /**
     * Возвращает массив событий в формате JSON за указанный период
     * @param <int> $start
     * @param <int> $end
     * @return JSON
     */
    function getEvents($start, $end)
    {
        // Делаем проверку чисел, и если это не число, то устанавливаем 0. Внимание, там ОЧЕНЬ большие числа!!!
        $start = (float)$start;
        $end   = (float)$end;

        $array = $this->getEventsArray($start, $end);
        foreach ($array as $key => $val) {
            $array[$key]['className'] = 'yellow'; //'green','red','blue'
            $array[$key]['draggable'] = true;
            $array[$key]['date'] = (int)$val['date'];
            $array[$key]['start_date'] = (int)$val['start_date'];
            $array[$key]['last_date'] = (int)$val['last_date'];
        }
        return json_encode($array);
    }

    /**
     * Возвращает массив событий за указанный период
     * @param <int> $start
     * @param <int> $end
     * @return array mixed
     */
    private function getEventsArray($start, $end)
    {
        // Убираем микросекунды, и приводим числа  к формату UNIX_TIMESTAMP
        //$start = strtotime(date("Y-m-d", strtotime($start / 1000)) . " -1 month"); // Делаем выборку за три месяца
        $start = $start / 1000;
        $end = $end / 1000;
        
        $sql = "SELECT 
            id, title, UNIX_TIMESTAMP(near_date) AS `date`,
            UNIX_TIMESTAMP(start_date) AS `start_date`, UNIX_TIMESTAMP(last_date) AS `last_date`,
            type_repeat AS `repeat`, count_repeat AS `count`, comment, chain, infinity
        FROM calendar WHERE user_id=? AND DATE(near_date) BETWEEN DATE(FROM_UNIXTIME(?)) AND DATE(FROM_UNIXTIME(?)) ORDER BY near_date;";
        $array = $this->db->select($sql, Core::getInstance()->user->getId(), $start, $end);
        return $array;
    }

    /**
     * Возвращает события в формате iCalendar
     */
    function iCalendar()
    {
        $header = "BEGIN:VCALENDAR".
        "\nPRODID:-//Home-Money.ru//http://home-money.ru rev.".REVISION."//EN".
        "\nVERSION:2.0".
        "\nCALSCALE:GREGORIAN".
        "\nMETHOD:PUBLISH". //???
        "\nX-WR-CALNAME:Список событий".
        "\nX-WR-TIMEZONE:Europe/Moscow".
        "\nX-WR-CALDESC:Список событий с сайта home-money.ru".
        "\nBEGIN:VTIMEZONE".
        "\nTZID:Europe/Moscow".
        "\nX-LIC-LOCATION:Europe/Moscow".
        "\nBEGIN:DAYLIGHT".
        "\nTZOFFSETFROM:+0300".
        "\nTZOFFSETTO:+0400".
        "\nTZNAME:MSD".
        "\nDTSTART:19700329T020000".
        "\nRRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU".
        "\nEND:DAYLIGHT".
        "\nBEGIN:STANDARD".
        "\nTZOFFSETFROM:+0400".
        "\nTZOFFSETTO:+0300".
        "\nTZNAME:MSK".
        "\nDTSTART:19701025T030000".
        "\nRRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU".
        "\nEND:STANDARD".
        "\nEND:VTIMEZONE".
        "\nBEGIN:VEVENT";
/*
BEGIN:VCALENDAR
PRODID:-//Google Inc//Google Calendar 70.9054//EN
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-WR-CALNAME:Открытый календарь
X-WR-TIMEZONE:Europe/Moscow
X-WR-CALDESC:Самый\, открытый календарь
BEGIN:VTIMEZONE
TZID:Europe/Moscow
X-LIC-LOCATION:Europe/Moscow
BEGIN:DAYLIGHT
TZOFFSETFROM:+0300
TZOFFSETTO:+0400
TZNAME:MSD
DTSTART:19700329T020000
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0400
TZOFFSETTO:+0300
TZNAME:MSK
DTSTART:19701025T030000
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
DTSTART;TZID=Europe/Moscow:20090624T110000
DTEND;TZID=Europe/Moscow:20090624T193000
RRULE:FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR;WKST=MO
DTSTAMP:20090714T145320Z
UID:cm9hgv5k2akiv6ssuf539p72vs@google.com
CLASS:PUBLIC
CREATED:00001231T000000Z
DESCRIPTION:Описание этого события
LAST-MODIFIED:20090626T074742Z
LOCATION:Москва\, метро Савёловское
SEQUENCE:2
STATUS:CONFIRMED
SUMMARY:Первое открытое событие
TRANSP:OPAQUE
END:VEVENT
END:VCALENDAR
*/
    }
}