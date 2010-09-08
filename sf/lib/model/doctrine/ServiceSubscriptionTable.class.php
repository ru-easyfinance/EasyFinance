<?php

/**
 * Таблица: подписки на сервисы
 */
class ServiceSubscriptionTable extends Doctrine_Table
{
    /**
     * Получить запись по id пользователя и id услуги
     *
     * @param int $userId id пользователя
     * @param int $serviceId id услуги
     * @return ServiceSubscription
     */
    public function getUserServiceSubscription($userId, $serviceId)
    {
        return $this->findOneByUserIdAndServiceId($userId, $serviceId);
    }


    /**
     * Получить активную запись по id пользователи и id услуги
     * @param int $userId
     * @param int $serviceId
     * @return ServiceSubscription
     */
    public function getActiveUserServiceSubscription($userId, $serviceId)
    {
        $alias = 'ss';

        $q = $this->createQuery($alias)
            ->andWhere('user_id = ?', $userId)
            ->andWhere('service_id = ?', $serviceId)
            ->andWhere('subscribed_till > ?', date('Y-m-d'));

        return $q->fetchOne();
    }

}
