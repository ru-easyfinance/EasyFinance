<?php

/**
 * Базовая таблица для объектов, которые поддерживают синхронизацию
 */
abstract class myBaseSyncTable extends Doctrine_Table
{
    /**
     * Получить список объектов подлежащих синхронизации
     *
     * @param  myDatetimeRange $range  - Интервал дат
     * @param  int             $userId - ID пользователя
     * @return Doctrine_Query
     */
    abstract public function queryFindModifiedForSync(myDatetimeRange $range, $userId);


    /**
     * Базовый запрос для синхронизации
     *
     * @param  myDatetimeRange $range  - Интервал дат
     * @param  int             $userId - ID пользователя
     * @param  string          $alias
     * @return Doctrine_Query
     */
    public function createBaseSyncQuery(myDatetimeRange $range, $userId, $alias = 'a')
    {
        $dateStart = $range->getStart()->format(DATE_ISO8601);
        $dateEnd   = $range->getEnd()->format(DATE_ISO8601);

        $q = $this->createQuery($alias)
            ->andWhere("{$alias}.updated_at BETWEEN CAST(? AS DATETIME) AND CAST(? AS DATETIME)", array(
                $dateStart, $dateEnd
            ));

        if ($this->hasColumn('user_id')) {
            $q->andWhere("{$alias}.user_id = ?", (int)$userId);
        }

        return $q;
    }

}
