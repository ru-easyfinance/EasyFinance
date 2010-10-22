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
    }

}
