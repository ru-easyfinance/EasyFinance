<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Сборник полезных функций
 * @author Max Kamashev "ukko" <max.kamashev@gmail.com>
 * @author korogen
 * @copyright http://home-money.ru/
 * SVN $Id$
 */

// Устанавливаем константу последней ревизии
//TODO Переписать на использование утилиты svnversion
$Rev = "$LastChangedRevision$"; //DO NOT EDIT!
$Rev = explode(" ", $Rev);
define('REVISION', $Rev[1]);

/**
 * Реализация функции __autoload для всех классов
 * @param string $class_name Строка с названием класса
 * @access protected Уровень доступа
 * @return void
 */
function __autoload($class_name) {
    $array = explode("_",$class_name);
    // Грузим контроллеры
    if ( $array[1] == 'Controller' &&
        file_exists(SYS_DIR_ROOT .'/controllers/'. strtolower($array[0]) . '.controller.php' ) ) {
            require_once SYS_DIR_ROOT .'/controllers/'. strtolower($array[0]). '.controller.php';
    // Загружаем модули /modules
    } elseif ( $array[1] == 'Model' &&
        file_exists(SYS_DIR_ROOT . '/models/' . strtolower($array[0]) . '.model.php') ) {
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
            trigger_error("*USER ERROR* [$errno] $errstr<br />\n".
            "  User error on line $errline in file $errfile<br />\n");
            exit(1);
        case E_USER_WARNING:
            trigger_error("*USER WARNING* [$errno] $errstr<br />\n".
            "  User warning on line $errline in file $errfile<br />\n");
            break;
        case E_USER_NOTICE:
            $tpl->append("notice", "*USER NOTICE* [$errno] $errstr<br />\n".
            "  User notice on line $errline in file $errfile<br />\n");
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
    header("HTTP/1.0 404 Not Found");
    die('404');
    //die(file_get_contents('/404.html'));
}

/**
 * Форматирует русское представление даты, например: <code>20.02.2009</code> в формат даты mysql <code>2009-02-20</code>
 * @param <string> $date
 * @return <string>
 */
function formatRussianDate2MysqlDate($date)
{
    if (empty ($date)) {
        return false;
    }
    $date = explode('.', $date);
    if (count($date) == 3) {
        return (int)$date[2].'-'.(int)$date[1].'-'.(int)$date[0];
    } else {
        return false;
    }
}

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

/**
 * @deprecated
 * @param $data
 * @return unknown_type
 */
function checkModules ($data)
{
    if (file_exists(SYS_DIR_MOD."/".$data.".php"))
    {
        return true;
    }else{
        return false;
    }
}

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

function make_float($var)
{
    $var = str_replace(',','.',$var);
    return (float)$var;
}

function make_report_outcome_for_period($data, $drain, $sys_currency)
{
    $current_bill = '';

    /*for ($i=0; $i<count($data); $i++)
     {
     if ($data[$i]['bill_name'] != $current_bill)
     {
     $current_bill = $data[$i]['bill_name'];

     for ($k=0; $k<count($data); $k++)
     {
     if ($data[$k]['bill_name'] == $current_bill)
     {
     if ($data[$k]['cat_parent'] == 0)
     {
     $current_cat_name = $data[$k]['cat_name'];
     $current_cat_id = $data[$k]['cat_id'];

     for ($n=0; $n<count($data); $n++)
     {
     if ($data[$n]['bill_name'] == $current_bill && ($data[$n]['cat_parent'] == $current_cat_id || $data[$n]['cat_id'] == $current_cat_id))
     {
     if ($data[$n]['cat_id'] == $current_cat_id && $data[$n]['cat_parent'] == 0)
     {
     $result[$current_bill][$current_cat_name]['cat_parent'] = $data[$n]['cat_id'];
     $result[$current_bill][$current_cat_name]['cat_parent_sum'] = $data[$n]['total_sum'];
     }else{
     $result[$current_bill][$current_cat_name]['cat_id'][] = $data[$n]['cat_id'];
     $result[$current_bill][$current_cat_name]['cat_name'][] = $data[$n]['cat_name'];
     $result[$current_bill][$current_cat_name]['total_sum'][] = $data[$n]['total_sum'];
     $result[$current_bill][$current_cat_name]['cur_name'][] = $data[$n]['cur_name'];
     }
     }
     }
     }
     }
     }
     }
     }*/

    if (empty($p_report['currency']))
    {
        $p_report['currency'] = 1;
    }
    if (empty($_POST['report']['currency']))
    {
        $_POST['report']['currency'] = 1;
    }

    for ($i = 0; $i < count($data); $i++)
    {
        $current_bill = $data[$i]['bill_name'];

        if ($data[$i]['cat_parent'] == 0)
        {
            $current_parent_cat_name = $data[$i]['cat_name'];

            $result[$current_bill][$current_parent_cat_name]['cat_parent'] = $data[$i]['cat_id'];

            if ($data[$i]['cur_id'] > '1' && $data[$i]['group_account'] == 'on')
            {
                //приводим к рублю
                $tmp_cat_sum = $data[$i]['total_sum'] * $sys_currency[$data[$i]['cur_id']];
                //получаем сумму в выбранной валюте
                $tmp_cat_sum = round(($tmp_cat_sum / $sys_currency[$p_report['currency']]),2);
            }else{
                //если валюта категории рубль, то конвертируем в выбранную валюту
                $tmp_cat_sum = round(($data[$i]['total_sum'] / $sys_currency[$_POST['report']['currency']]),2);
            }
            $result[$current_bill][$current_parent_cat_name]['cat_parent_sum'] = $tmp_cat_sum;
            //$data[$i]['total_sum'];
        }
        else
        {
            $current_parent_cat_name = $data[$i]['parent_cat_name'];

            $result[$current_bill][$current_parent_cat_name]['cat_parent'] = $data[$i]['parent_cat_id'];

            $result[$current_bill][$current_parent_cat_name]['cat_id'][] = $data[$i]['cat_id'];
            $result[$current_bill][$current_parent_cat_name]['cat_name'][] = $data[$i]['cat_name'];
            if ($data[0]['cur_id'] > '1' && $data[$i]['group_account'] == 'on')
            {
                //приводим к рублю
                $tmp_cat_sum = $data[$i]['total_sum'] * $sys_currency[$data[$i]['cur_id']];
                //получаем сумму в выбранной валюте
                $tmp_cat_sum = round(($tmp_cat_sum / $sys_currency[$_POST['report']['currency']]),2);
            }else{
                //если валюта категории рубль, то конвертируем в выбранную валюту
                $tmp_cat_sum = round(($data[$i]['total_sum'] / $sys_currency[$_POST['report']['currency']]),2);
            }
            $result[$current_bill][$current_parent_cat_name]['total_sum'][] = $tmp_cat_sum;
            //$data[$i]['total_sum'];
            $result[$current_bill][$current_parent_cat_name]['cur_name'][] = $data[$i]['cur_name'];

        }

    }



    if ($drain == 1)
    {
        $img = "img/red.gif";
    }else{
        $img = "img/green.gif";
    }

    if (!empty($result))
    {
        foreach ($result as $bill_name => $value)
        {
            if ($_POST['report']['group_account'] == 'on')
            {
                $tmpl_bill_name = "Общий счет";
            }else{
                $tmpl_bill_name = $bill_name;
            }
            $tmpl .= "<tr><td colspan=3 class=report_bill_name>".$tmpl_bill_name."</td></tr>";
            $total_bill_sum = 0;
            $total_bill_sum = count_bill_total_sum($data, $bill_name);

            if ($data[$i]['cur_id'] > '1' && $data[$i]['group_account'] == 'on')
            {
                //приводим к рублю
                $tmp_cat_sum = $total_bill_sum * $sys_currency[$data[$i]['cur_id']];
                //получаем сумму в выбранной валюте
                $tmp_cat_sum = round(($tmp_cat_sum / $sys_currency[$p_report['currency']]),2);
            }else{
                //если валюта категории рубль, то конвертируем в выбранную валюту
                $tmp_cat_sum = round(($total_bill_sum / $sys_currency[$_POST['report']['currency']]),2);
            }
            $total_bill_sum = $tmp_cat_sum;

            foreach ($value as $cat_parent => $value2)
            {
                //$cat_total_sum = $value2['cat_parent_sum'] + count_cat_total_sum($value2['total_sum']);
                //$total_bill_sum + $cat_total_sum;
                $cat_total_sum = $value2['cat_parent_sum'];

                $percent = round(($cat_total_sum * 100) / $total_bill_sum, 2);
                if ($percent > 0)
                {
                    $tmpl_percent = "&nbsp;(".$percent."%)";
                }else{
                    $tmpl_percent = "";
                }

                $tmpl .= "<tr onMouseOver=this.style.backgroundColor='#f8f6ea';
						   		onMouseOut=this.style.backgroundColor='#FFFFFF';>
				<td style='padding-left:25px;' width=25% class=cat_add>".$cat_parent."</td><td width=15% class=cat_add>".$cat_total_sum."".$tmpl_percent."</td><td class=cat_add><img src=".$img." width='".$percent."%' height=10></td></tr>";

                if (!empty($value2['cat_name']))
                {
                    for($i=0; $i<count($value2['cat_name']);$i++)
                    {
                        $percent = round(($value2['total_sum'][$i] * 100) / $total_bill_sum, 2);
                        if ($percent > 0)
                        {
                            $tmpl_percent = "&nbsp;(".$percent."%)";
                        }else{
                            $tmpl_percent = "";
                        }
                        $tmpl .= "<tr onMouseOver=this.style.backgroundColor='#f8f6ea';
						   		onMouseOut=this.style.backgroundColor='#FFFFFF';>
						<td style='padding-left:50px;' width=25% class=cat_add>".$value2['cat_name'][$i]."</td><td width=15% class=cat_add>".$value2['total_sum'][$i]."".$tmpl_percent."</td><td class=cat_add><img src=".$img." width='".$percent."%' height=10></td></tr>";
                    }
                }
            }
            for ($i=0; $i<count($_SESSION['user_currency']); $i++)
            {
                if ($_SESSION['user_currency'][$i]['cur_id'] == $_POST['report']['currency'])
                {
                    $tmpl_total_bill_sum = $total_bill_sum."&nbsp;".$_SESSION['user_currency'][$i]['cur_name'];
                }
            }
            $tmpl .= "<tr><td>&nbsp;</td><td class=report_month>".$tmpl_total_bill_sum."</td><td>&nbsp;</td></tr>";
        }

        $f_tmpl = "<table width=100% border=0><tr><td colspan=3><b>".$_POST['report']['date_from']." - ".$_POST['report']['date_to']."</b></td></tr>".$tmpl."</table>";

        return $f_tmpl;
    }

}

function make_report_outcome_grouped($data, $drain, $sys_currency, $sys_month)
{
    $current_bill = '';

    if (empty($p_report['currency']))
    {
        $p_report['currency'] = 1;
    }
    if (empty($_POST['report']['currency']))
    {
        $_POST['report']['currency'] = 1;
    }

    for ($i = 0; $i < count($data); $i++)
    {
        $current_bill = $data[$i]['date_new'];

        if ($data[$i]['cat_parent'] == 0)
        {
            $current_parent_cat_name = $data[$i]['cat_name'];

            $result[$current_bill][$current_parent_cat_name]['cat_parent'] = $data[$i]['cat_id'];

            if ($data[$i]['cur_id'] > '1')
            {
                //приводим к рублю
                $tmp_cat_sum = $data[$i]['total_sum'] * $sys_currency[$data[$i]['cur_id']];
                //получаем сумму в выбранной валюте
                $tmp_cat_sum = round(($tmp_cat_sum / $sys_currency[$p_report['currency']]),2);
            }else{
                //если валюта категории рубль, то конвертируем в выбранную валюту
                $tmp_cat_sum = round(($data[$i]['total_sum'] / $sys_currency[$_POST['report']['currency']]),2);
            }
            $result[$current_bill][$current_parent_cat_name]['cat_parent_sum'] = $tmp_cat_sum;
            //$data[$i]['total_sum'];
        }
        else
        {
            $current_parent_cat_name = $data[$i]['parent_cat_name'];

            $result[$current_bill][$current_parent_cat_name]['cat_parent'] = $data[$i]['parent_cat_id'];

            $result[$current_bill][$current_parent_cat_name]['cat_id'][] = $data[$i]['cat_id'];
            $result[$current_bill][$current_parent_cat_name]['cat_name'][] = $data[$i]['cat_name'];
            if ($data[0]['cur_id'] > '1')
            {
                //приводим к рублю
                $tmp_cat_sum = $data[$i]['total_sum'] * $sys_currency[$data[0]['cur_id']];
                //получаем сумму в выбранной валюте
                if ($_GET['et'] == 'on')
                {
                    pre($sys_currency);
                }
                $tmp_cat_sum = round(($tmp_cat_sum / $sys_currency[$_POST['report']['currency']]),2);
            }else{
                //если валюта категории рубль, то конвертируем в выбранную валюту
                $tmp_cat_sum = round(($data[$i]['total_sum'] / $sys_currency[$_POST['report']['currency']]),2);
            }
            $result[$current_bill][$current_parent_cat_name]['total_sum'][] = $tmp_cat_sum;
            //$data[$i]['total_sum'];
            $result[$current_bill][$current_parent_cat_name]['cur_name'][] = $data[$i]['cur_name'];

        }

    }



    if ($drain == 1)
    {
        $img = "img/red.gif";
    }else{
        $img = "img/green.gif";
    }

    if (!empty($result))
    {
        foreach ($result as $bill_name => $value)
        {
            if ($_POST['report']['group_account'] == 'on')
            {
                $tmpl_bill_name = "Общий счет";
            }else{
                $tmpl_bill_name = $bill_name;
            }

            list($month,$year) = explode(".", $tmpl_bill_name);

            $tmpl_bill_name2 = $sys_month[$month]."&nbsp;".$year;
            $tmpl .= "<tr><td colspan=3 class=report_bill_name>".$tmpl_bill_name2."</td></tr>";
            $total_bill_sum = 0;
            $total_bill_sum = count_month_total_sum($data, $bill_name);

            if ($data[0]['cur_id'] > '1')
            {
                //приводим к рублю
                $tmp_cat_sum = $total_bill_sum * $sys_currency[$data[0]['cur_id']];
                //получаем сумму в выбранной валюте
                $tmp_cat_sum = round(($tmp_cat_sum / $sys_currency[$_POST['report']['currency']]),2);
            }else{
                //если валюта категории рубль, то конвертируем в выбранную валюту

                $tmp_cat_sum = round(($total_bill_sum / $sys_currency[$_POST['report']['currency']]),2);
            }

            $total_bill_sum = $tmp_cat_sum;

            foreach ($value as $cat_parent => $value2)
            {
                //$cat_total_sum = $value2['cat_parent_sum'] + count_cat_total_sum($value2['total_sum']);
                //$total_bill_sum + $cat_total_sum;
                $cat_total_sum = $value2['cat_parent_sum'];

                $percent = round(($cat_total_sum * 100) / $total_bill_sum, 2);
                if ($percent > 0)
                {
                    $tmpl_percent = "&nbsp;(".$percent."%)";
                }else{
                    $tmpl_percent = "";
                }

                $tmpl .= "<tr onMouseOver=this.style.backgroundColor='#f8f6ea';
						   		onMouseOut=this.style.backgroundColor='#FFFFFF';>
				<td style='padding-left:25px;' width=25% class=cat_add>".$cat_parent."</td><td width=15% class=cat_add>".$cat_total_sum."".$tmpl_percent."</td><td class=cat_add><img src=".$img." width='".$percent."%' height=10></td></tr>";

                if (!empty($value2['cat_name']))
                {
                    for($i=0; $i<count($value2['cat_name']);$i++)
                    {
                        $percent = round(($value2['total_sum'][$i] * 100) / $total_bill_sum, 2);
                        if ($percent > 0)
                        {
                            $tmpl_percent = "&nbsp;(".$percent."%)";
                        }else{
                            $tmpl_percent = "";
                        }
                        $tmpl .= "<tr onMouseOver=this.style.backgroundColor='#f8f6ea';
						   		onMouseOut=this.style.backgroundColor='#FFFFFF';>
						<td style='padding-left:50px;' width=25% class=cat_add>".$value2['cat_name'][$i]."</td><td width=15% class=cat_add>".$value2['total_sum'][$i]."".$tmpl_percent."</td><td class=cat_add><img src=".$img." width='".$percent."%' height=10></td></tr>";
                    }
                }
            }
            for ($i=0; $i<count($_SESSION['user_currency']); $i++)
            {
                if ($_SESSION['user_currency'][$i]['cur_id'] == $_POST['report']['currency'])
                {
                    $tmpl_total_bill_sum = $total_bill_sum."&nbsp;".$_SESSION['user_currency'][$i]['cur_name'];
                }
            }
            $tmpl .= "<tr><td>&nbsp;</td><td class=report_month>".$tmpl_total_bill_sum."</td><td>&nbsp;</td></tr>";
        }

        $f_tmpl = "<table width=100% border=0><tr><td colspan=3><b>".$_POST['report']['date_from']." - ".$_POST['report']['date_to']."</b></td></tr>".$tmpl."</table>";

        return $f_tmpl;
    }

}

function count_cat_total_sum($data)
{

    $summ = 0;
    for($i=0; $i<count($data); $i++)
    {
        $summ = $summ + $data[$i];
    }

    return $summ;
}

function count_bill_total_sum($data, $bill)
{
    $summ = 0;

    for($i=0; $i<count($data); $i++)
    {
        if ($data[$i]['bill_name'] == $bill)
        {
            $summ = $summ + $data[$i]['total_sum'];
        }

    }

    return $summ;
}

function count_month_total_sum($data, $bill)
{
    $summ = 0;

    for($i=0; $i<count($data); $i++)
    {
        if ($data[$i]['date_new'] == $bill)
        {
            $summ = $summ + $data[$i]['total_sum'];
        }

    }

    return $summ;
}

function pre($data, $exit = false)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";

    if (!empty($exit))
    {
        exit();
    }
}

?>