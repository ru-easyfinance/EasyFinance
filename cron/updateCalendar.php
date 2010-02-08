<?php
/*
 * Скрипт для конвертирования событий календаря из старого формата в новый
 */
define('INDEX', true);
require_once dirname(dirname ( __FILE__ ) ) . '/include/config.php';
require_once dirname(dirname ( __FILE__ ) ) . '/core/external/DBSimple/Generic.php';

$db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);

print "get users accounts\n";

// Получаем все наличные счета пользователей (почему-то счета не записывались раньше в базу)
$rows = $db->query("SELECT user_id, account_id FROM accounts a WHERE a.account_type_id=1 GROUP BY user_id");
$accounts = array();
foreach ($rows as $key => $value) {
    $accounts[$value['user_id']] = $value['account_id'];
}

print "generate chains\n";
// Получаем все цепочки
$rows = $db->query("SELECT * FROM calendar c GROUP BY chain");

$sql = '';
foreach ($rows as $k => $v) {
    $type = ($v['event']=='cal')? 'c': 'p';
    $week = '0000000';
    if ($v['type_repeat'] == 0) {         // 0 - Без повторения
        $every = 0;
    } elseif ( $v['type_repeat'] == 1 ) { //1 - Ежедневно
        $every = 1;
    } elseif ( $v['type_repeat'] == 3 ) { // 3 - Каждый Пн., Ср. и Пт.,
        $every = 7;
        $week = '1010100';
    } elseif ( $v['type_repeat'] == 4 ) { // 4 - Каждый Вт. и Чт.,
        $every = 7;
        $week = '0101000';
    } elseif ( $v['type_repeat'] == 5 ) { // 5 - По будням,
        $every = 7;
        $week = '1111100';
    } elseif ( $v['type_repeat'] == 6 ) { // 6 - По выходным
        $every = 7;
        $week = '0000011';
    } elseif ( $v['type_repeat'] == 7 ) { // 7 - Еженедельно
        $every = 7;
        $week = '0000000'; //@TODO
    } elseif ( $v['type_repeat'] == 30 ) { // 30 - Ежемесячно,
        $every = 30;
        $week = '0000000';
    } elseif ( $v['type_repeat'] == 90 ) { // 90 - Ежеквартально,
        $every = 90;
        $week = '0000000';
    } elseif ( $v['type_repeat'] == 365 ) { // 365 - Ежегодно
        $every = 365;
        $week = '0000000';
    }
    $time = substr($v['near_date'], -8);

// *** Старые поля
//  `id`   `user_id`   `chain`   `title`  `near_date`
//  `start_date`  `last_date`  `type_repeat`  `count_repeat`
//  `comment`  `dt_create`  `dt_edit`  `infinity`  `week`
//  `event`  `amount`  `category`  `close`

// *** Новые поля
//  `id`   `user_id`  `type`   `title`   `start`   `date`
//  `last`   `time`   `every`   `repeat`   `week`   `comment`
//  `amount`   `cat_id`   `account_id`   `op_type`   `tags`

    if ( $v['amount'] > 0 ) {
        $op_type = 1;
    } else {
        $op_type = 0;
    } 

    if (!empty ($sql) ) $sql .= ',';
    $sql .= "('{$v['chain']}', '{$v['user_id']}','{$type}', '{$v['title']}', '{$v['start_date']}', 
    '0000-00-00', '{$v['last_date']}', '{$time}', '{$every}', '{$v['count_repeat']}', '{$week}',
    '{$v['comment']}', '{$v['amount']}', '{$v['category']}', '{$accounts[$v['user_id']]}', '{$op_type}', '')";
}

$sql = "INSERT INTO calend (`id`, `user_id`,`type`,`title`,`start`,`date`,`last`,`time`,`every`,
        `repeat`,`week`,`comment`,`amount`,`cat_id`,`account_id`,`op_type`,`tags`) VALUES " . $sql;

print "write chains\n";
$db->query( $sql );

print "generate events\n";

$events = $db->query("SELECT chain, DATE(near_date) as `date`, `close` FROM calendar c ");

$sql = '';
foreach ($events as $v) {
    if (!empty ($sql) ) $sql .= ',';
    $sql .= "('{$v['chain']}', '{$v['date']}', '{$v['close']}')";
}

$sql = "INSERT INTO calendar_events (`cal_id`, `date`, `accept`) VALUES " . $sql;

print "write events\n";

$db->query( $sql );

print "ok\n";

