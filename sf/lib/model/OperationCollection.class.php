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

    /**
     * @var User
     */
    private $_user;

    public function __construct(User $user)
    {
        $this->_user = $user;
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
            ->innerJoin("{$alias}.Account")
            ->leftJoin("{$alias}.TransferAccount")
            ->leftJoin("{$alias}.Category")
            ->andWhere("{$alias}.user_id = ? ", $this->_user->getId())
            ->andWhere("{$alias}.date >= ? ", $startDate->format('Y-m-d'))
            ->andWhere("{$alias}.date <= ? ", $endDate->format('Y-m-d'));

        $this->_operations = $query->execute(array())->getData();
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