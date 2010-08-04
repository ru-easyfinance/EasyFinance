<?php
 /**
  * Конфигуратор для плагина
 */

class myAuthPluginConfiguration extends sfPluginConfiguration
{
    /**
     * Инициализация
     * @see sfPluginConfiguration
     *
     * @return boolean
     */
    public function initialize()
    {
        $registerRoutes = sfConfig::get('app_myAuth_registerRoutes', true);

        if ($registerRoutes && $this->isAuthModuleEnabled()) {
            $this->dispatcher->connect(
                'routing.load_configuration',
                array('myAuthPluginRouting', 'addRoutesForAuth')
            );
        }

        return true;
    }


    /**
     * Определить, подключен ли модуль в apps/<>/config/settings.yml
     *
     * @return boolean
     */
    public function isAuthModuleEnabled()
    {
        return in_array('myAuth', sfConfig::get('sf_enabled_modules', array()));
    }

}
