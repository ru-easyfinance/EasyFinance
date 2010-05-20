<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

/**
 * Currency
 *
 * @todo
 *  - исключение, если указан неверный код (? проверять по наличию курсов)
 */
class classes_Currency_CurrencyTest extends UnitTestCase
{
    /**
     * Объект Money
     */
    function testCreateMoney()
    {
        $rur100 = new efMoney(100, efMoney::RUR);
        $this->assertEquals(100, $rur100->getAmount(), 'Get amount');
        $this->assertEquals(efMoney::RUR, $rur100->getCode(), 'Get code');
    }


    /**
     * Курсы: Курс валюты к самой себе всегда будет 1
     */
    public function testSelfRateIsAlwaysOne()
    {
        $ex = new efCurrencyExchange;
        $this->assertEquals(1, $ex->getRate(efMoney::RUR, efMoney::RUR));
        $this->assertEquals(1, $ex->getRate(efMoney::USD, efMoney::USD));
    }


    /**
     * Установить курсы валют
     */
    public function testSetRate()
    {
        $ex = new efCurrencyExchange;
        $ex->setRate(efMoney::USD, 25, efMoney::RUR);
        $ex->setRate(efMoney::EUR, 40, efMoney::RUR);

        $this->assertEquals(25,   $ex->getRate(efMoney::USD, efMoney::RUR), "Get RUR for 1 USD");
        $this->assertEquals(1/25, $ex->getRate(efMoney::RUR, efMoney::USD), "Get USD for 1 RUR");

        $this->assertEquals(40,      $ex->getRate(efMoney::EUR, efMoney::RUR), "Get RUR for 1 EUR");
        $this->assertEquals(1*25/40, $ex->getRate(efMoney::USD, efMoney::EUR), "Get EUR for 1 USD");
    }


    /**
     * Невозможно установить курс с 0 рейтом
     */
    public function testFailToSetZeroRate()
    {
        $ex = new efCurrencyExchange;

        $this->setExpectedException('InvalidArgumentException', 'invalid rate');
        $ex->setRate(efMoney::USD, 0, efMoney::RUR);
    }


    /**
     * Исключение, если курс не найден
     */
    public function testExceptionIfRateNotFound()
    {
        $ex = new efCurrencyExchange;
        $this->setExpectedException('Exception', 'Rate not found');
        $ex->getRate(efMoney::USD);
    }


    /**
     * Конвертация валют
     */
    public function testConvert()
    {
        $ex = new efCurrencyExchange;
        $ex->setRate(efMoney::USD, 25, efMoney::RUR);
        $ex->setRate(efMoney::EUR, 40, efMoney::RUR);

        $rur100 = new efMoney(100, efMoney::RUR);
        $this->assertEquals($rur100, $ex->convert($rur100, $rur100->getCode()),
            "Self convert");

        $this->assertEquals(new efMoney(4, efMoney::USD), $ex->convert($rur100, efMoney::USD),
            "Convert 100RUR into USD with rate 1/25");

        $usd8 = new efMoney(8, efMoney::USD);
        $this->assertEquals(new efMoney(5, efMoney::EUR), $ex->convert($usd8, efMoney::EUR),
            "Convert 8USD into EUR with rate 25/40");
    }


    /**
     * Суммирование валют
     */
    public function testSumm()
    {
        $ex = new efCurrencyExchange;
        $ex->setRate(efMoney::USD, 25, efMoney::RUR);

        $rur100 = new efMoney(100, efMoney::RUR);
        $usd4   = new efMoney(4, efMoney::USD);
        $usd1   = new efMoney(1, efMoney::USD);

        $rur225 = $ex->summ(array($rur100, $usd4, $usd1), efMoney::RUR);
        $this->assertEquals(225, $rur225->getAmount(), 'Get amount');
        $this->assertEquals(efMoney::RUR, $rur225->getCode(), 'Get code');
    }

}
