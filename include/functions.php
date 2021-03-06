<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
* Сборник полезных функций
* @author Max Kamashev "ukko" <max.kamashev@gmail.com>
* @copyright http://easyfinance.ru/
* SVN $Id$
*/

/**
* Реализация функции __autoload для всех классов
* @param string $class_name Строка с названием класса
* @access protected Уровень доступа
* @return void
*/
function __autoload($class_name) {
    $array = explode("_",$class_name);

    // Грузим контроллеры
    if ( isset($array[1]) && $array[1] == 'Controller' && file_exists(SYS_DIR_ROOT .'/controllers/'. strtolower($array[0]) . '.controller.php' ) )
    {
            require_once SYS_DIR_ROOT .'/controllers/'. strtolower($array[0]). '.controller.php';
    }
    // Загружаем модели /models
    elseif ( isset($array[1]) && $array[1] == 'Model' && file_exists(SYS_DIR_ROOT . '/models/' . strtolower($array[0]) . '.model.php') )
    {
            require_once SYS_DIR_ROOT . '/models/' . strtolower($array[0]) . '.model.php';
    }
    // Загружаем дополнительные классы /core
    elseif (file_exists(SYS_DIR_LIBS . strtolower($array[0]) . '.class.php'))
    {
        require_once SYS_DIR_LIBS . strtolower($array[0]) . '.class.php';
    }
    else
    {
        //trigger_error("Не удалось найти файл с классом {$class_name} ".var_dump($array), E_USER_ERROR);
        return false;
    }
}

// Код обработчика ошибок SQL.
function databaseErrorHandler($message, $info)
{
    // Если использовалась @, ничего не делать.
    if (!error_reporting()) return;

    // Выводим подробную информацию об ошибке.
    throw new Exception(var_export($info, true));
}

function databaseLogger($db, $sql)
{
    //$caller = $db->findLibraryCaller();
    //trigger_error(end(end(@$caller['object']->_placeholderCache)), E_USER_NOTICE);
    return false;
}

/**
* Функция - обработчик ПОЛЬЗОВАТЕЛЬСКИХ ошибок.
* @param $errno integer
* @param $errstr string
* @param $errfile string
* @param $errline integer
* @return bool
*/
function UserErrorHandler($errno, $errstr, $errfile, $errline)
{
     switch ($errno) {
        case E_USER_ERROR:
            trigger_error("*USER ERROR* [$errno] $errstr line: $errline in file: $errfile");
            //Core::getInstance()->errors[] = $errstr;
            exit(1);
        case E_USER_WARNING:
            //trigger_error("*USER WARNING* [$errno] $errstr  line: $errline in file: $errfile");
            Core::getInstance()->errors[] = $errstr;
            break;
        case E_USER_NOTICE:
            if (DEBUG) {
                if (substr($errstr, 0, 4) == '  --'){
                    FirePHP::getInstance(true)->log($errstr);
                } else {
                    FirePHP::getInstance(true)->info($errstr);
                }
                break;
            }
    }
    return true;
}

/**
* Выводит красивую 404 ошибку
* @param $path string Строка, которую нужно показать
*/
function error_404 ($path='')
{
    //TODO
    //header("HTTP/1.1 404 Not Found");
    header('HTTP', true, 404);
    exit;
    //die(file_get_contents('/404.html'));
}

/**
* Форматирует русское представление даты, например: <code>20.02.2009</code> в формат даты mysql <code>2009-02-20</code>
* @param <string> $date Дата, в формате дд.мм.гггг
* @param <string> $time Время в формате чч:мм
* @return <string>
*/
function formatRussianDate2MysqlDate($date, $time='')
{
    /**
    * Собирает в себе отформатированную дату
    * @var string
    */
    $retval = '';
    if (empty ($date)) {
        return false;
    }

    $date = explode('.', $date);
    if (count($date) == 3) {

        // Добавляем год
        $retval = (int)$date[2].'-';

        // Добавляем месяц
        if ((int)$date[1] < 10) {
            $retval .= '0'.(int)$date[1] .'-';
        } else {
            $retval .= (int)$date[1] .'-';
        }

        // Добавляем день
        if((int)$date[0] < 10) {
            $retval .= '0'.(int)$date[0];
        } else {
            $retval .= (int)$date[0];
        }

        //  Добавляем время (если есть)
        if (empty ($time)) {
            return $retval;
        } elseif (preg_match("/^([0-9]{2}):([0-9]{2})$/", $time)) {
            return $retval . ' ' . $time . ':00';
        }

    } else {
        return false;
    }
}

/**
* Форматирует дату в формате mysql <code>1983-05-22</code> или датетайм <code>1983-05-22 12:43:03</code> в unix_timestamp
* @param string $date
* @return int Unix Timestamp или false в случае ошибки
*/
function formatMysqlDate2UnixTimestamp($date)
{
    $date = trim($date);
    // Таймштамп
    if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/", $date)) {
        return strtotime($date);
    // Дата
    } elseif (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date)) {
        return strtotime($date);
    } else {
        return false;
    }
}

 /**
 * Шифрует данные с помощью расширения mcrypt
 * @param string $text Текст, который требуется зашифровать
 * @param string $key // 24 битный ключ
 * @return string Возвращает зашифрованный ключ в обёртке base64
 */
function encrypt($text, $key = CRYPT_KEY) {
    $text = trim(serialize($text));
    $iv = substr(md5($key), 0,mcrypt_get_iv_size (MCRYPT_CAST_256,MCRYPT_MODE_CFB));
    $c_t = mcrypt_cfb (MCRYPT_CAST_256, $key, $text, MCRYPT_ENCRYPT, $iv);
    return base64_encode($c_t);
}

 /**
 * Расшифровывает данные с помощью расширения mcrypt
 * @param string $text Текст, который требуется разшифровать
 * @param string $key // 24 битный ключ
 * @return string Расшифрованную строку
 */
function decrypt($text, $key = CRYPT_KEY) {
    $text = base64_decode($text);
    $iv = substr(md5($key), 0,mcrypt_get_iv_size (MCRYPT_CAST_256,MCRYPT_MODE_CFB));
    $p_t = mcrypt_cfb (MCRYPT_CAST_256, $key, $text, MCRYPT_DECRYPT, $iv);
    return @unserialize(trim($p_t));
}

/**
 * Возвращает частоиспользуемые категории
 * @param int $count Максимальный размер возвращаемого списка
 * @param int $barier количество совершённых операций по отдельной категории, необходимое для попадания в Частые Категории
 */
function get_recent_category ($count, $barier)
{
    $array  = array();
    $retval = array();

    if ((int)$barier == 0) {
        $barier = 3;
    }

    if ((int)$count == 0) {
        $count = 10;
    }

    foreach (Core::getInstance()->user->getUserCategory() as $val) {
        if ($val['howoften'] >= $barier) {
            $array[$val['howoften']][] = $val['cat_id'];
        }
    }

    krsort($array);

    foreach ($array as $val) {
        foreach ($val as $v) {
            if ((int)count($retval) <= $count) {
                $retval[] = $v;
            } else {
                //return $retval;
            }
        }
    }
    return $retval;
}

function get_tree_select ($selected = 0 )
{
    //используется дальше в коде, но что то и где инициализируется так и не понял
    $s = '';

    $cat = Core::getInstance()->user->getUserCategory();
    $array = array();
    $result = '';
/////////////
    $arrayoften = array();
    $arr = array();
    $barier = 3;//количество совершённых операций по отдельной категории, необходимое для попадания в ЧастыеКат
    //challenger/вывод часто используемых категорий//список часто используемых категорий за последние три месяца включая текущий.
    foreach ($cat as $val) {
        if ($val['howoften'] >= $barier) {
            if ($val['cat_parent'] == 0) {
                //if (($type == $val['type']) || ($val['type'] == 0))
                    $arr[$val['howoften']][] = "<option value='{$val['cat_id']}' iswaste='{$val['type']}'  id='ca_{$val['cat_id']}' {$s} title='{$val['cat_name']}'>&mdash;&nbsp;{$val['cat_name']}</option>";
            } else {
                //if (($type == $val['type']) || ($val['type'] == 0))
                    $arr[$val['howoften']][] = "<option value='{$val['cat_id']}' iswaste='{$val['type']}' id='ca_{$val['cat_id']}' {$s} title='{$val['cat_name']}'>&mdash;&nbsp;{$val['cat_name']}</option>";
            }
        }
    }
    if ( !empty($arr) ){
        asort($arr);
        $arrayoften[][] = "<optgroup id='often' label=' Часто используемые'></optgroup>";

        $howmuch = 10; // указывает сколько их, последних наиболее часто юзаемых категорий
        $count = 0;
        foreach ($arr as $val){
            if ($count - $howmuch < 0){
                $arrayoften[]=$val;
                $count++;
            }
        }
        $arrayoften[][] = "</optgroup>";
        $arrayoften[][] = "<optgroup id='often' label='Категории'></optgroup>";
        foreach ($arrayoften as $v) {
            $result .= implode('', $v);
        }

    }
    //

    foreach ($cat as $val) {
        if ($selected == $val['cat_id']) {
            $s = "selected='selected'";
        } else {
            $s = ' ';
        }

        if ($val['cat_parent'] == 0) {
            //if (($type == $val['type']) || ($val['type'] == 0))
                $array[$val['cat_id']][] = "<option value='{$val['cat_id']}' iswaste='{$val['type']}'  id='ca_{$val['cat_id']}' {$s} title='{$val['cat_name']}'>{$val['cat_name']}</option>";
        } else {
            //if (($type == $val['type']) || ($val['type'] == 0))
                $array[$val['cat_parent']][] = "<option value='{$val['cat_id']}' iswaste='{$val['type']}' id='ca_{$val['cat_id']}' {$s} title='{$val['cat_name']}'>&mdash;&nbsp;{$val['cat_name']}</option>";
        }
    }
    foreach ($array as $v) {
        $result .= implode('', $v);
    }
    return $result;
}

function get_tree_select2 ($selected = 0, $type = 0)
{
    $cat = Core::getInstance()->user->getUserCategory();
    $array = array();
    $result = '';
/////////////
    $arrayoften = array();
    $arr = array();
    $barier = 3;//количество совершённых операций по отдельной категории, необходимое для попадания в ЧастыеКат
    //challenger/вывод часто используемых категорий//список часто используемых категорий за последние три месяца включая текущий.
    foreach ($cat as $val) {
        if ($val['howoften'] >= $barier) {
            if ($val['cat_parent'] == 0) {
                if ( ($val['type'] == 0) || ($val['type'] == $type) ) {
                    $arr[$val['howoften']][] = "<option value='{$val['cat_id']}' iswaste='{$val['type']}' " .
                        "id='ca_{$val['cat_id']}' title='{$val['cat_name']}'>&mdash;&nbsp;{$val['cat_name']}</option>";
                }
            } else {
                if ( ($type == $val['type']) || ($val['type'] == 0) ) {
                    $arr[$val['howoften']][] = "<option value='{$val['cat_id']}' iswaste='{$val['type']}' " .
                    "id='ca_{$val['cat_id']}' title='{$val['cat_name']}'>&mdash;&nbsp;{$val['cat_name']}</option>";
                }
            }
        }
    }
    if ( !empty($arr) ){
        asort($arr);
        $arrayoften[][] = "<optgroup id='often' label=' Часто используемые'></optgroup>";

        $howmuch = 10; // указывает сколько их, последних наиболее часто юзаемых категорий
        $count = 0;
        foreach ($arr as $val){
            if ($count - $howmuch < 0){
                $arrayoften[]=$val;
                $count++;
            }
        }
        $arrayoften[][] = "</optgroup>";
        $arrayoften[][] = "<optgroup id='often' label='Категории'></optgroup>";
        foreach ($arrayoften as $v) {
            $result .= implode('', $v);
        }

    }
    //

    foreach ($cat as $val) {
        if ($selected == $val['cat_id']) {
            $s = "selected='selected'";
        } else {
            $s = ' ';
        }

        if ($val['cat_parent'] == 0) {
            if (($val['type'] == 0) || ($val['type'] == $type))
                $array[$val['cat_id']][] = "<option value='{$val['cat_id']}' iswaste='{$val['type']}'  id='ca_{$val['cat_id']}' {$s} title='{$val['cat_name']}'>{$val['cat_name']}</option>";
        } else {
            if (($val['type'] == 0) || ($val['type'] == $type))
                $array[$val['cat_parent']][] = "<option value='{$val['cat_id']}' iswaste='{$val['type']}' id='ca_{$val['cat_id']}' {$s} title='{$val['cat_name']}'>&mdash;&nbsp;{$val['cat_name']}</option>";
        }
    }
    foreach ($array as $v) {
        $result .= implode('', $v);
    }
    return $result;
}


/**
 * Альтернатива для симфоньской include_partial('<name>', array());
 * подключает только глобальные партиалы из frontend
 * не проверяет наличие файла, мне лень это писать/копипастить
 *
 * @param  string $templateName
 * @param  array  $vars
 * @throws Exception что бы не повадно было подключать не глобальные шаблоны
 * @return void
 */
function include_partial($templateName, $vars = array()) {
    if (false === strpos($templateName, 'global/')) {
        throw new Exception("Только глобальные партиалы из frontend'а");
    }
    foreach($vars as $kk => $vv) {
        if (preg_match('/^[a-z_]\w+$/i', $kk)) {
            $$kk = $vv;
        }
    }
    $templateName = str_ireplace('global/', '', $templateName);

    include(SYS_DIR_ROOT . "/sf/apps/frontend/templates/_" . $templateName . ".php");
}

/**
 * Кодирует строку в base64 и заменяет символы /, + и =
 * @param string $string
 * @return string закодированная строка
 */
function urlsafe_b64encode($string)
{
    $data = base64_encode($string);
    $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
    return $data;
}

/**
 * Раскодирует строку из urlsafe base64
 * @param string $string закодированная строка
 * @return string раскодированная строка
 */
function urlsafe_b64decode($string)
{
    $data = str_replace(array('-', '_'),array('+', '/'), $string);
    $l    = strlen($data);
    $data = str_pad($data, $l + (4 - ($l % 4)) % 4, '=');
    return base64_decode($data);
}

if (!function_exists("cal_days_in_month")) {
    function cal_days_in_month($cal, $month, $year)
    {
        $leapYear = ($year % 4 == 0) && ($year % 100 != 0 || $year % 400 == 0);

        $days = array(
            1  => 31,
            2  => $leapYear ? 29 : 28,
            3  => 31,
            4  => 30,
            5  => 31,
            6  => 30,
            7  => 31,
            8  => 31,
            9  => 30,
            10 => 31,
            11 => 30,
            12 => 31,
        );

        return $days[(int)$month];
    }
}

