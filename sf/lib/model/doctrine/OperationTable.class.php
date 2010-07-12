<?php

/**
 * Таблица: Операция
 */
class OperationTable extends Doctrine_Table
{
    /**
     * Посчитать кол-во операций по счетам (пользователя) за месяц
     *
     * @param  sfUser $user
     * @param  int    $limit
     * @return array
     */
    public function getMonthCountByUser(User $user = null, $limit = 3)
    {
        $queryCountAccounts = $this->createQuery('o')
            ->select("o.account_id account_id, COUNT(*) count")
            ->where("
                (o.updated_at BETWEEN ADDDATE(NOW(), INTERVAL -1 MONTH) AND NOW())
                AND o.type != ?
            ", Operation::TYPE_BALANCE)
            ->groupBy("o.account_id");

        $queryCountTransfers = $this->createQuery('o')
            ->select("o.transfer_account_id account_id, COUNT(*) count")
            ->where("
                (o.updated_at BETWEEN ADDDATE(NOW(), INTERVAL -1 MONTH) AND NOW())
                AND o.type != ?
                AND o.transfer_account_id IS NOT NULL
                AND o.transfer_account_id != 0
            ", Operation::TYPE_BALANCE)
            ->groupBy("o.transfer_account_id");

        if ($user) {
            $queryCountAccounts->andWhere("o.user_id = ?", $user->getId());
            $queryCountTransfers->andWhere("o.user_id = ?", $user->getId());
        }

        $result = array();

        $accCount = $queryCountAccounts->execute(array(), "FetchPair");

        foreach($queryCountTransfers->execute(array(), "FetchPair") as $accId => $count) {
            if (array_key_exists($accId, $accCount)) {
                $accCount[$accId] += $count;
            } else {
                $accCount[$accId] = $count;
            }
        }

        $result = arsort($accCount);

        $result = array_slice($accCount, 0, $limit, true);

        return $accCount;
    }
}
