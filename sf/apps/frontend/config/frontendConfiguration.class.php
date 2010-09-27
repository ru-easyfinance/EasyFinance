<?php

class frontendConfiguration extends sfApplicationConfiguration
{
    protected static $myCurrencyExchange = null;

    /**
     * SetUp
     */
    public function configure()
    {
        $this->setWebDir($this->getRootDir().'/web.front');

        // Эвент на получение "из контекста" обменника валют
        $this->dispatcher->connect('app.myCurrencyExchange', array('frontendConfiguration', 'getMyCurrencyExchange'));
    }


    /**
     * Достать объект обменника валют
     */
    public static function getMyCurrencyExchange(sfEvent $event)
    {
        if (!self::$myCurrencyExchange) {
            $currencies = Doctrine::getTable('Currency')->createQuery()->execute(array(), Doctrine::HYDRATE_ARRAY);
            self::$myCurrencyExchange = new myCurrencyExchange();

            foreach ($currencies as $currency) {
                self::$myCurrencyExchange->setRate($currency['id'], $currency['rate'], myCurrencyExchange::BASE_CURRENCY);
            }
        }

        // set return value and stop chain
        $event->setReturnValue(self::$myCurrencyExchange);
        return true;
    }

}
