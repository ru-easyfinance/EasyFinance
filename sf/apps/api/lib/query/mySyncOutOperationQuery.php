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
        $this->getQuery()
            //->andWhere("{$alias}.chain_id < 1 OR {$alias}.accepted = 1")
            // TODO Вернуть назад, когда айфоновцы допилят приложение
            ->andWhere("{$alias}.accepted = 1");
    }

}
