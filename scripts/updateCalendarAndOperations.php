<?php

define ('INDEX', true);

require_once dirname(dirname(__FILE__)) . "/include/config.php";
require_once dirname(dirname(__FILE__)) . "/models/operation.model.php";

$dsn      = 'mysql:dbname='.SYS_DB_BASE.';host=127.0.0.1';
$user     = SYS_DB_USER;
$password = SYS_DB_PASS;

$mysql = mysql_connect(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PASS);
mysql_select_db(SYS_DB_BASE, $mysql);

mysql_query( "SET character_set_client = utf8;" );
mysql_query( "SET character_set_results = utf8;" );
mysql_query( "SET character_set_connection = utf8;" );

// Копируем данные из старого календаря в новый
// Ставим ограничения на ошибочные операции, с которыми должны разобраться потом
$sql = "INSERT INTO calendar_chains (`id`, `user_id`, `start`, `last`, `every`, `repeat`, `week`)
    SELECT o.id, o.user_id, o.start, o.last, o.every, o.repeat, o.week FROM calend o
    WHERE every <= 365";

$result = mysql_query( $sql );


// Выбираем все запланированные события
$sql = "SELECT c.id AS chain, c.user_id, c.type AS type_event, c.title, c.time, c.comment, c.amount,
    c.cat_id, c.account_id, c.op_type AS `type`, ce.`date` FROM calend c
    LEFT JOIN calendar_events ce ON c.id=ce.id
    WHERE ce.accept=0";

$result = mysql_query( $sql );

$sql = '';
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    if ( ! empty ( $sql ) ) $sql .= ',';
    $sql .= "('"
        . $row['user_id'] . "','"
        . ( ( $row['type'] == 0 ) ?
            ( -1 * abs($row['amount']) )
            : $row['amount'] ) . "','"
        . $row['date'] . "','"
        . $row['cat_id'] . "','"
        . $row['account_id'] . "','"
        . ! $row['type'] . "','"
        . $row['type'] . "','"
        . ( ( $row['type_event'] == 'e' ) ? "<Автоматически преобразованое событие>\n" : "" ) . $row['comment'] . "','"
        . "','"
        . "0', '"
        . $row['chain'] . "', NOW())";
}

$sql = 'INSERT INTO `operation` (`user_id`, `money`, `date`, `cat_id`, `account_id`,
    `drain`, `type`, `comment`, `tags`, `accepted`, `chain_id`, `dt_create`) VALUES ' . $sql;

$result = mysql_query( "START TRANSACTION;" );
$result = mysql_query( $sql );
$result = mysql_query( "COMMIT;" );

print_r($result);

mysql_close($mysql);


