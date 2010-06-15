<?php

/**
 * Запрос для выборки категорий для синхронизации
 */
class mySyncOutCategoryQuery extends mySyncOutQuery
{
    /**
     * Получить название модели
     *
     * @return string
     */
    public function getModelName()
    {
        return 'Category';
    }
}
