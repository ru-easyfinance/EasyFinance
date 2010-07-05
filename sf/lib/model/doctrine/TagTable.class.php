<?php

/**
 * Таблица: Теги
 */
class TagTable extends Doctrine_Table
{
    /**
     * Выбрать теги пользователя отсортированные по
     * весу, посчитать вес (кол-во повторов)
     *
     * @param  User $user
     * @return Doctrine_Query
     */
    public function queryFindUniqueWithCountByUser(User $user)
    {
        return $this->createQuery("tag")
            ->select("tag.name, COUNT(tag.name) count")
            ->where("tag.user_id = ?", $user->getId())
            ->groupBy("tag.name")
            ->orderBy("count DESC");
    }

}
