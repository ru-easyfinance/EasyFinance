<?php
/**
 * Данные для вывода виджета счетов res.accounts
 * TODO: наверное сюда же сделать res.accountsRecent, если оно нужно
 *
 * @param  array $accounts массив счетов
 * @param  array $reserves суммы, зарезервированные на фин. цели
 */

    $data = array();

    $keys = array(
        'id'          => 'id',
        'type_id'     => 'type',
        'currency_id' => 'currency',
        'description' => 'comment',
        'name'        => 'name',
        'balance'     => 'totalBalance',
    );

    foreach ($accounts as $account) {
        // мапим объект
        foreach ($keys as $column => $jsKey) {
            $data[$account['id']][$jsKey] = $account[$column];
        }

        // мапим начальную сумму на счете
        # Svel: TODO:
        #       это условие не будет нужно как только при создании
        #       и выборке счета будет всегда! находиться балансовая операция
        #       @see AccountTable::queryFindWithBalanceAndBalanceOperation
        if (isset($account['Operations']['0'])) {
            $data[$account['id']]['initPayment'] = $account['Operations']['0']['amount'];
        }

        // мапим зарезервированные на фин. цели средства
        $accountReserve = 0;
        foreach ($reserves as $reserve) {
            if ($reserve['account_id'] == $account['id']) {
                $accountReserve = $reserve['reserve'];
                break;
            }
        }

        $data[$account['id']]['reserve'] = $accountReserve;
    }
?>

res.accounts = <?php echo json_encode($data) ?>;
