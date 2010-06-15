<?php

/**
 * Запрос для выборки валют для синхронизации
 */
class mySyncOutCurrencyQuery extends mySyncOutQuery
{
    /**
     * Получить название модели
     *
     * @return string
     */
    public function getModelName()
    {
        return 'Currency';
    }


    /**
     * Хук для уточнения запроса
     *
     * @param  myDatetimeRange $range
     * @param  int             $userId
     * @param  string          $alias
     * @return void
     */
    protected function _extendQuery(myDatetimeRange $range, $userId, $alias)
    {
        $this->getQuery()->andWhere("{$alias}.is_active = ?", 1);
    }

}
