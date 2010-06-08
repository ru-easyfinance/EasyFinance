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


    /**
     * Настройки Doctrine
     */
    public function configureDoctrine(Doctrine_Manager $manager)
    {
        parent::configureDoctrine($manager);

        // Отключить SoftDelete
        $manager->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, false);
    }

}
