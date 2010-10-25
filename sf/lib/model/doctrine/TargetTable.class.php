<?php

/**
 * Таблица: финансовая цель
 */
class TargetTable extends Doctrine_Table
{
    /**
     * Возвращает список категорий финцелей со статистикой
     */
    public function getTargetCategories()
    {
        $query = $this->createQuery('t')
        ->select(
            "t.category_id, COUNT(t.id) AS cnt, SUM(t.done) AS cl"
        )
        ->where("t.category_id BETWEEN 1 AND 8")
        ->groupBy("category_id")
        ->orderBy("cnt DESC");

        return $query->execute(array(), Doctrine::HYDRATE_ARRAY);
    }

    /**
     * Список финцелей пользователя
     * @param User пользователь
     * @return array Список финцелей
     */
    public function getUserTargets(User $user)
    {
        $query = "
            SELECT
                t.id, t.category_id as category,
                t.title,
                t.amount,
                DATE_FORMAT(t.date_begin,'%d.%m.%Y') as start,
                DATE_FORMAT(t.date_end,'%d.%m.%Y') as end,
                t.percent_done,
                t.forecast_done,
                t.visible,
                t.photo,
                t.url,
                t.comment,
                t.target_account_id AS account,
                t.amount_done,
                t.close,
                t.done,
                (SELECT b.money FROM target_bill b WHERE b.target_id = t.id ORDER BY b.dt_create ASC LIMIT 1) AS money
            FROM target t
            WHERE t.user_id = :user_id
            ORDER BY t.date_end ASC
            LIMIT 0, 20;
        ";

        $pdoConn = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
        $statement = $pdoConn->prepare($query);

        $statement->execute(array('user_id' => (int) $user->getId()));

        $result = $statement->fetchAll();

        $statement->closeCursor();

        return $result;
    }
}
