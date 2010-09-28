<?php
include(dirname(__FILE__).'/../../bootstrap/all.php');

/**
 * Тест модуля биллинга Robokassa
 */
class robokassaActionsTest extends myFunctionalTestCase
{
    protected $app = 'frontend';

    /**
     * Последняя транзакция
     * @var BillingTransaction
     */
    protected $_transaction;

    /**
     * @var BillingTransaction
     */
    protected $_service;

    /**
     * Тест /robokassa/init
     *
     */
    public function testInit()
    {
        $user = $this->helper->makeUser();
        //$user->setUserName('Admin');
        $this->authenticateUser($user);
        $user->save();

        $service = new Service();
        $service->price = 100;
        $service->save();

        // Верный запрос
        // Ожидаем редирект
        $this->browser->
        post($this->generateUrl('robokassa', array('action'  => 'init')),
        array (
            'service' => $service->getId(),
            'term'    => 6,
        ))->
        with('request')->begin()->
            isParameter('service', $service->getId())->
            isParameter('term', 6)->
        end()->
        with('response')->begin()->
            isStatusCode(200)->
            matches('/(robokassa)/i')->
        end();

        // Неверный запрос с несуществующим id сервиса
        // Ожидаем переброс на 404
        $this->browser->
        post('robokassa/init/',
        array (
            'service' => time(),
            'term' => 6,
        ))->
        with('response')->begin()->
            isStatusCode(404)->
        end();
    }


    /**
     * Тест /robokassa/result
     *
     */
    public function testResult()
    {
        $user = $this->helper->makeUser();
        $this->authenticateUser($user);
        $user->save();

        $billingSettings = sfConfig::get('app_billing_robokassa');
        $transactionId = $this->getTestTransaction( $user )->getId();

        // Ожидаем ответ ОК + номер транзакции
        $this->browser->
        post($this->generateUrl('robokassa', array('action'  => 'result')),
        array (
            'InvId'  => $transactionId,
            'OutSum' => $this->getTestTransaction()->getTotal(),
            'SignatureValue'   => md5( $this->getTestTransaction()->getTotal() . ':' . $transactionId . ':' . $billingSettings['pass2'] . ':shpa=' . $this->getTestTransaction()->getTerm() ),
            'shpa'   => $this->getTestTransaction()->getTerm(),
        ))->
        with('response')->begin()->
            isStatusCode(200)->
            matches("/(OK{$transactionId})/i")->
        end();
    }


    /**
     * Тест /robokassa/success
     *
     */
    public function testSuccess()
    {
        $user = $this->helper->makeUser();
        $this->authenticateUser($user);
        $user->save();

        $billingSettings = sfConfig::get('app_billing_robokassa');
        $transactionId = $this->getTestTransaction( $user )->getId();

        $this->browser->
        post($this->generateUrl('robokassa', array('action'  => 'success')),
        array (
            'InvId'  => $transactionId,
            'OutSum' => ($this->getTestTransaction()->getPrice() * $this->getTestTransaction()->getTerm() ),
            'SignatureValue'   => md5( $this->getTestTransaction()->getTotal() . ':' . $transactionId . ':' . $billingSettings['pass1'] . ':shpa=' . $this->getTestTransaction()->getTerm() ),
            'shpa'   => $this->getTestTransaction()->getTerm(),
        ))->
        with('response')->
        begin()->
            isStatusCode(200)->
            matches('/(Оплата произведена успешно)/')->
        end();

        $transaction =  Doctrine::getTable('BillingTransaction')->find( $transactionId );

        // Транзакция должна сменить статус на 1 = оплачено
        $this->assertEquals($transaction->getStatus(), 1);
    }


    /**
     * Тест /robokassa/fail
     *
     */
    public function testFail()
    {
        $user = $this->helper->makeUser();
        $this->authenticateUser($user);
        $user->save();

        $billingSettings = sfConfig::get('app_billing_robokassa');
        $transactionId = $this->getTestTransaction( $user )->getId();

        $this->browser->
        post($this->generateUrl('robokassa', array('action'  => 'fail')),
        array (
            'InvId'  => $transactionId,
            'OutSum' => ($this->getTestTransaction( $user )->getPrice() * $this->getTestTransaction( $user )->getTerm() ),
            'SignatureValue'   => md5( $this->getTestTransaction( $user )->getTotal() . ':' . $transactionId . ':' . $billingSettings['pass1'] . ':shpa=' . $this->getTestTransaction( $user )->getTerm() ),
            'shpa'   => $this->getTestTransaction( $user )->getTerm(),
        ))->
        with('response')->
        begin()->
            isStatusCode(200)->
            matches('/(Ошибка)/')->
        end();

        $transaction =  Doctrine::getTable('BillingTransaction')->find( $transactionId );

        // Транзакция должна сменить статус на 2 = ошибка
        $this->assertEquals($transaction->getStatus(), 2);
    }

    /**
     * Тест двукратной оплаты одной и той же улуги
     */
    public function testDoublePay()
    {
        $user = $this->helper->makeUser();
        $this->authenticateUser($user);
        $user->save();

        $billingSettings = sfConfig::get('app_billing_robokassa');

        $this->sendSuccessRequest(
            $this->getTestTransaction($user),
            $billingSettings
        );

        $this->sendSuccessRequest(
            $transaction = $this->getTestTransaction($user, true),
            $billingSettings
        );

        $subscriptions = Doctrine::getTable('ServiceSubscription')
            ->findByUserIdAndServiceId(
                $transaction->getUserId(),
                $transaction->getServiceId()
            );

        $this->assertEquals(1, $subscriptions->count());
        $this->assertNotNull($subscriptions->getFirst()->getCreatedAt());
    }

    /**
     * Отправляет запрос на success url
     * @param BillingTransaction $transaction
     * @param array $billingSettings
     */
    private function sendSuccessRequest($transaction, $billingSettings)
    {
        $url = $this->generateUrl('robokassa', array('action'  => 'success'));
        $params = array(
            'InvId' => $transaction->getId(),
            'OutSum' => $transaction->getPrice() * $transaction->getTerm(),
            'SignatureValue' => md5(
                implode(
                    ':',
                    array(
                        $transaction->getTotal(),
                        $transaction->getId(),
                        $billingSettings['pass1'],
                        'shpa=' . $transaction->getTerm()
                   )
                )
            ),
            'shpa' => $transaction->getTerm(),
        );

        $this->browser->post($url, $params)
            ->with('response')->begin()
            ->isStatusCode(200)
            ->matches('/(Оплата произведена успешно)/')
            ->end();
    }

    /**
     * @return Service
     */
    private function getService()
    {
        if (is_null($this->_service)) {
            $service = new Service();
            $service->price = 100;
            $service->save();
            $this->_service = $service;
        }

        return $this->_service;
    }

    /**
     * Генерирует транзакцию для пользователя и сервиса
     * @param object $user
     * @param bool $isNew создавать ли новую транзакцию, если есть старая
     * @return BillingTransaction
     */
    private function getTestTransaction($user = null, $isNew = false)
    {
        if ((is_null($this->_transaction) || $isNew) && !is_null($user)) {
            $service = $this->getService();
            $term = 6;

            $transaction = new BillingTransaction();
            $transaction->fromArray(
                array(
                    'service_id' => $service->getId(),
                    'price'      => $service->getPrice(),
                    'term'       => $term,
                    'status'     => 0,
                    'total'      => $term * $service->getPrice(),
                    'user_id'    => $user->getId()
                )
            );
            $transaction->save();

            $this->_transaction = $transaction;
        }

        return $this->_transaction;
    }
}