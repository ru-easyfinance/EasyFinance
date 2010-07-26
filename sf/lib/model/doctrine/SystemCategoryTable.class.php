<?php

/**
 * Таблица: Системные Категории
 */
class SystemCategoryTable extends Doctrine_Table
{
    /**
     * Выбрать категории с сортировкой по названию
     * TODO тест простой добавить
     *
     * @param  string  $alias
     * @return Doctrine_Query
     */
    public function queryFindWithOrderByName($alias = 'c')
    {
        $query = $this->createQuery($alias)
            ->orderBy("{$alias}.name");

        return $query;
    }

}
