<?php
/**
 *
 */
class OperationCollection {

    /**
     * Список операций
     * @var array
     */
    private $_operations;

    public function __construct()
    {
    }


    /**
     * Наполняет список операций из БД за период
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return void
     */
    public function fillForPeriod(DateTime $startDate, DateTime $endDate)
    {
        $alias = 'op';
        $query = Doctrine::getTable('Operation')
            ->createQuery($alias)
            ->andWhere("{$alias}.date >= ? ", $startDate)
            ->andWhere("{$alias}.date <= ? ", $endDate);

        $this->_operations = $query->execute(array());
    }

    /**
     * @return array массив операций
     * @throws myOperationCollectionFillRequiredException
     */
    public function getOperations()
    {
        if (!isset($this->_operations))
            throw new myOperationCollectionFillRequiredException(
                'Прежде чем вызывать getOperations заполните operations'
            );

        return $this->_operations;
    }

}

class myOperationCollectionFillRequiredException extends Exception {}