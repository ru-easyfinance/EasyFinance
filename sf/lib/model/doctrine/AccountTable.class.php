<?php

/**
 * Таблица: счета
 */
class AccountTable extends Doctrine_Table
{
    /**
     * Запрос для выборки счета, который привязан к AMT
     *
     * @param  int $userId
     * @return Doctrine_Query
     */
    public function queryFindLinkedWithAmt($userId, $alias = 'a')
    {
        $q = $this->createQuery($alias)
            ->select("{$alias}.*")
            ->innerJoin("{$alias}.Properties p")
            ->andWhere("{$alias}.user_id = ?", (int) $userId)
            ->andWhere("{$alias}.type_id = ?", Account::TYPE_DEBIT_CARD)
            ->andWhere('p.field_id = ?', AccountProperty::COLUMN_BINDING)
            ->andWhere('p.field_value = ?', Operation::SOURCE_AMT)
            ->distinct(true)
            ->limit(1);

        return $q;
    }


    /**
     * Все счета пользователя с балансом и начальным балансом
     *
     * @param  int $userId
     * @return Doctrine_Query
     */
    public function queryFindWithBalanceAndInitPayment($userId, $alias = 'a')
    {
        $q = $this->createQuery($alias)
            ->select("{$alias}.name, {$alias}.type_id type,
                {$alias}.description comment, {$alias}.currency_id currency,
                {$alias}.id, {$alias}.id account_id")
            ->addSelect("o.money initPayment")
            ->addSelect("SUM(op2.money) totalBalance")
            ->andWhere("{$alias}.user_id = ?", (int) $userId)
            ->orderBy("{$alias}.name")
            ->innerJoin("{$alias}.Operations o ON o.account_id = {$alias}.account_id
                AND o.cat_id IS NULL
                AND o.user_id = ?", (int) $userId)
            ->innerJoin("{$alias}.Operations op2 ON op2.account_id = {$alias}.account_id
                AND op2.accepted = 1
                AND op2.deleted_at IS NULL
                AND op2.user_id = ?", (int) $userId)
            ->groupBy("{$alias}.id");

        return $q;
    }

}
