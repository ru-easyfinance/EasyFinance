<?php

/**
 * Таблица: уведомления об операциях
 */
class OperationNotificationTable extends Doctrine_Table
{
    /**
     * Выбрать активные не отправленные уведомления
     *
     * @return Doctrine_Query
     */
    # Svel: TODO  теста нет
    # Svel: FIXME join пользователей и операций, см. sendEmailAndSmsNotifyTask
    public function getUnsentNotifications($alias = 'n')
    {
        $q = $this->createQuery($alias)
            ->andWhere("{$alias}.schedule < ?", date('Y-m-d H:i:s'))
            ->andWhere("{$alias}.is_sent = 0")
            ->andWhere("{$alias}.is_done = 0");

        return $q;
    }
}
