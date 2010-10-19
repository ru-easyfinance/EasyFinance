<?php

/**
 * Таблица: подписки на сервисы
 */
class ServiceSubscriptionTable extends Doctrine_Table
{
    /**
     * Получить запись по id пользователя и id услуги
     *
     * @param User $user пользователь
     * @param string $serviceKeyword
     * @return ServiceSubscription
     */
    public function getUserServiceSubscription($user, $serviceKeyword)
    {
        $alias = 'ss';

        $q = $this
            ->queryUserServiceSubscription($user, $serviceKeyword, $alias);

        return $q->fetchOne();
    }


    /**
     * Получить активную запись по id пользователи и keyword услуги
     * @param User $user
     * @param string $serviceKeyword
     * @return ServiceSubscription
     */
    public function getActiveUserServiceSubscription($user, $serviceKeyword)
    {
        $alias = 'ss';

        $q = $this
            ->queryUserServiceSubscription($user, $serviceKeyword, $alias)
            ->andWhere("{$alias}.subscribed_till > ?", date('Y-m-d'));

        return $q->fetchOne();
    }


    /**
     * Получить запись по id пользователи и keyword услуги
     * @param User $user
     * @param string $serviceKeyword
     * @param string $alias
     * @return ServiceSubscription
     */
    public function queryUserServiceSubscription(
        $user, $serviceKeyword, $alias = 'ss'
    )
    {
        $q = $this->createQuery($alias)
            ->innerJoin("{$alias}.Service s")
            ->andWhere("{$alias}.user_id = ?", $user->getId())
            ->andWhere("s.keyword = ?", $serviceKeyword);

        return $q;
    }
}
