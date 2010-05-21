<?php

/**
 * Сумма в указанной валюте
 */
class efMoney
{
    CONST RUR = 1;
    CONST USD = 2;
    CONST EUR = 3;
    CONST UAH = 4;
    CONST BYR = 6;
    CONST KZT = 9;

    /**
     * ID валюты
     * @var int
     */
    private $_codeId;

    /**
     * Сумма
     * @var float
     */
    private $_amount;


    /**
     * Конструктор
     *
     * @param float $amount
     * @param int   $codeId
     */
    public function __construct($amount, $codeId)
    {
        $this->_amount = (float)$amount;
        $this->_codeId = $codeId;
    }


    /**
     * Возвращает сумму
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->_amount;
    }


    /**
     * Возвращает ID валюты
     *
     * @return int
     */
    public function getCode()
    {
        return $this->_codeId;
    }

}
