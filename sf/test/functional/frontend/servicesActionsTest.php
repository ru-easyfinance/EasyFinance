<?php
include(dirname(__FILE__).'/../../bootstrap/all.php');

/**
 * Тест модуля выбора услуг для оплаты
 */
class servicesActionsTest extends myFunctionalTestCase
{
    protected $app = 'frontend';

    protected $transaction;

    /**
     * Тест /services/index
     *
     */
    public function testIndex()
    {
    	$user = $this->helper->makeUser();
        $this->authenticateUser($user);
    	$user->save();

        $service = new Service();
        $service->price = 100;
        $service->name = "Уникальное имя услуги " . uniqid();
        $service->save();

        $this->browser->
        get( $this->generateUrl('services') )->
        with('response')->begin()->
            isStatusCode(200)->
            matches('/(' . $service->name . ')/i')->
        end();
    }
}