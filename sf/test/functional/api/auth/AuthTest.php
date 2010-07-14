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
    public function testAuthentificated()
    {
        $expected = array(
            'user_name' => "Login",
            'login'     => "Login",
            'password'  => "ValidPassword",
        );

        $user = $this->helper->makeUser($expected);

        unset($expected['user_name']);
        $this->browser
            ->post($this->generateUrl("auth"), $expected)
            ->with("request")->begin()
                ->isParameter("module", "myAuth")
                ->isParameter("action", "login")
            ->end()
            ->with("response")->begin()
                ->isStatusCode(200)
                ->checkElement('response message', 'Authentificated')
            ->end()
            ->with("user")->isAuthenticated(true);
    }

}
