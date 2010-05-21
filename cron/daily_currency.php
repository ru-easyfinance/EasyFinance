<?php
    define ('INDEX', true);

    // Подключаем конфиг
    require_once dirname(dirname(__FILE__)) . '/include/config.php';

    // Подключаем конфиг
    require_once dirname(dirname(__FILE__)) . '/include/functions.php';

    error_reporting(E_ALL);
    // Инициализируем одно *единственное* подключение к базе данных
    require_once SYS_DIR_LIBS . 'external/DBSimple/Mysql.php';

    /** @var DbSimple_Generic */
    $db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
    $db->setErrorHandler('databaseErrorHandler');

    $db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8',character_set_results = 'utf8'");

    //Получаем список последних системных валют, и как валюту РУБЛЬ
    $result = $db->select("
        SELECT
            1 as currency_id,
            1 as currency_from,
            (SELECT MAX(currency_date) FROM daily_currency WHERE user_id=0) AS currency_date,
            0 as currency_sum,
            0 as direction,
            0 as currency_user_sum,
            0 as user_id
        UNION
        SELECT
            currency_id,
            currency_from,
            currency_date,
            currency_sum,
            direction,
            currency_user_sum,
            user_id
        FROM daily_currency
        WHERE currency_date = (SELECT MAX(currency_date) FROM daily_currency WHERE user_id=0)");

    $ar = array();
    foreach ($result as $v) {
        $ar[$v['currency_id']] = (float)$v['currency_sum'];
    }

    // Формируем сегодняшнюю дату и ссылку
    $date = date("d/m/Y");
    $dateCBR = str_replace('.', '/', date("m/d/Y"));
    $linkCBR = "http://www.cbr.ru/scripts/XML_daily.asp?date_req=".$date;//центральный банк России
    $linkBEL = "http://www.nbrb.by/Services/XmlExRates.aspx?ondate=".$dateCBR;//национальный банк республики Беларусь
    $linkUKR = "http://bank-ua.com/export/currrate.xml";//национальный хохляндский банк.
    $linkKAZ = "http://www.nationalbank.kz/rss/rates_all.xml";//национальный казахстанский банк

    //Создаём ДОМ объект
    $dom = new DOMDocument('1.0', 'windows-1251');
    $dom->load($linkCBR);

    // Иногда, ЦБР подло меняет разделители знаков в дате на слеш, поэтому перестраховываемся
    $date = str_replace('/', '.', $dom->getElementsByTagName('ValCurs')->item(0)->getAttribute('Date'));
    $date = formatRussianDate2MysqlDate($date);

    //Если на эту дату уже существует, то прекращать выполнение скрипта
    if (trim($result[0]['currency_date']) === trim($date)) {
        die('Has already loaded for date '.$date);
    }

    $sql = '';

    $sql_update = "UPDATE
                currency
            SET
                rate = ?f,
                updated_at = NOW()
            WHERE cur_char_code = ?";

    foreach ( $dom->getElementsByTagName('Valute') as $elem ) {
        if (!empty ($sql)) $sql .= ',';

        $charCode = $elem->getElementsByTagName('CharCode')->item(0)->nodeValue;
        $getId = "SELECT cur_id FROM currency WHERE cur_char_code=?";
        $re = $db->query($getId, $charCode);
        $id = $re[0]['cur_id'];

        //Готовим движение курса
        $sum = (float)str_replace(',', '.', $elem->getElementsByTagName('Value')->item(0)->nodeValue);
        $nom = (float)str_replace(',', '.', $elem->getElementsByTagName('Nominal')->item(0)->nodeValue);
        $sum = $sum / $nom;

        if (isset($ar[$id])) { // Max: прохачил, чтобы не вываливались нотисы
            if ($sum > $ar[$id]) {
                $dir = '+';
            } elseif ($sum < $ar[$id]) {
                $dir = '-';
            } else {
                $dir = '0';
            }
        } else {
            $dir = '0';
        }
        $sql .= "('{$id}', 1 ,'{$date}','".number_format($sum, 6, '.','')."', '{$dir}', '0.0000', '0')";
        $db->query($sql_update, $sum, $charCode);
    }
    if (!empty ($sql)) {
        $sql = "INSERT INTO daily_currency VALUES ". $sql;
        $db->query($sql);
        print "RUS OK\n";
    }

    //белорусский банк
    $sql = '';
    $dom = new DOMDocument('1.0', 'windows-1251');
    $dom->load($linkBEL);
    foreach ( $dom->getElementsByTagName('Currency') as $elem ) {
        if (!empty ($sql)) $sql .= ',';
        $charCode = $elem->getElementsByTagName('CharCode')->item(0)->nodeValue;
        $getId = "SELECT cur_id FROM currency WHERE cur_char_code=?";
        $re = $db->query($getId, $charCode);
        $id = $re[0]['cur_id'];

        //Готовим движение курса
        (float)$sum = str_replace(',', '.', $elem->getElementsByTagName('Rate')->item(0)->nodeValue);
        (float)$nom = str_replace(',', '.', $elem->getElementsByTagName('Scale')->item(0)->nodeValue);
        (float)$sum = $sum / $nom;
        if (isset($ar[$id])) { // Max: прохачил, чтобы не вываливались нотисы
            if ($sum > $ar[$id]) {
                $dir = '+';
            } elseif ($sum < $ar[$id]) {
                $dir = '-';
            } else {
                $dir = '0';
            }
        } else {
            $dir = '0';
        }
        $sql .= "('{$id}', 6 ,'{$date}','".number_format($sum, 6, '.','')."', '{$dir}', '0.0000', '0')";
    }
    if (!empty ($sql)) {
        $sql = "INSERT INTO daily_currency VALUES ". $sql;
        $db->query($sql);
        print "BELRUB OK\n";
    }


    //украинский банк
    $sql = '';
    $dom = new DOMDocument('1.0', 'windows-1251');
    $dom->load($linkUKR);
    foreach ( $dom->getElementsByTagName('item') as $elem ) {
        if (!empty ($sql)) $sql .= ',';
        $charCode = $elem->getElementsByTagName('char3')->item(0)->nodeValue;
        if ($charCode == 'AZM')
            $charCode = 'AZN';// хак для хохдяндского банка. у них манат не правильно. в базу пишу в соответсвии с вики и ЦБР, кстати
        $getId = "SELECT cur_id FROM currency WHERE cur_char_code=?";
        $re = $db->query($getId, $charCode);
        $id = $re[0]['cur_id'];

        //Готовим движение курса
        (float)$sum = str_replace(',', '.', $elem->getElementsByTagName('rate')->item(0)->nodeValue);
        (float)$nom = str_replace(',', '.', $elem->getElementsByTagName('size')->item(0)->nodeValue);
        (float)$sum = $sum / $nom;
        if (isset($ar[$id])) { // Max: прохачил, чтобы не вываливались нотисы
            if ($sum > $ar[$id]) {
                $dir = '+';
            } elseif ($sum < $ar[$id]) {
                $dir = '-';
            } else {
                $dir = '0';
            }
        } else {
            $dir = '0';
        }
        $sql .= "('{$id}', 4 ,'{$date}','".number_format($sum, 6, '.','')."', '{$dir}', '0.0000', '0')";
    }
    if (!empty ($sql)) {
        $sql = "INSERT INTO daily_currency VALUES ". $sql;
        $db->query($sql);
        print "UKR OK\n";
    }

    //казахстанский банк
    $sql = '';
    $dom = new DOMDocument('1.0', 'windows-1251');
    $dom->load($linkKAZ);
    foreach ( $dom->getElementsByTagName('item') as $elem ) {
        if (!empty ($sql)) $sql .= ',';
        $charCode = $elem->getElementsByTagName('title')->item(0)->nodeValue;
        $getId = "SELECT cur_id FROM currency WHERE cur_char_code=?";
        $re = $db->query($getId, $charCode);
        $id = $re[0]['cur_id'];

        //Готовим движение курса
        (float)$sum = str_replace(',', '.', $elem->getElementsByTagName('description')->item(0)->nodeValue);
        (float)$nom = str_replace(',', '.', $elem->getElementsByTagName('quant')->item(0)->nodeValue);
        (float)$sum = $sum / $nom;
        if (isset($ar[$id])) { // Max: прохачил, чтобы не вываливались нотисы
            if ($sum > $ar[$id]) {
                $dir = '+';
            } elseif ($sum < $ar[$id]) {
                $dir = '-';
            } else {
                $dir = '0';
            }
        } else {
            $dir = '0';
        }
        $sql .= "('{$id}', 9 ,'{$date}','".number_format($sum, 6, '.','')."', '{$dir}', '0.0000', '0')";
    }
    if (!empty ($sql)) {
        $sql = "INSERT INTO daily_currency VALUES ". $sql;
        $db->query($sql);
        print "KAZ OK\n";
    }
