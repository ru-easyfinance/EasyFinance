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

    private $_acceptedOnly;

    public function getAcceptedOnly() {
        return $this->_acceptedOnly;
    }

    public function setAcceptedOnly($acceptedOnly) {
        $this->_acceptedOnly = $acceptedOnly;
        return $this;
    }


    private $_periodStartDate;
    private $_periodEndDate;

    public function getPeriodStartDate() {
        return $this->_periodStartDate;
    }

    public function setPeriodStartDate(DateTime $startDate) {
        $this->_periodStartDate = $startDate;
        return $this;
    }

    public function getPeriodEndDate() {
        return $this->_periodEndDate;
    }

    public function setPeriodEndDate(DateTime $endDate) {
        $this->_periodEndDate = $endDate;
        return $this;
    }


    /**
     * Наполняет список операций из БД за период
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return void
     */
    public function fill()
    {
        $alias = 'op';
        $query = Doctrine::getTable('Operation')
            ->createQuery($alias)
            ->innerJoin("{$alias}.Account")
            ->leftJoin("{$alias}.TransferAccount")
            ->leftJoin("{$alias}.Category")
            ->andWhere("{$alias}.user_id = ? ", $this->_user->getId());

        if($this->_periodStartDate)
            $query = $query->andWhere("{$alias}.date >= ? ",
                                      $this->_periodStartDate->format('Y-m-d'));

        if ($this->_periodEndDate)
            $query = $query->andWhere("{$alias}.date <= ? ",
                                      $this->_periodEndDate->format('Y-m-d'));
        if($this->_acceptedOnly)
            $query = $query->andWhere("{$alias}.accepted = 1");

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