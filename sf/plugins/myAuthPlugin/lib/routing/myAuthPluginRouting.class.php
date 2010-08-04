<?php
/**
 * Подключает роуты динамически
 */
class myAuthPluginRouting
{
    /**
     * Обработка события: добавить роуты для входа/выхода пользователя
     */
    static public function addRoutesForAuth(sfEvent $event)
    {
        $event->getSubject()->prependRoute('logout', new sfRoute('/logout', array(
            'module' => 'myAuth',
            'action' => 'logout',
        )));

        $event->getSubject()->prependRoute('login',  new sfRoute('/login', array(
            'module' => 'myAuth',
            'action' => 'login',
        )));
    }

}
