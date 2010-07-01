<?php

/**
 * Обменник валют
 */
class myCurrencyExchange
{
    /**
     * Массив со списком курсов валют
     *
     * @var array
     */
    private $_rates = array();


    /**
     * Базовая валюта
     */
    CONST BASE_CURRENCY = myMoney::RUR;


    /**
     * Добавить курс
     *
     * @param  int   $codeFrom
     * @param  float $rate
     * @param  int   $codeTo
     * @return void
     */
    public function setRate($codeFrom, $rate, $codeTo)
    {
        if ($codeFrom == $codeTo) {
            return;
        }

        if ($rate <= 0) {
            throw new InvalidArgumentException(__METHOD__.": invalid rate `{$rate}`");
        }

        $this->_rates[$this->_makeKey($codeFrom, $codeTo)] = (float)$rate;
        if ($rate != 0) {
            $this->_rates[$this->_makeKey($codeTo, $codeFrom)] = 1 / (float)$rate;
        } else {
            $this->_rates[$this->_makeKey($codeTo, $codeFrom)] = 1;
        }
    }


    /**
     * Получить курс
     *
     * @param  int $codeFrom
     * @param  int $codeTo
     * @return float
     */
    public function getRate($codeFrom, $codeTo = self::BASE_CURRENCY)
    {
        if ($codeFrom == $codeTo) {
            return 1;
        }

        if (!$this->_hasRate($codeFrom, $codeTo)) {
            if (!$this->_hasRate($codeFrom, self::BASE_CURRENCY)) {
                throw new Exception (__METHOD__.": Rate not found for currency {$codeFrom}-{$codeTo}");
            } else {
                $rateFrom = $this->_getRate($codeFrom);
                $rateTo = $this->_getRate($codeTo);
                $this->setRate($codeFrom, $rateFrom / $rateTo, $codeTo);
            }
        }

        return $this->_getRate($codeFrom, $codeTo);
    }


    /**
     * Конвертировать сумму в другую валюту
     *
     * @param  myMoney $money
     * @param  int     $codeTo
     * @return myMoney
     */
    public function convert(myMoney $money, $codeTo = self::BASE_CURRENCY)
    {
        $rate = $this->getRate($money->getCode(), $codeTo);
        return new myMoney($rate * $money->getAmount(), $codeTo);
    }


    /**
     * Получить сумму двух валют
     *
     * @param  myMoney $money1
     * @param  myMoney $money2
     * @param  int     $codeTo - Код итоговой валюты
     * @return myMoney
     */
    public function plus(myMoney $money1, myMoney $money2, $codeTo = self::BASE_CURRENCY)
    {
        $amount = $this->convert($money1, $codeTo)->getAmount() + $this->convert($money2, $codeTo)->getAmount();
        return new myMoney($amount, $codeTo);
    }


    /**
     * Подсчитывает сумму всех денег в массиве, и возвращает деньгу в указанной валюте
     *
     * @param  array $array
     * @param  int   $codeTo
     * @return myMoney
     */
    public function summ(array $array, $codeTo = self::BASE_CURRENCY)
    {
        $result = array_shift($array);
        foreach($array as $money) {
            $result = $this->plus($result, $money, $codeTo);
        }
        return $result;
    }


    /**
     * Проверить наличие курса по указанной валюте
     *
     * @param  int $codeFrom
     * @param  int $codeTo
     * @return bool
     */
    private function _hasRate($codeFrom, $codeTo)
    {
        return isset($this->_rates[$this->_makeKey($codeFrom, $codeTo)]);
    }


    /**
     * Возвращает курс к указанной валюте
     *
     * @param  int $codeFrom
     * @param  int $codeTo
     * @return float
     */
    private function _getRate($codeFrom, $codeTo = self::BASE_CURRENCY)
    {
        return $this->_rates[$this->_makeKey($codeFrom, $codeTo)];
    }


    /**
     * Создать ключ для хранения курса валют
     *
     * @param  int $codeFrom
     * @param  int $codeTo
     * @return string
     */
    private function _makeKey($codeFrom, $codeTo)
    {
        return $codeFrom.'|'.$codeTo;
    }

}
