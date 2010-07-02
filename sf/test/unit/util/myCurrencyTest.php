<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';

/**
 * Currency
 *
 * @todo
 *  - исключение, если указан неверный код (? проверять по наличию курсов)
 */
class util_myCurrencyTest extends myUnitTestCase
{
    /**
     * Объект Money
     */
    function testCreateMoney()
    {
        $rur100 = new myMoney(100, myMoney::RUR);
        $this->assertEquals(100, $rur100->getAmount(), 'Get amount');
        $this->assertEquals(myMoney::RUR, $rur100->getCode(), 'Get code');
    }


    /**
     * Курсы: Курс валюты к самой себе всегда будет 1
     */
    public function testSelfRateIsAlwaysOne()
    {
        $ex = new myCurrencyExchange;
        $this->assertEquals(1, $ex->getRate(myMoney::RUR, myMoney::RUR));
        $this->assertEquals(1, $ex->getRate(myMoney::USD, myMoney::USD));
    }


    /**
     * Установить курсы валют
     */
    public function testSetRate()
    {
        $ex = new myCurrencyExchange;
        $ex->setRate(myMoney::USD, 25, myMoney::RUR);
        $ex->setRate(myMoney::EUR, 40, myMoney::RUR);

        $this->assertEquals(25,   $ex->getRate(myMoney::USD, myMoney::RUR), "Get RUR for 1 USD");
        $this->assertEquals(1/25, $ex->getRate(myMoney::RUR, myMoney::USD), "Get USD for 1 RUR");

        $this->assertEquals(40,      $ex->getRate(myMoney::EUR, myMoney::RUR), "Get RUR for 1 EUR");
        $this->assertEquals(1*25/40, $ex->getRate(myMoney::USD, myMoney::EUR), "Get EUR for 1 USD");
    }


    /**
     * Невозможно установить курс с 0 рейтом
     */
    public function testFailToSetZeroRate()
    {
        $ex = new myCurrencyExchange;

        $this->setExpectedException('InvalidArgumentException', 'invalid rate');
        $ex->setRate(myMoney::USD, 0, myMoney::RUR);
    }


    /**
     * Исключение, если курс не найден
     */
    public function testExceptionIfRateNotFound()
    {
        $ex = new myCurrencyExchange;
        $this->setExpectedException('Exception', 'Rate not found');
        $ex->getRate(myMoney::USD);
    }


    /**
     * Конвертация валют
     */
    public function testConvert()
    {
        $ex = new myCurrencyExchange;
        $ex->setRate(myMoney::USD, 25, myMoney::RUR);
        $ex->setRate(myMoney::EUR, 40, myMoney::RUR);

        $rur100 = new myMoney(100, myMoney::RUR);
        $this->assertEquals($rur100, $ex->convert($rur100, $rur100->getCode()),
            "Self convert");

        $this->assertEquals(new myMoney(4, myMoney::USD), $ex->convert($rur100, myMoney::USD),
            "Convert 100RUR into USD with rate 1/25");

        $usd8 = new myMoney(8, myMoney::USD);
        $this->assertEquals(new myMoney(5, myMoney::EUR), $ex->convert($usd8, myMoney::EUR),
            "Convert 8USD into EUR with rate 25/40");
    }


    /**
     * Суммирование валют
     */
    public function testSumm()
    {
        $ex = new myCurrencyExchange;
        $ex->setRate(myMoney::USD, 25, myMoney::RUR);

        $rur100 = new myMoney(100, myMoney::RUR);
        $usd4   = new myMoney(4, myMoney::USD);
        $usd1   = new myMoney(1, myMoney::USD);

        $rur225 = $ex->summ(array($rur100, $usd4, $usd1), myMoney::RUR);
        $this->assertEquals(225, $rur225->getAmount(), 'Get amount');
        $this->assertEquals(myMoney::RUR, $rur225->getCode(), 'Get code');
    }

}
