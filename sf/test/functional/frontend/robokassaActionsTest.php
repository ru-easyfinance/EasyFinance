<?php
include(dirname(__FILE__).'/../../bootstrap/all.php');

/**
 * Тест модуля биллинга Robokassa
 */
class robokassaActionsTest extends myFunctionalTestCase
{
    protected $app = 'frontend';

    protected $transaction;

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


    private function getTestTransaction( $user=null )
    {
        if ( is_null( $this->transaction ) )
        {
            $service = new Service();
            $service->price = 100;
            $service->save();

            $transaction = new BillingTransaction();
            $transaction->setServiceId($service->getId());
            $transaction->setPrice($service->getPrice());
            $transaction->setTerm(6);
            $transaction->setStatus(0);
            $transaction->setTotal(600);
            $transaction->setUserId($user->getId() );
            $transaction->save();

            $this->transaction = $transaction;
        }
        return $this->transaction;
    }
}