<?php
    define ('INDEX', true);

    // Подключаем конфиг
    require_once dirname(dirname(__FILE__)) . '/include/config.php';

    // Подключаем конфиг
    require_once dirname(dirname(__FILE__)) . '/include/functions.php';

    // Инициализируем одно *единственное* подключение к базе данных
    require_once SYS_DIR_LIBS . 'external/DBSimple/Mysql.php';

    /** @var DbSimple_Generic */
    $db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
    $db->setErrorHandler('databaseErrorHandler');
    
    $db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8',character_set_results = 'utf8'");

    //Получаем список последних системных валют, и как валюту РУБЛЬ
    $result = $db->select("SELECT 1, (SELECT MAX(currency_date) FROM daily_currency WHERE user_id=0) AS currency_date, 1, 0, 0, 0, 0 UNION
        SELECT * FROM daily_currency WHERE 
        currency_date = (SELECT MAX(currency_date) FROM daily_currency WHERE user_id=0)");

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

    //print "\n".$date;

    //Создаём ДОМ объект
    $dom = new DOMDocument('1.0', 'windows-1251');
    $dom->load($linkCBR);
    // Иногда, ЦБР подло меняет разделители знаков в дате на слеш, поэтому перестраховываемся
    $date = str_replace('/', '.', $dom->getElementsByTagName('ValCurs')->item(0)->getAttribute('Date'));
    $date = formatRussianDate2MysqlDate($date);

    //print_r($result);

    //Если на эту дату уже существует, то прекращать выполнение скрипта
    if (trim($result[0]['currency_date']) === trim($date)) {
        exit;
    }

    $sql = '';
    foreach ( $dom->getElementsByTagName('Valute') as $elem ) {
        if (!empty ($sql)) $sql .= ',';
        /*if ($elem->getElementsByTagName('CharCode')->item(0)->nodeValue == 'AUD') {
            $id = 5;
        } elseif ($elem->getElementsByTagName('CharCode')->item(0)->nodeValue == 'GBP') {
            $id = 16;
        } elseif ($elem->getElementsByTagName('CharCode')->item(0)->nodeValue == 'BYR') {
            $id = 6;
        } elseif ($elem->getElementsByTagName('CharCode')->item(0)->nodeValue == 'DKK') {
            $id = 7;
        } elseif ($elem->getElementsByTagName('CharCode')->item(0)->nodeValue == 'USD') {
            $id = 2;
        } elseif ($elem->getElementsByTagName('CharCode')->item(0)->nodeValue == 'EUR') {
            $id = 3;
        } elseif ($elem->getElementsByTagName('CharCode')->item(0)->nodeValue == 'ISK') {
            $id = 8;
        } elseif ($elem->getElementsByTagName('CharCode')->item(0)->nodeValue == 'KZT') {
            $id = 9;
        } elseif ($elem->getElementsByTagName('CharCode')->item(0)->nodeValue == 'CAD') {
            $id = 10;
        } elseif ($elem->getElementsByTagName('CharCode')->item(0)->nodeValue == 'CNY') {
            $id = 11;
        } elseif ($elem->getElementsByTagName('CharCode')->item(0)->nodeValue == 'NOK') {
            $id = 12;
        } elseif ($elem->getElementsByTagName('CharCode')->item(0)->nodeValue == 'XDR') {
            $id = 13;
        } elseif ($elem->getElementsByTagName('CharCode')->item(0)->nodeValue == 'SGD') {
            $id = 14;
        } elseif ($elem->getElementsByTagName('CharCode')->item(0)->nodeValue == 'TRY') {
            $id = 15;
        } elseif ($elem->getElementsByTagName('CharCode')->item(0)->nodeValue == 'UAH') {
            $id = 4;
        } elseif ($elem->getElementsByTagName('CharCode')->item(0)->nodeValue == 'SEK') {
            $id = 17;
        } elseif ($elem->getElementsByTagName('CharCode')->item(0)->nodeValue == 'CHF') {
            $id = 18;
        } elseif ($elem->getElementsByTagName('CharCode')->item(0)->nodeValue == 'JPY') {
            $id = 19;
        } */
        $kod3 = $elem->getElementsByTagName('CharCode')->item(0)->nodeValue;
        $getId = "SELECT cur_id FROM currency WHERE cur_char_code=?";
        $re = $db->query($getId, $kod3);
        $id = $re[0]['cur_id'];

        //Готовим движение курса
        (float)$sum = str_replace(',', '.', $elem->getElementsByTagName('Value')->item(0)->nodeValue);
        (float)$nom = str_replace(',', '.', $elem->getElementsByTagName('Nominal')->item(0)->nodeValue);
        (float)$sum = $sum / $nom;
        if ($sum > $ar[$id]) {
            $dir = '+';
        } elseif ($sum < $ar[$id]) {
            $dir = '-';
        } else {
            $dir = '0';
        }
        $sql .= "('{$id}', 1 ,'{$date}','{$sum}', '{$dir}', '0.0000', '0')";
    }
    if (!empty ($sql)) {
        $sql = "INSERT INTO daily_currency VALUES ". $sql;
        $db->query($sql);
	print " OK";
        //@TODO Записать файл
    }


    //белорусский банк
    $sql = '';
    $dom = new DOMDocument('1.0', 'windows-1251');
    print_r($linkBEL);
    $dom->load($linkBEL);
    foreach ( $dom->getElementsByTagName('Currency') as $elem ) {
        if (!empty ($sql)) $sql .= ',';
        $kod3 = $elem->getElementsByTagName('CharCode')->item(0)->nodeValue;
        $getId = "SELECT cur_id FROM currency WHERE cur_char_code=?";
        $re = $db->query($getId, $kod3);
        $id = $re[0]['cur_id'];

        //Готовим движение курса
        (float)$sum = str_replace(',', '.', $elem->getElementsByTagName('Rate')->item(0)->nodeValue);
        (float)$nom = str_replace(',', '.', $elem->getElementsByTagName('Scale')->item(0)->nodeValue);
        (float)$sum = $sum / $nom;
        if ($sum > $ar[$id]) {
            $dir = '+';
        } elseif ($sum < $ar[$id]) {
            $dir = '-';
        } else {
            $dir = '0';
        }
        $sql .= "('{$id}', 6 ,'{$date}','{$sum}', '{$dir}', '0.0000', '0')";
    }
    if (!empty ($sql)) {
        $sql = "INSERT INTO daily_currency VALUES ". $sql;
        $db->query($sql);
	print " OK";
        //@TODO Записать файл
    }


    //украинский банк
    $sql = '';
    $dom = new DOMDocument('1.0', 'windows-1251');
    print_r($linkUKR);
    $dom->load($linkUKR);
    foreach ( $dom->getElementsByTagName('item') as $elem ) {
        if (!empty ($sql)) $sql .= ',';
        $kod3 = $elem->getElementsByTagName('char3')->item(0)->nodeValue;
        if ($kod3 == 'AZM')
            $kod3 = 'AZN';// хак для хохдяндского банка. у них манат не правильно. в базу пишу в соответсвии с вики и ЦБР, кстати
        $getId = "SELECT cur_id FROM currency WHERE cur_char_code=?";
        $re = $db->query($getId, $kod3);
        $id = $re[0]['cur_id'];

        //Готовим движение курса
        (float)$sum = str_replace(',', '.', $elem->getElementsByTagName('rate')->item(0)->nodeValue);
        (float)$nom = str_replace(',', '.', $elem->getElementsByTagName('size')->item(0)->nodeValue);
        (float)$sum = $sum / $nom;
        if ($sum > $ar[$id]) {
            $dir = '+';
        } elseif ($sum < $ar[$id]) {
            $dir = '-';
        } else {
            $dir = '0';
        }
        $sql .= "('{$id}', 4 ,'{$date}','{$sum}', '{$dir}', '0.0000', '0')";
    }
    if (!empty ($sql)) {
        $sql = "INSERT INTO daily_currency VALUES ". $sql;
        $db->query($sql);
	print " OK";
        //@TODO Записать файл
    }

    //казахстанский банк
    $sql = '';
    $dom = new DOMDocument('1.0', 'windows-1251');
    print_r($linkKAZ);
    $dom->load($linkKAZ);
    foreach ( $dom->getElementsByTagName('item') as $elem ) {
        if (!empty ($sql)) $sql .= ',';
        $kod3 = $elem->getElementsByTagName('title')->item(0)->nodeValue;
        $getId = "SELECT cur_id FROM currency WHERE cur_char_code=?";
        $re = $db->query($getId, $kod3);
        $id = $re[0]['cur_id'];

        //Готовим движение курса
        (float)$sum = str_replace(',', '.', $elem->getElementsByTagName('description')->item(0)->nodeValue);
        (float)$nom = str_replace(',', '.', $elem->getElementsByTagName('quant')->item(0)->nodeValue);
        (float)$sum = $sum / $nom;
        if ($sum > $ar[$id]) {
            $dir = '+';
        } elseif ($sum < $ar[$id]) {
            $dir = '-';
        } else {
            $dir = '0';
        }
        $sql .= "('{$id}', 9 ,'{$date}','{$sum}', '{$dir}', '0.0000', '0')";
    }
    if (!empty ($sql)) {
        $sql = "INSERT INTO daily_currency VALUES ". $sql;
        $db->query($sql);
	print " OK";
        //@TODO Записать файл
    }