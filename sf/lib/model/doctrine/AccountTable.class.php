<?php

/**
 * Таблица: счета
 */
class AccountTable extends Doctrine_Table
{
    /**
     * Запрос для выборки счета, который привязан к AMT
     *
     * @param  int    $userId
     * @param  string $alias
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
     * @param  User   $user
     * @param  string $alias
     * @return Doctrine_Query
     */
    public function queryFindWithBalanceAndBalanceOperation(User $user, $alias = 'a')
    {
        $userId = (int) $user->getId();

        $q = $this->createQuery($alias)
            ->select("{$alias}.name, {$alias}.type_id,
                {$alias}.description, {$alias}.currency_id,
                {$alias}.id, {$alias}.state")
            ->addSelect("o.amount")
            // TODO: мысли вслух - м.б. использовать Views или prepared statements ?
            ->addSelect("SUM(CASE
                WHEN
                    op2.type = " . Operation::TYPE_TRANSFER . "
                    AND op2.transfer_account_id = {$alias}.account_id
                THEN
                    op2.transfer_amount
                ELSE
                    op2.amount
                END) AS balance")
            ->andWhere("{$alias}.user_id = ?", $userId)
            ->orderBy("{$alias}.name")
            ->leftJoin("{$alias}.Operations o ON o.user_id={$userId} AND o.account_id = {$alias}.account_id
                AND o.type = ?", array(Operation::TYPE_BALANCE))
            ->leftJoin("{$alias}.Operations op2 ON op2.user_id={$userId} AND (
                    op2.account_id = {$alias}.account_id
                    OR op2.transfer_account_id = {$alias}.account_id
                )
                AND op2.accepted = ?", Operation::STATUS_ACCEPTED)
            ->groupBy("{$alias}.id");

        return $q;
    }


    /**
     * Выборка счёта пользователя, который привязан к источнику
     *
     * @param  int       $userId
     * @param  string    $source
     * @return null|int  id счета или null
     */
    public function findLinkedWithSource($userId, $source)
    {
        $id = $this->createQuery('a')
            ->select('a.*')
            ->innerJoin("a.Properties p")
            ->andWhere("a.user_id = ?", (int) $userId)
            ->andWhere('p.field_id = ?', AccountProperty::COLUMN_BINDING)
            ->andWhere('p.field_value = ?', $source)
            ->distinct(true)
            ->limit(1)
            ->select('a.id')
            ->execute(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
        return ($id) ? (int) $id : null;;
    }

}
