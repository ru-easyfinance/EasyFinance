<?php

/**
 * Запрос для выборки счетов для синхронизации
 */
class mySyncOutAccountQuery extends mySyncOutQuery
{
    /**
     * Получить название модели
     *
     * @return string
     */
    public function getModelName()
    {
        return 'Account';
    }
}
