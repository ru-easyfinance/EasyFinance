<?php


class ServiceSubscriptionTable extends Doctrine_Table
{
    /**
     * Получить запись по id пользователя и id услуги
     *
     * @param int $userId id пользователя
     * @param int $serviceId id услуги
     * @return ServiceSubscription
     */
    public function getUserServiceSubscription( $userId, $serviceId )
    {
        return $this->createQuery()
            ->select('*')
            ->from('ServiceSubscription')
            ->where('user_id=? AND service_id=?')
            ->fetchOne( array( $userId, $serviceId ) );
    }


    /**
     * Получить активную запись по id пользователи и id услуги
     * @param int $userId
     * @param int $serviceId
     * @return ServiceSubscription
     */
    public function getActiveUserServiceSubscription( $userId, $serviceId )
    {
        return $this->createQuery()
            ->select('*')
            ->from('ServiceSubscription')
            ->where('user_id=? AND service_id=? AND subscribed_till > NOW()')
            ->fetchOne( array( $userId, $serviceId ) );
    }
}