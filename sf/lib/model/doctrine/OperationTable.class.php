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
                AND o.account_id IS NOT NULL
                AND o.account_id != 0
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
     * Посчитать фактический расход по категориям за месяц
     *
     * @param  sfUser $user
     * @param  string $date дата начала месяца
     * @param  string $rate курс пользовательской валюты
     * @return array
     */
    public function getFactByCategory(User $user, $date, $rate = 1)
    {
        $alias = 'foo';

        $query = $this->querySumByCategory($user, $rate, $alias)
            ->andWhere("{$alias}.accepted = 1")
            ->andWhere("{$alias}.date >= ?", $date)
            ->andWhere("{$alias}.date <= LAST_DAY('$date')");

        $data = $query->execute(array(), 'FetchPair');

        return $data;
    }


    /**
     * Расход по по категориям для операций из календаря
     *
     * @param  sfUser $user
     * @param  string $date дата начала месяца
     * @param  string $rate курс пользовательской валюты
     * @return array
     */
    public function getCalendarPlanByCategory(User $user, $date, $rate = 1)
    {
        $alias = 'foo'; # TODO: нормальные альясы

        $query = $this->querySumByCategory($user, $rate, $alias)
            ->andWhere("{$alias}.chain_id IS NOT NULL")
            ->andWhere("{$alias}.chain_id <> 0")
            ->andWhere("{$alias}.date >= '$date'")
            ->andWhere("{$alias}.date <= LAST_DAY('$date')");

        $data = $query->execute(array(), 'FetchPair');
        return $data;
    }


    /**
     * Расход по операциям из календаря
     *
     * @param  sfUser $user
     * @param  string $date дата начала месяца
     * @param  string $rate курс пользовательской валюты
     * @return array
     */
    public function getNotCalendarPlanByCategory(User $user, $date, $rate = 1)
    {
        $alias = 'foo';

        $query = $this->querySumByCategory($user, $rate, $alias)
            ->andWhere("{$alias}.chain_id IS NULL OR {$alias}.chain_id = 0")
            ->andWhere("{$alias}.accepted = 1")
            ->andWhere("{$alias}.date >= ?", $date)
            ->andWhere("{$alias}.date <= LAST_DAY('$date')");

        $data = $query->execute(array(), 'FetchPair');

        return $data;
    }


    /**
     * Посчитать фактический расход по категориям
     *
     * @param  sfUser $user
     * @param  string $alias
     * @param  string $rate курс пользовательской валюты
     * @return Doctrine_Query
     */
    public function querySumByCategory(User $user, $rate = 1, $alias = 'o')
    {
        $query = $this->createQuery("{$alias}")
            ->select("category_id, sum(amount*cu.rate/$rate) AS fact")
            ->innerJoin("{$alias}.Category c")
            ->innerJoin("{$alias}.Account.Currency cu")
            ->where("{$alias}.user_id = ?", $user->getId())
            ->groupBy("{$alias}.category_id");

        return $query;
    }


    /**
     * Выборка запланированных операций без учета времени
     *
     * @param  User     $user
     * @param  string   $alias
     */
    private function queryFindWithCalendarChainsCommon(User $user, $alias = 'o')
    {
        // Missed: tags, time, форматирование дат
        $q = $this->createQuery($alias)
            ->leftJoin("{$alias}.CalendarChain c")
            ->andWhere("{$alias}.user_id = ?", (int) $user->getId())
            ->andWhere("{$alias}.deleted_at IS NULL")
            ;

        return $q;
    }


    /**
     * Возращаем дату, отстоящую от сегодня на заданное кол-во дней, в формате Y-m-d
     *
     * @param  int  daysCount   Кол-во дней
     */
    private function addDays($daysCount) {
        return date('Y-m-d', time() + $daysCount * 24 * 60 * 60);
    }


    /**
     * Выборка операций, запланированных в календаре или уже принятых
     *
     * @param  User     $user
     * @param  DateTime $dateStart
     * @param  DateTime $dateEnd
     * @param  string   $alias
     */
    public function queryFindWithCalendarChainsForPeriod(User $user, DateTime $dateStart, DateTime $dateEnd, $alias = 'o')
    {
        $period = array($dateStart->format('Y-m-d'), $dateEnd->format('Y-m-d'));
        $q = $this->queryFindWithCalendarChainsCommon($user, $alias)
            ->andWhere("{$alias}.date between ? and ?", $period)
            ->andWhere("( {$alias}.accepted=" . Operation::STATUS_DRAFT . " or {$alias}.chain_id > 0 )")
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
        $q->andWhere("{$alias}.date <= ?", $this->addDays(0))
          ->andWhere("{$alias}.accepted = " . Operation::STATUS_DRAFT);

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

        $period = array(
            $this->addDays(sfConfig::get('app_calendarFuture_daysStart', 1)),
            $this->addDays(sfConfig::get('app_calendarFuture_daysEnd', 8)),
        );

        $q->andWhere("{$alias}.date between ? and ?", $period)
          ->andWhere("{$alias}.accepted = " . Operation::STATUS_DRAFT);

        return $q;
    }


    /**
     * Найти ID счета последней активной операции пользователя по источнику
     *
     * @param  int  $userId
     * @param  int  $sourceKey
     * @return int|null
     */
    public function findAccountIdByLastAcceptedOperationBySource($userId, $sourceKey)
    {
        $q = $this->createQuery("o")
            ->select("o.account_id")
            ->andWhere("o.accepted = " . Operation::STATUS_ACCEPTED)
            ->andWhere("o.updated_at = (SELECT MAX(op.updated_at) FROM Operation op WHERE op.user_id = :user AND op.source_id = :source AND op.account_id IS NOT NULL AND op.accepted = " . Operation::STATUS_ACCEPTED . ")")
            ->andWhere("o.user_id = :user")
            ->andWhere("o.source_id = :source", array(':user' => $userId, ':source' => $sourceKey,))
            ->andWhere("o.account_id IS NOT NULL")
            ->limit(1);

        $accountId = $q->execute(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);

        return ($accountId) ? $accountId : null;
    }


    /**
     * Обновить все неподтвержденные операции пользователя по источнику, присвоить ID счета
     *
     * @param  Operation $operation
     * @return int
     */
    public function updateAccountIdBySourceOperation(Operation $operation)
    {
        $q = $this->createQuery('o')
            ->update("Operation o")
            ->set("o.account_id", $operation->getAccountId())
            ->where("o.accepted = " . Operation::STATUS_DRAFT)
            ->andWhere("o.user_id = ?", $operation->getUserId())
            ->andWhere("o.source_id = ?", $operation->getSourceId())
            ->andWhere("o.account_id IS NULL");

        return $q->execute();
    }


    /**
     * Получить опытность пользователя в днях
     *
     * @param   User    $user
     * @return  integer
     */
    public function getExpirienceByUser(User $user)
    {
        $query = "
            SELECT (DATEDIFF(CURDATE(), IFNULL(tbl.mindate, CURDATE())) + 1) AS cnt
            FROM (
                SELECT MIN(op.date) AS mindate
                FROM operation op
                WHERE op.type != :operation_type
                    AND op.accepted = 1
                    AND op.deleted_at IS NULL
                    AND op.user_id = :user_id
            ) AS tbl
        ";

        $pdoConn = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
        $statement = $pdoConn->prepare($query);

        $statement->execute(array(
            'operation_type' => Operation::TYPE_BALANCE,
            'user_id'        => (int) $user->getId(),
        ));

        $result = (int) $statement->fetchColumn(0);

        $statement->closeCursor();

        return $result;
    }


    /**
     * Баланс пользователя по счетам
     */
    public function getBalance(User $user, $hydrate = Doctrine::HYDRATE_ARRAY)
    {
        return $this->createQuery()
            ->getBalanceQuery($user)
            ->execute(array(), $hydrate);
    }


    /**
     * Доход пользователя по счетам
     *
     * @param   User    $user
     * @param   integer $months
     * @param   mixed   $hydrationMode  @see Doctrine_Query_Abstract::execute
     * @return  array|mixed
     */
    public function getProfit(User $user, $months = null, $hydrate = Doctrine::HYDRATE_ARRAY)
    {
        return $this->createQuery()
            ->getProfitQuery($user, $months)
            ->execute(array(), $hydrate);
    }


    /**
     * Расход пользователя по счетам
     *
     * @param   User    $user
     * @param   integer $months
     * @param   mixed   $hydrationMode  @see Doctrine_Query_Abstract::execute
     * @return  array|mixed
     */
    public function getExpence(User $user, $months = null, $hydrate = Doctrine::HYDRATE_ARRAY)
    {
        return $this->createQuery()
            ->getExpenceQuery($user, $months)
            ->execute(array(), $hydrate);
    }


    /**
     * Выплаты пользователя по кредитам
     */
    public function getRepayLoanExpence(User $user, $months = null, $hydrate = Doctrine::HYDRATE_ARRAY)
    {
        return $this->createQuery()
            ->getRepayLoanQuery($user, $months)
            ->execute(array(), $hydrate);
    }


    /**
     * Проценты пользователя по кредитам и займам
     */
    public function getInterestOnLoanExpence(User $user, $months = null, $hydrate = Doctrine::HYDRATE_ARRAY)
    {
        return $this->createQuery()
            ->getInterestOnLoanQuery($user, $months)
            ->execute(array(), $hydrate);
    }

}
