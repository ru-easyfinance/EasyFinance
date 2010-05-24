<?php


/**
 * Таблица: Валюта
 */
class CurrencyTable extends Doctrine_Table
{
    /**
     * Список измененных объектов для синка
     *
     * @param  array $params
     * @return Doctrine_Query
     */
    public function queryFindModifiedForSync(myDatetimeRange $range, $userId)
    {
        $dateStart = $range->getStart()->format(DATE_ISO8601);
        $dateEnd   = $range->getEnd()->format(DATE_ISO8601);

        $q = $this->createQuery('c')
            ->andWhere('c.updated_at BETWEEN CAST(? AS DATETIME) AND CAST(? AS DATETIME)', array(
                $dateStart, $dateEnd
            ))
            ->orWhere('c.created_at BETWEEN CAST(? AS DATETIME) AND CAST(? AS DATETIME)', array(
                $dateStart, $dateEnd
            ))
            ->andWhere('c.is_active = ?', 1);

        return $q;
    }
}
