<?php

/**
 * Запрос для выборки операций для синхронизации
 */
class mySyncOutOperationQuery extends mySyncOutQuery
{
    /**
     * Получить название модели
     *
     * @return string
     */
    public function getModelName()
    {
        return 'Operation';
    }


    /**
     * Хук для уточнения запроса
     *
     * Не отдаем неподтвержденные операции из календаря
     *
     * @param  myDatetimeRange $range
     * @param  int             $userId
     * @param  string          $alias
     * @return void
     */
    protected function _extendQuery(myDatetimeRange $range, $userId, $alias)
    {
        // TODO Убрать хак после рефакторинга операций
        $hackQuery = "
            SELECT o.id
            FROM operation o
                LEFT JOIN target_bill tb
                    ON o.transfer_account_id = tb.bill_id
                    AND o.transfer_amount = tb.money
            WHERE
                tb.money IS NULL
                AND o.comment <> 'Перевод на счёт финцели'
                AND o.user_id = $userId";

        $data = Doctrine_Manager::getInstance()
            ->getConnection('doctrine')
            ->getDbh()
            ->query($hackQuery)->fetchAll(PDO::FETCH_COLUMN);

       $data = implode(', ', $data);

        $this->getQuery()
            ->andWhere("{$alias}.chain_id < 1 OR {$alias}.accepted = 1")
            ->andWhere("{$alias}.id IN ($data)");
    }

}
