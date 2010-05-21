<?php


/**
 * Валюта: таблица
 */
class CurrencyTable extends Doctrine_Table
{
    /**
     * Список измененных объектов для синка
     *
     * @param  array $params
     * @return Doctrine_Query
     */
    public function queryFindModifiedForSync(array $params)
    {
        $q = $this->createQuery('c')
            ->andWhere('c.is_active = ?', 1);

        return $q;
    }
}
