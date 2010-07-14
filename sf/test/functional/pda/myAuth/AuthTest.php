<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * PDA: авторизация пользователя
 */
class pdaAuthTest extends myFunctionalTestCase
{
    protected $app = 'pda';

    /**
     * Получить форму
     */
    public function testAuthForm()
    {return true;
        $this->browser
            ->get($this->generateUrl("login"))
            ->with("request")->begin()
                ->isParameter("module", "myAuth")
                ->isParameter("action", "login")
            ->end()
            ->with("response")->begin()
                ->isStatusCode(200)
            ->end();
    }

    /**
     * Авторизоваться
     */
    public function testAuthentificated()
    {return true;
        $expected = array(
            'user_name' => "test",
            'login'     => "test",
            'password'  => "test1",
        );

        $user = $this->helper->makeUser($expected);

        unset($expected['user_name']);
        $this->browser
            ->post($this->generateUrl("login"), array('auth' => $expected))
            ->with("request")->begin()
                ->isParameter("module", "myAuth")
                ->isParameter("action", "login")
            ->end()
            ->with("response")->begin()
                ->isStatusCode(302)
            ->end()
            ->with("user")->isAuthenticated(true);
    }

    /**
     * Деавторизация
     */
    public function testLogout()
    {return true;
        $expected = array(
            'user_name' => "test",
            'login'     => "test",
            'password'  => "test1",
        );

        $user = $this->helper->makeUser($expected);

        unset($expected['user_name']);
        $this->browser
            ->post($this->generateUrl("login"), array('auth' => $expected))
            ->with("request")->begin()
                ->isParameter("module", "myAuth")
                ->isParameter("action", "login")
            ->end()
            ->with("response")->begin()
                ->isStatusCode(302)
            ->end()
            ->with("user")->isAuthenticated(true);

        $this->browser->
            get($this->generateUrl('logout'))
                ->with("response")->begin()
                ->isStatusCode(302)
            ->end()
            ->with("user")->isAuthenticated(false);
    }

    /**
     * Галочка запомнить меня
     */
    public function testRememberMe()
    {
        $expected = array(
            'user_name' => "test",
            'login'     => "test",
            'password'  => "test1",
        );

        $user = $this->helper->makeUser($expected);

        unset($expected['user_name']);
        $expected['remember'] = true;

        // Входим методом POST
        $this->browser
            ->post($this->generateUrl("login"), array('auth' => $expected))
            ->with("request")->begin()
                ->isParameter("module", "myAuth")
                ->isParameter("action", "login")
            ->end()
            ->with("response")->begin()
                ->isStatusCode(302)
            ->end()
            ->with("user")->isAuthenticated(true);

        // Проверяем наличие печенья myRemember
        $cookies = $this->browser->getResponse()->getCookies();
        $this->assertArrayHasKey('myRemember', $cookies);

        // Проверяем вход с печеньем
        $this->browser
            ->get($this->generateUrl("login"))
            ->with("request")->begin()
                ->isParameter("module", "myAuth")
                ->isParameter("action", "login")
            ->end()
            ->with("response")->begin()
                ->isStatusCode(302)
            ->end()
            ->with("user")->isAuthenticated(true);
    }
}
