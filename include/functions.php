<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
* Сборник полезных функций
* @author Max Kamashev "ukko" <max.kamashev@gmail.com>
* @author korogen
* @copyright http://home-money.ru/
* SVN $Id$
*/

require_once dirname(__FILE__).'/version.php';

/**
* Реализация функции __autoload для всех классов
* @param string $class_name Строка с названием класса
* @access protected Уровень доступа
* @return void
*/
function __autoload($class_name) {
    $array = explode("_",$class_name);
    // Грузим контроллеры
    if ( $array[1] == 'Controller' && file_exists(SYS_DIR_ROOT .'/controllers/'. strtolower($array[0]) . '.controller.php' ) ) {
            require_once SYS_DIR_ROOT .'/controllers/'. strtolower($array[0]). '.controller.php';
    // Загружаем модули /modules
    } elseif ( $array[1] == 'Model' && file_exists(SYS_DIR_ROOT . '/models/' . strtolower($array[0]) . '.model.php') ) {
            require_once SYS_DIR_ROOT . '/models/' . strtolower($array[0]) . '.model.php';
    // Загружаем дополнительные классы /core
    } elseif (file_exists(SYS_DIR_LIBS . strtolower($array[0]) . '.class.php')) {
        require_once SYS_DIR_LIBS . strtolower($array[0]) . '.class.php';
    } else {
        trigger_error("Не удалось найти файл с классом {$class_name} ".var_dump($array), E_USER_ERROR);
    }
}

// Код обработчика ошибок SQL.
function databaseErrorHandler($message, $info)
{
    // Если использовалась @, ничего не делать.
    if (!error_reporting()) return;
    // Выводим подробную информацию об ошибке.
    echo "SQL Error: $message<br><pre>";
    print_r($info);
    echo "</pre>";
    exit();
}

function databaseLogger($db, $sql)
{
    $caller = $db->findLibraryCaller();
    trigger_error(end(end(@$caller['object']->_placeholderCache)), E_USER_NOTICE);
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
    $tpl = Core::getInstance()->tpl;
    //TODO Нотисы и варнинги - показывать, ерроры - не показывать, только записывать в лог
    switch ($errno) {
        case E_USER_ERROR:
            trigger_error("*USER ERROR* [$errno] $errstr line: $errline in file: $errfile");
            exit(1);
        case E_USER_WARNING:
            trigger_error("*USER WARNING* [$errno] $errstr  line: $errline in file: $errfile");
            break;
        case E_USER_NOTICE:
            if (substr($errstr, 0, 4) == '  --'){
                FirePHP::getInstance(true)->log($errstr);
            } else {
                FirePHP::getInstance(true)->info($errstr);
            }
//            $tpl->append("notice", '"'.nl2br(htmlspecialchars($errstr)).' line: $errline in file: $errfile"');
            break;
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
    header("HTTP/1.1 404 Not Found");
    die('404');
    //die(file_get_contents('/404.html'));
}

/**
* Форматирует русское представление даты, например: <code>20.02.2009</code> в формат даты mysql <code>2009-02-20</code>
* @param <string> $date Дата, в формате дд.мм.гггг
* @param <string> $time Время в формате чч:мм
* @return <string>
*/
function formatRussianDate2MysqlDate($date, $time)
{
    /**
    * Собирает в себе отформатированную дату
    * @var <string>
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
* @param <string> $date
* @return <int> Unix Timestamp или false в случае ошибки
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
 * @param <string> $text Текст, который требуется зашифровать
 * @param <string> $key // 24 битный ключ
 * @return <string> Возвращает зашифрованный ключ в обёртке base64
 */
function encrypt($text, $key) {
    $text = trim(serialize($text));
    $iv = substr(md5($key), 0,mcrypt_get_iv_size (MCRYPT_CAST_256,MCRYPT_MODE_CFB));
    $c_t = mcrypt_cfb (MCRYPT_CAST_256, $key, $text, MCRYPT_ENCRYPT, $iv);
    return base64_encode($c_t);
}

 /**
 * Расшифровывает данные с помощью расширения mcrypt
 * @param <string> $text Текст, который требуется разшифровать
 * @param <string> $key // 24 битный ключ
 * @return <string> Расшифрованную строку
 */
function decrypt($text, $key) {
    $text = base64_decode($text);
    $iv = substr(md5($key), 0,mcrypt_get_iv_size (MCRYPT_CAST_256,MCRYPT_MODE_CFB));
    $p_t = mcrypt_cfb (MCRYPT_CAST_256, $key, $text, MCRYPT_DECRYPT, $iv);
    return unserialize(trim($p_t));
}

/**
* Проверяет корректность почтового ящика
* @param $email string
* @return bool
*/
function validate_email($email)
{
    if (!empty($email)) {
        if (preg_match('/^[a-z0-9&\'\.\-_\+]+@[a-z0-9\-]+\.([a-z0-9\-]+\.)*?[a-z]+$/is', $email)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function make_float($var)
{
    return (float)str_replace(',','.',$var);
}

function get_tree_select ($selected = 0)
{
    $result = '';
    $cat = Core::getInstance()->user->getUserCategory();
    foreach ($cat as $val) {
        if ($selected == $val['cat_id']) {
            $s = "selected='selected'";
        } else {
            $s = ' ';
        }

        if ($val['cat_parent'] == 0) {
            $result .= "<option value='{$val['cat_id']}' id='ca_{$val['cat_id']}' {$s} title='{$val['cat_name']}'>{$val['cat_name']}</option>";
        } else {
            $result .= "<option value='{$val['cat_id']}' id='ca_{$val['cat_id']}' {$s} title='{$val['cat_name']}'>&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;{$val['cat_name']}</option>";
        }
    }
    return $result;
}

/**
*  @deprecated Избавиться от неё позже
*/
function get_three ($data, $parent = 0)
{
global $three;
static $counter = 0;

$counter += 1;

for ($i = 0; $i < count ($data); $i ++)
{
if ($data [$i] ['cat_parent'] == $parent)
{
$three .= "<tr onMouseOver=this.style.backgroundColor='#f8f6ea';
						   onMouseOut=this.style.backgroundColor='#FFFFFF';>
						   <td class=cat_add width=100%>";
for ($j = 0; $j < $counter; $j ++)
$three .= "<img src=img/tree/empty.gif>";

$folderopen = "<img src=img/tree/folderopen.gif>";

if ($counter == 2)
{
$folderopen = "<img src=img/tree/joinbottom.gif><img src=img/tree/page.gif>";
}

$three .= "
							".$folderopen."
								<a href=index.php?modules=category&action=edit&id=".$data[$i]['cat_id'].">".$data[$i]['cat_name']. "</a>
							</td>
						</tr>";

get_three ($data, $data [$i] ['cat_id']);
}
}
$counter -= 1;
return $three;
}

/**
*  @deprecated Избавиться от неё позже
*/
function get_three_select ($data, $parent = 0, $selected = 0)
{
global $three_select;
static $counter_select = 0;

$counter_select += 1;

for ($i = 0; $i < count ($data); $i ++)
{
if ($data [$i] ['cat_parent'] == $parent)
{
for ($j = 0; $j < $counter_select; $j ++)

$folderopen = "";

if ($counter_select == 2)
{
$folderopen = "&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;";
}

if ($data[$i]['cat_id'] == $selected)
{
$select = "selected = 'selected'";
//echo $data[$i]['cat_id']." = ".$selected." - $select<br>";
$check = true;
}else{
$select = "";
}
$three_select .= "
								<option value=".$data[$i]['cat_id']." ".$select.">".$folderopen."".$data[$i]['cat_name']."</option>";

get_three_select ($data, $data [$i] ['cat_id'], $selected);
}
}
$counter_select -= 1;

return $three_select;
}

/**
*  @deprecated Избавиться от неё позже
*/
function get_three_select2 ($data, $parent = 0, $selected = 0)
{
global $three_select2;
static $counter_select2 = 0;

$counter_select2 += 1;

for ($i = 0; $i < count ($data); $i ++)
{
if ($data [$i] ['cat_parent'] == $parent)
{
for ($j = 0; $j < $counter_select; $j ++)

$folderopen = "";

if ($counter_select2 == 2)
{
$folderopen = "&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;";
}

if ($data[$i]['cat_id'] == $selected)
{
$select = "selected = 'selected'";
//echo $data[$i]['cat_id']." = ".$selected." - $select<br>";
$check = true;
}else{
$select = "";
}
$three_select2 .= "
								<option value=".$data[$i]['cat_id']." ".$select.">".$folderopen."".$data[$i]['cat_name']."</option>";

get_three_select2 ($data, $data [$i] ['cat_id'], $selected);
}
}
$counter_select2 -= 1;

return $three_select2;
}

/**
*  @deprecated Избавиться от неё позже
*/
function get_number_format($number)
{
if ($number > 0)
{
return '<span style="color: green;">'.number_format($number, 2, '.', ' ').'</span>';
}
else
{
if (!empty($number))
{
$result = substr($number, 1);
$result = "-".number_format($result, 2, '.', ' ');
return '<span style="color: red;">'.$result.'</span>';
}
return $number;
}
}