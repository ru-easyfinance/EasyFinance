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

    #Max: тест?
     */
    public function queryFindWithBalanceAndInitPayment($userId, $alias = 'a')
    {
        #Max: давай здесь писать по человечески, а мапить колонки в контроллере или во вью
        #     зачем ты здесь прогибаешься под требования клиента
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
                AND o.date = '0000-00-00'")
            ->innerJoin("{$alias}.Operations op2 ON op2.account_id = {$alias}.account_id
                AND op2.accepted = 1")
            ->groupBy("{$alias}.id");

        return $q;
    }


    /**
     * Запрос для выборки зарезервированных средств
     *
     * @param  array $accountIds массив ID аккаунтов
     * @return Doctrine_Query

    #Max: тест?
     */
    public function queryCountReserves($accountIds)
    {
        $q = Doctrine_Query::create()
            ->select("t.account_id, SUM(t.money) reserve")
            ->from("TargetTransaction t INDEXBY t.account_id")
            ->innerJoin("t.Target tg")
            ->andWhere("tg.done = 0")
            ->andWhereIn("t.account_id", $accountIds)
            ->groupBy("t.account_id");

        return $q;
    }

}
