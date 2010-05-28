<?php

/**
 * Таблица: Операция
 */
class OperationTable extends Doctrine_Table
{
    public function queryFindModifiedForSync(array $params)
    {
        $q = $this->createQuery('a')
            ->andWhere('a.user_id = ?', $params['user_id']);

        return $q;
    }

}
