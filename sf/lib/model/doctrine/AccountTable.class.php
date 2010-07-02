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
        $q = $this->createQuery($alias)
            ->select("{$alias}.name, {$alias}.type_id,
                {$alias}.description, {$alias}.currency_id,
                {$alias}.id")
            ->addSelect("o.amount")
            ->addSelect("SUM(op2.amount) balance")
            ->andWhere("{$alias}.user_id = ?", (int) $user->getId())
            ->orderBy("{$alias}.name")
            ->leftJoin("{$alias}.Operations o ON o.account_id = {$alias}.account_id
                AND o.category_id IS NULL
                AND o.date = '0000-00-00'
                AND o.accepted = ?
                AND o.type = ?", array(Operation::STATUS_ACCEPTED, Operation::TYPE_BALANCE))
            ->leftJoin("{$alias}.Operations op2 ON op2.account_id = {$alias}.account_id
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
