<?php

/**
 * Table: Category
 */
class CategoryTable extends Doctrine_Table
{
    /**
     * Выбрать категории пользователя и посчитать частоту использования
     *
     * @param  User    $user
     * @param  string  $alias
     * @return Doctrine_Query
     */
    public function queryFindWithUseCount(User $user, $alias = 'c')
    {
        $query = $this->createQuery($alias)
            ->select("{$alias}.*, COUNT(o.id) as count")
            ->leftJoin("{$alias}.Operations o ON {$alias}.id = o.cat_id AND o.user_id = {$alias}.user_id")
            ->andWhere("{$alias}.user_id = ?", $user->getId())
            ->orderBy("{$alias}.parent_id, {$alias}.name")
            ->groupBy("{$alias}.id");

        return $query;
    }

}
