<?php


/**
 * Таблица: Валюта
 */
class CurrencyTable extends myBaseSyncTable
{
    /**
     * Получить список объектов подлежащих синхронизации
     *
     * @param  myDatetimeRange $range  - Интервал дат
     * @param  int             $userId - ID пользователя
     * @return Doctrine_Query
     */
    public function queryFindModifiedForSync(myDatetimeRange $range, $userId)
    {
        $q = $this->createBaseSyncQuery($range, $userId, 'c')
            ->andWhere('c.is_active = ?', 1);

        return $q;
    }

}
