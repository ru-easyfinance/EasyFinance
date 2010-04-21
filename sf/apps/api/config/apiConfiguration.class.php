<?php

class apiConfiguration extends sfApplicationConfiguration
{
    /**
     * SetUp
     */
    public function configure()
    {
        $this->setWebDir($this->getRootDir().'/web.api');
    }


    /**
     * Init
     */
    public function initialize()
    {
    }


    /**
     * Инициализировать плагины
     */
    protected function initPlugins()
    {
        $plugins = array(
            'sfDoctrinePlugin',
        );

        if ('test' == $this->getEnvironment()) {
            $plugins[] = 'sfPhpunitPlugin';
        }

        $this->enablePlugins($plugins);
    }
}