<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Таблица уведомлений
 */
class model_OperationNotificationTableTest extends myUnitTestCase
{
    private $table;


    /**
     * SetUp
     */
    protected function _start()
    {
        $this->table = Doctrine::getTable('OperationNotification');

        // Создать услугу с захардкоженным ID
        $service = new Service();
        $service->setId(1); // TODO: Убрать хардкод
        $service->save();
    }


    /**
     * Создать пользователя и подписать его на услугу
     * Вернуть новый счет с этим пользователем
     *
     * @return Account
     */
    private function _makeAccountWithSubscribedUser($daysShift = 1)
    {
        $user = $this->helper->makeUser();

        $date = new DateTime("{$daysShift}days");

        // Подписка
        $subscription = new ServiceSubscription();
        $subscription->setUser($user);
        $subscription->setServiceId(1); // TODO: Убрать хардкод
        $subscription->setSubscribedTill($date->format('Y-m-d 00:00:00'));
        $subscription->save();

        return $this->helper->makeAccount($user);
    }


    /**
     * Уведомления для таска: только истекшие и незавершенные
     */
    public function testTaskQueueOnlyUnprocessedAndExpired()
    {
        $op = $this->helper->makeOperation($this->_makeAccountWithSubscribedUser(),
            array('accepted' => Operation::STATUS_DRAFT));

        // OK
        $ntfn1 = $this->helper->makeOperationNotification($op);
        // No - в будущем
        $ntfn2 = $this->helper->makeOperationNotification($op, array('schedule' => date('Y-m-d H:i:s', time()+100)));
        // No - уже выполнена
        $ntfn3 = $this->helper->makeOperationNotification($op, array('is_done' => 1));
        // No - сильно просрочена
        $ntfn4 = $this->helper->makeOperationNotification($op, array('schedule' => date('Y-m-d H:i:s', time()-sfConfig::get('app_notification_ttl'))));

        $result = $this->table->queryFindUnprocessed()->execute();
        $this->assertEquals(1, $result->count(), 'Expected found 1 record');
        $this->assertModels($ntfn1, $result->getFirst());
    }


    /**
     * Уведомления для таска: операция не удалена и не подтверждена
     */
    public function testTaskQueueOperationIsNotAcceptedAndDeleted()
    {
        $ops = $this->helper->makeOperationCollection(3, $this->_makeAccountWithSubscribedUser(),
        array(
            // OK
            array('accepted' => Operation::STATUS_DRAFT),
            // No - Не подтверждена, но удалена
            array('accepted' => Operation::STATUS_DRAFT, 'deleted_at' => date('Y-m-d H:i:s')),
            // No - Подтверждена
            array('accepted' => Operation::STATUS_ACCEPTED),
        ));


        $ntfn1 = $this->helper->makeOperationNotification($ops[0]);
        $ntfn2 = $this->helper->makeOperationNotification($ops[1]);
        $ntfn3 = $this->helper->makeOperationNotification($ops[2]);

        $result = $this->table->queryFindUnprocessed()->execute();
        $this->assertEquals(1, $result->count(), 'Expected found 1 record');
        $this->assertModels($ntfn1, $result->getFirst());
    }


    /**
     * Уведомления для таска: пользователь должен оплатить
     */
    public function testTaskQueueUsetMustHaveActiveSubscription()
    {
        $accOk = $this->_makeAccountWithSubscribedUser(+1);
        $accNo = $this->_makeAccountWithSubscribedUser(-1);

        $opOk = $this->helper->makeOperation($accOk, array('accepted' => Operation::STATUS_DRAFT));
        $opNo = $this->helper->makeOperation($accNo, array('accepted' => Operation::STATUS_DRAFT));

        $ntfnOk = $this->helper->makeOperationNotification($opOk);
        $ntfnMo = $this->helper->makeOperationNotification($opNo);

        $result = $this->table->queryFindUnprocessed()->execute();
        $this->assertEquals(1, $result->count(), 'Expected found 1 record');
        $this->assertModels($ntfnOk, $result->getFirst());
    }

}
