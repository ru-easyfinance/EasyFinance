<?php
/**
 * Расширенный тестер для request
 *
 * @author Max <maxim.olenik@gmail.com>
 */

class myFunctionalTesterRequest extends sfTesterRequest
{
    /**
     * Check: проверить, что был вызван указанный модуль и контроллер
     *
     * @param string $module
     * @param string $module
     */
    public function checkModuleAction($module, $action)
    {
        $this->isParameter('module', $module);
        $this->isParameter('action', $action);

        return $this->getObjectToReturn();
    }

}
