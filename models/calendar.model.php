<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления календарём
 * @category calendar
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Calendar_Model {
/**
 * Ссылка на экземпляр DBSimple
 * @var DbSimple_Mysql
 */
    private $db = NULL;

    /**
     * Массив со ссылками на ошибки. Ключ - имя поля, значение массив текста ошибки
     * @example array('date'=>array('Не указана дата'), 'time'=> array('Не указано время'));
     * @var array
     */
    private $errorData = array();

    /**
     * Конструктор
     * @return void
     */
    function  __construct() {
        $this->db = Core::getInstance()->db;
    }

    /**
     * Проверяет <b>$_POST</b> на ошибки, при добавлении или редактировании события, все ошибки записывает в переменную <code>$this->errorData</code> в виде массива
     * @param array $params Массив с параметрами, которые нужно проверить. Если массив пустой или параметр не указан, проверяются все значения
     * @example array('id','title','near_date');
     * @return bool false - если есть ошибки
     */
    function checkData($params = array()) {
    /**
     * Массив, в котором хранятся проверенные <b>валидные</b> значения из суперглобального массива $_POST
     * @example $valid['id'] = 109;
     * @var array mixed
     */
        $valid = array();
        $this->errorData = array();

        // Проверяем ID
        if (in_array('id', $params) or count($params) == 0) {
            $valid['id'] = (int)@$_POST['key'];
            if ($valid['id'] === 0) {
                $this->errorData['id'][] = 'Не указан id события';
            }
        }

        // Проверяем заголовок - title
        if (in_array('title', $params) or count($params) == 0) {
            $valid['title'] = trim(htmlspecialchars(@$_POST['title']));
            if (empty ($valid['title'])) {
                $this->errorData['title'][] = 'Не указан заголовок события';
            }
        }

        // Проверяем дни недели - week
        $valid['week'] = array();
        (isset($_POST['mon']) && $_POST['mon'] == '1') ? $valid['week'][1] = true : $valid['week'][1] = false;
        (isset($_POST['tue']) && $_POST['tue'] == '1') ? $valid['week'][2] = true : $valid['week'][2] = false;
        (isset($_POST['wed']) && $_POST['wed'] == '1') ? $valid['week'][3] = true : $valid['week'][3] = false;
        (isset($_POST['thu']) && $_POST['thu'] == '1') ? $valid['week'][4] = true : $valid['week'][4] = false;
        (isset($_POST['fri']) && $_POST['fri'] == '1') ? $valid['week'][5] = true : $valid['week'][5] = false;
        (isset($_POST['sat']) && $_POST['sat'] == '1') ? $valid['week'][6] = true : $valid['week'][6] = false;
        (isset($_POST['sun']) && $_POST['sun'] == '1') ? $valid['week'][0] = true : $valid['week'][0] = false;

        // Получаем тип повторения событий
        if (in_array('type_repeat', $params) or count($params) == 0) {
            $valid['type_repeat'] = (int)@$_POST['repeat'];
        }

        // Проверяем ближайшую дату
        if (in_array('near_date', $params) or count($params) == 0) {
            $valid['near_date'] = formatRussianDate2MysqlDate(@$_POST['date'], @$_POST['time']);
            if (!$valid['near_date']) {
                $this->errorData['near_date'][] = 'Не верно указана дата';
            // Если события повторяются еженедельно
            } elseif ($valid['type_repeat'] >= 3 && $valid['type_repeat'] <= 7) {
                $dt = formatMysqlDate2UnixTimestamp($valid['near_date']);
                $dw = date  ('w', $dt); // День недели, от 0 (вск) до 6 (суб)
                // Если не совпадает, то меняем ближайшую дату, найдя соответствующую дату, прокрутив цикл по дням в одну неделю
                if (!$valid['week'][$dw]) {
                    for ($j = 1; $j < 8 ; $j++) {
                        if ( $dw == 6) { $dw = 0; } else { $dw++; }
                        if ($valid['week'][$dw]) {
                            $valid['near_date'] = date('Y-m-d G:i:00',($dt+(86400 * $j)));
                            break;
                        }
                    }
                }
            }
        }

        // Проверяем дату начала
        if (in_array('start_date', $params) || count($params) == 0) {
            $valid['start_date'] = formatRussianDate2MysqlDate(@$_POST['start_date']);
            if (!$valid['start_date']) {
                $this->errorData['start_date'][] = 'Не верно указана дата начала';
            }
        }

        // Проверяем дату окончания
        if (in_array('last_date', $params) || count($params) == 0) {
            $valid['last_date'] = formatRussianDate2MysqlDate(@$_POST['date_end']);
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
     * @return json Массив в формате json. Если пустой, значит успешно добавлено, если со значениями - значит ошибка. И в них содержится информация о том, что введено не верно.
     */
    function add() {
    // Проверяем корректность отправленных данных
        $array = array('title','near_date','last_date','type_repeat','count_repeat','comment');
        $array = $this->checkData($array);

        // Если есть ошибки, то возвращаем их пользователю в виде массива
        if ($array == false) {
            die(json_encode($this->errorData));
        }

        $sql = "INSERT INTO calendar ".
            "(user_id,title,near_date,start_date,last_date,type_repeat,count_repeat,comment,dt_create) ".
            "VALUES (?,?,?,?,?,?,?,?,NOW())";

        // Дата начала и текущая (ближайшая) дата - тут равны
        $last_id = $this->db->query($sql, Core::getInstance()->user->getId(), $array['title'],
            $array['near_date'], $array['near_date'],$array['last_date'], $array['type_repeat'],
            $array['count_repeat'], $array['comment']);

        // Если у нас есть повторения события, то добавляем и их тоже
        if ($array['type_repeat'] == 1) {
            $sql = $this->_repeatDay($array, $last_id);
        } elseif ($array['type_repeat'] >= 3 && $array['type_repeat'] <= 7) {
            $sql = $this->_repeatWeekDay($array, $last_id);
        } elseif ($array['type_repeat'] == 30) {
            $sql = $this->_repeatMonth($array, $last_id);
        //} elseif ($array['type_repeat'] == 90) {
        //$type = ' QUARTER ';
        } elseif ($array['type_repeat'] == 365) {
            $sql = $this->_repeatYear($array, $last_id);
        } else {
            return '[]';
        }
        if ($array['type_repeat'] > 0 && !empty($sql)) {
            $this->db->query("INSERT INTO calendar (`user_id`,`chain`,`title`,`start_date`,`last_date`,".
                "`type_repeat`,`count_repeat`, `comment`, `dt_create`, `near_date`) VALUES " . $sql);
        //                print "INSERT INTO calendar (`user_id`,`chain`,`title`,`start_date`,`last_date`,`type_repeat`,`count_repeat`, `comment`, `dt_create`, `near_date`) VALUES " . $sql;
        }
        return '[]';
    }

    /**
     * Возвращает часть сформированного sql запроса, для повторения по указанным дням недели
     * @param array $array mixed
     * @param int $last_id
     * @return string $sql
     */
    private function _repeatWeekDay($array, $last_id) {
        $sql = "";
        // Повторяем заданное количество раз
        if((int)@$_POST['rep_type'] == 1) {
            for ($i = 0; $i <= $array['count_repeat'] - 1 ; $i++) {
            // Начинаем счёт со следующего дня и добавляем отступ в неделю
                $dt = formatMysqlDate2UnixTimestamp($array['near_date']) + 86400 + ((86400 * 7) * $i);
                for ($j = 1 ; $j < 8 ; $j++) {
                    $dw = date('w', $dt + ($j * 86400)); // День недели, от 0 (вск) до 6 (суб)
                    if ($array['week'][$dw]) {
                        if (!empty ($sql)) { $sql .= ','; }
                        $sql .= "('".Core::getInstance()->user->getId()."','{$last_id}','{$array['title']}',
                        '{$array['near_date']}','{$array['last_date']}','{$array['type_repeat']}','{$array['count_repeat']}','".
                            addslashes($array['comment'])."', NOW(), '".date('Y-m-d G:i:00',($dt+(86400 * $j)))."')";
                    }
                }
            }
        // Бесконечно повторяем события
        } elseif((int)@$_POST['rep_type'] == 2) {
            for ($i = 0 ; $i < 90 ; $i++) {
            // Начинаем счёт со следующего дня и добавляем отступ в неделю
                $dt = formatMysqlDate2UnixTimestamp($array['near_date']) + 86400 + ((86400 * 7) * $i);
                for ($j = 1 ; $j < 8 ; $j++) {
                    $dw = date('w', $dt + ($j * 86400)); // День недели, от 0 (вск) до 6 (суб)
                    if ($array['week'][$dw]) {
                        if (!empty ($sql)) { $sql .= ','; }
                        $sql .= "('".Core::getInstance()->user->getId()."','{$last_id}','{$array['title']}',
                        '{$array['near_date']}','{$array['last_date']}','{$array['type_repeat']}','{$array['count_repeat']}','".
                            addslashes($array['comment'])."', NOW(), '".date('Y-m-d G:i:00',($dt+(86400 * $j)))."')";
                    }
                }
            }
        // Повторяем до указанной даты
        } elseif((int)@$_POST['rep_type'] == 3) {
            for ($i = 0 ; $i < 90 ; $i++) {
                $l = formatMysqlDate2UnixTimestamp($array['last_date']);
                // Начинаем счёт со следующего дня и добавляем отступ в неделю
                $dt = formatMysqlDate2UnixTimestamp($array['near_date']);
                $dt += ((86400 * 7) * $i) + 86400;
                for ($j = 1 ; $j < 8 ; $j++) {
                    if ($dt+(86400 * $j) > $l) {
                        return $sql;
                    }
                    $dw = date('w', $dt + ($j * 86400)); // День недели, от 0 (вск) до 6 (суб)
                    if ($array['week'][$dw]) {
                        if (!empty ($sql)) { $sql .= ','; }
                        $sql .= "('".Core::getInstance()->user->getId()."','{$last_id}','{$array['title']}',
                        '{$array['near_date']}','{$array['last_date']}','{$array['type_repeat']}','{$array['count_repeat']}','".
                            addslashes($array['comment'])."', NOW(), '".date('Y-m-d G:i:00',($dt+(86400 * $j)))."')";
                    }
                }
            }
        }
        return $sql;
    }

    /**
     * Возвращает часть сформированного sql запроса, для ежемесячного повторения
     * @param array $array mixed
     * @param int $last_id
     * @return string $sql
     */
    private function _repeatMonth($array, $last_id) {
        $ds = formatMysqlDate2UnixTimestamp($array['near_date']);
        $dl = formatMysqlDate2UnixTimestamp($array['last_date']);
        $sql = "";
        if ((int)@$_POST['rep_type'] == 1) { // Определённое количество раз
            for ($i = 1 ; $i <= $array['count_repeat'] ; $i++) {
                if (!empty ($sql)) { $sql .= ','; }
                $sql .= "('".Core::getInstance()->user->getId()."','{$last_id}','{$array['title']}','{$array['near_date']}',".
                    "'{$array['last_date']}','{$array['type_repeat']}','{$array['count_repeat']}','".
                    addslashes($array['comment'])."', NOW(), FROM_UNIXTIME('".strtotime("+{$i} MONTH", $ds)."'))";
            }
        } elseif((int)@$_POST['rep_type'] == 2) { // Бесконечно
            for ($i = 1 ; $i <= 90 ; $i++) {
                if (!empty ($sql)) { $sql .= ','; }
                $sql .= "('".Core::getInstance()->user->getId()."','{$last_id}','{$array['title']}','{$array['near_date']}',".
                    "'{$array['last_date']}','{$array['type_repeat']}','{$array['count_repeat']}','".
                    addslashes($array['comment'])."', NOW(), FROM_UNIXTIME('".strtotime("+{$i} MONTH", $ds)."'))";
            }
        } elseif((int)@$_POST['rep_type'] == 3) { // Повторять до даты
            for ($i = 1 ; $i <= 90 ; $i++) {
                $dt = strtotime("+{$i} MONTH", $ds);
                if ($dt > $dl) { return $sql; }
                if (!empty ($sql)) { $sql .= ','; }
                $sql .= "('".Core::getInstance()->user->getId()."','{$last_id}','{$array['title']}','{$array['near_date']}',".
                    "'{$array['last_date']}','{$array['type_repeat']}','{$array['count_repeat']}','".
                    addslashes($array['comment'])."', NOW(), FROM_UNIXTIME({$dt}))";
            }
        }
        return $sql;
    }

    /**
     * Возвращает часть сформированного sql запроса, для ежегодного повторения
     * @param array $array mixed
     * @param int $last_id
     * @return string $sql
     */
    private function _repeatYear($array, $last_id) {
        $ds = formatMysqlDate2UnixTimestamp($array['near_date']);
        $dl = formatMysqlDate2UnixTimestamp($array['last_date']);
        $sql = "";
        if ((int)@$_POST['rep_type'] == 1) { // Определённое количество раз
            for ($i = 1 ; $i <= $array['count_repeat'] ; $i++) {
                if (!empty ($sql)) { $sql .= ','; }
                $sql .= "('".Core::getInstance()->user->getId()."','{$last_id}','{$array['title']}','{$array['near_date']}',".
                    "'{$array['last_date']}','{$array['type_repeat']}','{$array['count_repeat']}','".
                    addslashes($array['comment'])."', NOW(), FROM_UNIXTIME('".strtotime("+{$i} YEAR", $ds)."'))";
            }
        } elseif((int)@$_POST['rep_type'] == 2) { // Бесконечно
            for ($i = 1 ; $i <= 90 ; $i++) {
                if (!empty ($sql)) { $sql .= ','; }
                $sql .= "('".Core::getInstance()->user->getId()."','{$last_id}','{$array['title']}','{$array['near_date']}',".
                    "'{$array['last_date']}','{$array['type_repeat']}','{$array['count_repeat']}','".
                    addslashes($array['comment'])."', NOW(), FROM_UNIXTIME('".strtotime("+{$i} YEAR", $ds)."'))";
            }
        } elseif((int)@$_POST['rep_type'] == 3) { // Повторять до даты
            for ($i = 1 ; $i <= 90 ; $i++) {
                $dt = strtotime("+{$i} YEAR", $ds);
                if ($dt > $dl) { return $sql; }
                if (!empty ($sql)) { $sql .= ','; }
                $sql .= "('".Core::getInstance()->user->getId()."','{$last_id}','{$array['title']}','{$array['near_date']}',".
                    "'{$array['last_date']}','{$array['type_repeat']}','{$array['count_repeat']}','".
                    addslashes($array['comment'])."', NOW(), FROM_UNIXTIME({$dt}))";
            }
        }
        return $sql;
    }

    /**
     * Возвращает часть сформированного sql запроса, для ежедневного повторения
     * @param array $array mixed
     * @param int $last_id
     * @return string $sql
     */
    private function _repeatDay($array, $last_id) {
        $sql = "";
        if ((int)@$_POST['rep_type'] == 1) { // Определённое количество раз
            for ($i = 1 ; $i <= $array['count_repeat'] ; $i++) {
                if (!empty ($sql)) { $sql .= ','; }
                $sql .= "('".Core::getInstance()->user->getId()."','{$last_id}','{$array['title']}','{$array['near_date']}',".
                    "'{$array['last_date']}','{$array['type_repeat']}','{$array['count_repeat']}','".
                    addslashes($array['comment'])."', NOW(), DATE_ADD('{$array['near_date']}', INTERVAL {$i} DAY))";
            }
        } elseif((int)@$_POST['rep_type'] == 2) { // Бесконечно
            for ($i = 1 ; $i <= 90 ; $i++) {
                if (!empty ($sql)) { $sql .= ','; }
                $sql .= "('".Core::getInstance()->user->getId()."','{$last_id}','{$array['title']}','{$array['near_date']}',".
                    "'{$array['last_date']}','{$array['type_repeat']}','{$array['count_repeat']}','".
                    addslashes($array['comment'])."', NOW(), DATE_ADD('{$array['near_date']}', INTERVAL {$i} DAY))";
            }
        } elseif((int)@$_POST['rep_type'] == 3) { // Повторять до даты
            $c = formatMysqlDate2UnixTimestamp($array['near_date']);
            for ($i = 1 ; $i <= 90 ; $i++) {
                $c = $c + 86400;
                $l = formatMysqlDate2UnixTimestamp($array['last_date']);
                if ( $c > $l) { return $sql;}
                if (!empty ($sql)) { $sql .= ','; }
                $sql .= "('".Core::getInstance()->user->getId()."','{$last_id}','{$array['title']}','{$array['near_date']}',".
                    "'{$array['last_date']}','{$array['type_repeat']}','{$array['count_repeat']}','".
                    addslashes($array['comment'])."', NOW(), '".date('Y-m-d G:i:00',$c)."')";
            }
        }
        return $sql;
    }

    /**
     * Редактируем событие
     * @return json Массив в формате json. Если пустой, значит успешно отредактировано, если со значениями - значит ошибка. И в них содержится информация о том, что введено не верно.
     */
    function edit() {
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
     * @return json Массив в формате json. Если пустой, значит успешно удалено, если со значениями - значит ошибка. И в них содержится ошибка.
     */
    function del() {
    /**
     * Ид события, которое требуется удалить
     * @var int
     */
        $id = (int)@$_POST['id'];

        /**
         * Ид цепочки событий. Если указано, то удаляется вся цепочка событий следующая после id, если false - то только указанное в id
         * @var bool
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
     * @param int $start
     * @param int $end
     * @return JSON
     */
    function getEvents($start, $end) {
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
     * @param int $start
     * @param int $end
     * @return array mixed
     */
    private function getEventsArray($start, $end) {
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
    function iCalendar() {
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