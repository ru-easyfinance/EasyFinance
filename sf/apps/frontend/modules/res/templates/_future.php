<?php
/**
 * Будущие операции из календаря (из запланированных событий)
 *
 * @param array $futureOperations Инф. об операциях и связанных с ними событиями календаря
 */

// поехали мапить
$mapped = array();
foreach ($futureOperations as $operation) {
    $mapped[$operation['id']] = array(
        'id'            => $operation['id'],
        'account_id'    => $operation['account_id'],
        'cat_id'        => $operation['category_id'],
        'source'        => $operation['source_id'],
        'chain'         => $operation['chain_id'],
        'transfer'      => $operation['transfer_account_id'],
        'type'          => $operation['type'],
        'comment'       => $operation['comment'],
        'accepted'      => (int) $operation['accepted'],
        'money'         => $operation['amount'],
        'tags'          => $operation['tags'],

        'date'          => date("d.m.Y", strtotime($operation['date'])),
        //'time'          => $operation['time'],
        'timestamp'     => strtotime($operation['date']),

        'start'         => $operation['CalendarChain']['date_start'],
        'last'          => $operation['CalendarChain']['date_end'],

        'every'         => $operation['CalendarChain']['every_day'],
        'repeat'        => $operation['CalendarChain']['repeat'],
        'week'          => $operation['CalendarChain']['week_days'],

        'mailEnabled'   => '',
        'mailDaysBefore'=> '',
        'mailHour'      => '',
        'mailMinutes'   => '',
        'smsEnabled'    => '',
        'smsDaysBefore' => '',
        'smsHour'       => '',
        'smsMinutes'    => '',
    );
}
?>

res.calendar.future = <?php echo json_encode($mapped) ?>;
