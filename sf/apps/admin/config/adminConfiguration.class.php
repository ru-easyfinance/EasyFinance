<?php

class adminConfiguration extends sfApplicationConfiguration
{
    public function configure()
    {
        $this->setWebDir($this->getRootDir().'/web.admin');
    }


    /**
     * Инициализация плагинов
     */
    protected function initPlugins()
    {
        $this->enablePlugins(array(
            'sfDoctrinePlugin',
            'sfFormExtraPlugin'
        ));
    }
}
