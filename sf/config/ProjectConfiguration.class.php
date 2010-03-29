<?php

require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();


class ProjectConfiguration extends sfProjectConfiguration
{
    /**
     * SetUp
     */
    public function setup()
    {
        $this->initPlugins();

        // Escaper
        sfOutputEscaper::markClassesAsSafe(array(
            'DateTime',
        ));
    }


    /**
     * Инициализировать все плагины для CLI
     * Конкретные приложения перекрывают этот метод
     */
    protected function initPlugins()
    {
        $this->enablePlugins(array(
            'sfDoctrinePlugin',
        ));
    }


    /**
     * Настройки Doctrine
     */
    public function configureDoctrine(Doctrine_Manager $manager)
    {
        $manager->setAttribute(Doctrine_Core::ATTR_DEFAULT_TABLE_CHARSET, 'utf8');
        $manager->setAttribute(Doctrine_Core::ATTR_DEFAULT_TABLE_COLLATE, 'utf8_general_ci');
        $manager->setAttribute(Doctrine_Core::ATTR_DEFAULT_TABLE_TYPE,    'INNODB');
    }

}
