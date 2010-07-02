<?php

/**
 * Таблица: операции перевода на фин.цель
 */
class TargetTransactionTable extends Doctrine_Table
{
    /**
     * Запрос для выборки зарезервированных средств
     *
     * @param  array  $accountIds массив ID счетов
     * @param  User   $user
     * @param  string $alias
     * @return Doctrine_Query
     */
    public function queryCountReserves(array $accountIds, User $user = null, $alias = 't')
    {
        $q = $this->createQuery($alias)
            ->select("{$alias}.account_id, SUM({$alias}.amount) reserve")
            ->innerJoin("{$alias}.Target tg")
            ->andWhere("tg.done = 0")
            ->andWhereIn("{$alias}.account_id", $accountIds)
            ->groupBy("{$alias}.account_id");

        if ($user) {
            $q->andWhere("{$alias}.user_id = ? AND tg.user_id = ?", array((int) $user->getId(), (int) $user->getId()));
        }

        return $q;
    }

}
