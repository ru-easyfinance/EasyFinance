<?php


class OperationNotificationTable extends Doctrine_Table
{

    public static function getInstance()
    {
        return Doctrine_Core::getTable('OperationNotification');
    }

    /**
     * Получить список активных неотправленных уведомлений
     *
     * @return Doctrine_Collection
     */
    public function getUnsentNotifications()
    {
        $dQL = "(date_time < NOW()) AND (is_sent = 0) AND ( is_done = 0 )";
        return $this->findByDql( $dQL, array() );
    }
}