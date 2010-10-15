<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * Синхронизация: авторизация пользователя
 */
class api_sync_AuthTest extends myFunctionalTestCase
{
    protected $app = 'api';


    /**
     * Ошибка: пользователь не авторизован
     */
    public function testAuth401()
    {
        $user = $this->helper->makeUser(array(), false);

        $this->browser
            ->getAndCheck('sync', 'syncOut', $this->generateUrl('sync_get_modified', array(
                'model' => 'currency',
            )), 401)
            ->with('response')->begin()
                ->checkElement('response error', 'Authentification required')
            ->end()
            ->with("user")->isAuthenticated(false);
    }


    /**
     * Авторизоваться
     */
    public function testAuthentificatedAndSubscribed()
    {
        $plan = array(
            array(
                'subscribed_till' => date('Y-m-d', strtotime('- 2 days')),
                'status_code' => 401,
                'element' => array(
                    'selector' => 'response error',
                    'contents' => 'Subscription required'
                ),
                'is_authenticated' => false,
            ),
            array(
                'subscribed_till' => date('Y-m-d', strtotime('+ 2 days')),
                'status_code' => 200,
                'element' => array(
                    'selector' => 'response message',
                    'contents' => 'Authentificated'
                ),
                'is_authenticated' => true,
            ),
        );

        $expected = array(
            'user_name'  => "Name",
            'user_login' => "Login",
            'password'   => "ValidPassword",
        );

        $user = $this->helper->makeUser($expected);

        $service = new Service();
        $service->id = Service::SERVICE_IPHONE;
        $service->price = 100;
        $service->name = "Уникальное имя услуги " . uniqid();
        $service->save();

        foreach ($plan as $case) {
            if (!isset($subscription)) {
                $subscription = new ServiceSubscription();
                $subscription->service_id = Service::SERVICE_IPHONE;
                $subscription->user_id = $user->getId();
            }

            $subscription->subscribed_till = $case['subscribed_till'];
            $subscription->save();

            $requestParams = array(
                'login'    => $expected['user_login'],
                'password' => $expected['password'],
            );

            $this->browser
                ->post($this->generateUrl("auth"), $requestParams)
                ->with("request")->begin()
                    ->isParameter("module", "myAuth")
                    ->isParameter("action", "login")
                ->end()
                ->with("response")->begin()
                    ->isStatusCode($case['status_code'])
                    ->checkElement(
                        $case['element']['selector'],
                        $case['element']['contents']
                    )
                ->end()
                ->with("user")->isAuthenticated($case['is_authenticated']);
        }
    }


    /**
     * Попытка авторизоваться без подписки на услугу
     */
    public function testSubscriptionRequired()
    {
        $expected = array(
            'user_name'  => "Name",
            'user_login' => "Login",
            'password'   => "ValidPassword",
        );

        $user = $this->helper->makeUser($expected);

        $requestParams = array(
            'login'    => $expected['user_login'],
            'password' => $expected['password'],
        );

        $this->browser
            ->post($this->generateUrl("auth"), $requestParams)
            ->with("request")->begin()
                ->isParameter("module", "myAuth")
                ->isParameter("action", "login")
            ->end()
            ->with("response")->begin()
                ->isStatusCode(401)
                ->checkElement('response error', 'Subscription required')
            ->end()
            ->with("user")->isAuthenticated(false);
    }

}
