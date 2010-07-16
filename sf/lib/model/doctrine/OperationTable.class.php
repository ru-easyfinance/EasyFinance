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


    /**
     * Выборка запланированных операций без учета времени
     *
     * @param  User     $user
     * @param  string   $alias
     */
    public function queryFindWithCalendarChainsCommon(User $user, $alias = 'o')
    {
        // Missed: tags, time, форматирование дат
        $q = $this->createQuery($alias)
            ->select("{$alias}.id
                    , {$alias}.chain_id
                    , {$alias}.type
                    , {$alias}.amount
                    , {$alias}.comment
                    , {$alias}.category_id
                    , {$alias}.account_id
                    , {$alias}.date
                    , {$alias}.accepted
                    , {$alias}.transfer_account_id
                    , {$alias}.source_id
                    ")
            ->addSelect("c.date_start")
            ->addSelect("c.date_end")
            ->addSelect("c.every_day")
            ->addSelect("c.repeat")
            ->addSelect("c.week_days")
            ->leftJoin("{$alias}.CalendarChain c")
            ->andWhere("{$alias}.user_id = ?", (int) $user->getId())
            ->andWhere("{$alias}.accepted=".Operation::STATUS_DRAFT)
            ->andWhere("{$alias}.deleted_at IS NULL")
            ;

        return $q;
    }


    /**
     * Выборка просроченных запланированных операций
     *
     * @param  User     $user
     * @param  string   $alias
     */
    public function queryFindWithOverdueCalendarChains(User $user, $alias = 'o')
    {
        $q = $this->queryFindWithCalendarChainsCommon($user, $alias);
        $q->andWhere("{$alias}.date <= ?", date('Y-m-d', time());
        return $q;
    }


    /**
     * Выборка будущих запланированных операций (на неделю вперед)
     *
     * @param  User     $user
     * @param  string   $alias
     */
    public function queryFindWithFutureCalendarChains(User $user, $alias = 'o')
    {
        $q = $this->queryFindWithCalendarChainsCommon($user, $alias);
        $q->andWhere("{$alias}.date <= ?", date('Y-m-d', time());

        return $q;
/*
        # Svel: ой, что это?
                AND
                    o.`date` BETWEEN ADDDATE(CURRENT_DATE(), INTERVAL 1 DAY) AND ADDDATE(CURRENT_DATE(), INTERVAL 8 DAY)
*/
    }

}
