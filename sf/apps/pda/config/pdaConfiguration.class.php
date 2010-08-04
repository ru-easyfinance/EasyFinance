<?php

class pdaConfiguration extends sfApplicationConfiguration
{
    /**
     * SetUp
     */
    public function configure()
    {
        $this->setWebDir($this->getRootDir().'/web.pda');
    }


    /**
     * Инициализировать плагины
     */
    protected function initPlugins()
    {
        $plugins = array(
            'sfDoctrinePlugin',
            'myAuthPlugin',
        );

        if ('test' == $this->getEnvironment()) {
            $plugins[] = 'sfPhpunitPlugin';
        }

        $this->enablePlugins($plugins);
    }

}
