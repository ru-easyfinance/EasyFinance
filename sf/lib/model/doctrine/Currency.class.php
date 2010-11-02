<?php

/**
 * Валюта
 */
class Currency extends BaseCurrency
{
    /**
     * Конвертирует сумму из указанной валюты в свою валюту
     * @param $amount
     * @param $currencyFrom
     */
    public function convert($amount, Currency $currencyFrom)
    {
        return ($amount * $this->getRate()) / $currencyFrom->getRate();
    }
}
