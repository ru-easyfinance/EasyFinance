<?php

/**
 * Таблица: счета
 */
class AccountTable extends Doctrine_Table
{
    public function queryFindModifiedForSync(array $params)
    {
        $q = $this->createQuery('a')
            ->andWhere('a.user_id = ?', $params['user_id']);

        return $q;
    }


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

}
