<?php

/**
 * BudgetCategory Table
 */
class BudgetCategoryTable extends Doctrine_Table
{
    /**
     * Рассчитать итоговую сумму расходной части бюджета на текущий месяц
     *
     * @param  int $userId
     * @return float
     */
    public function countTotalExpense($userId)
    {
        return (float) $this->createQuery('b')
            ->select('SUM(b.amount)')
            ->andWhere('b.user_id = ?', (int)$userId)
            ->andWhere('b.date_start = ?', date('Y-m-01'))
            ->andWhere('b.drain = ?', 1)
            ->execute(array(), Doctrine::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * Список запланированных расходов на месяц
     *
     * @param User $user
     * @param DateTime $start
     * @return array
     */
    public function getBudget(User $user, DateTime $start)
    {
        $alias  = 'b';

        $q = $this->createQuery($alias)
            ->innerJoin("{$alias}.Category c")
            ->andWhere("{$alias}.date_start = ?", $start->format('Y-m-d'))
            ->andWhere("{$alias}.user_id = ?", $user->getId());

        return $q->execute()->getData();
    }
}
