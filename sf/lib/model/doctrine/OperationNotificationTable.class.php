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
    public function queryFindUnprocessed($alias = 'n')
    {
        $q = $this->createQuery($alias)
            ->select("{$alias}.*, o.*, u.*")
                ->andWhere("{$alias}.schedule < ?", date('Y-m-d H:i:s'))
                ->andWhere("{$alias}.schedule > ?", date('Y-m-d H:i:s', time()-sfConfig::get('app_notification_ttl')))
                ->andWhere("{$alias}.is_done = 0")
            ->innerJoin("{$alias}.Operation o")
                ->andWhere("o.deleted_at IS NULL")
                ->andWhere("o.accepted = ?", Operation::STATUS_DRAFT)
            ->innerJoin("o.User u")
            ->innerJoin("u.ServiceSubscription ss")
            ->innerJoin("ss.Service s")
                ->andWhere('s.keyword=? AND ss.subscribed_till > ?', array('sms',  date('Y-m-d')))
            ->limit(100);

        return $q;
    }
}
