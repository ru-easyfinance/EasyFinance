<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * PDA: авторизация пользователя
 */
class pdaAuthTest extends myFunctionalTestCase
{
    protected $app = 'pda';

    /**
     * Получить необходимые и правильные поля пользователя
     */
    protected function getExpectedUserData()
    {
        return array(
            'user_name'  => "test",
            'user_login' => "test",
            'password'   => "test1",
        );
    }


    /**
     * Получить правильные данные формы
     */
    protected function getExpectedFormData($withCookie = false)
    {
        $uData = $this->getExpectedUserData();

        $login = $uData['user_login'];
        $password = $uData['password'];

        return array('auth' => array(
            'login'    => $login,
            'password' => $password,
            'remember' => (bool) $withCookie,
        ));
    }


    /**
     * Получить форму
     */
    public function testAuthForm()
    {
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
    {
        $user = $this->helper->makeUser($this->getExpectedUserData());

        $this->browser
            ->post($this->generateUrl("login"), $this->getExpectedFormData())
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
    {
        $user = $this->helper->makeUser($this->getExpectedUserData());

        $this->browser
            ->post($this->generateUrl("login"), $this->getExpectedFormData())
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
        $user = $this->helper->makeUser($this->getExpectedUserData());

        // Входим методом POST
        $this->browser
            ->post($this->generateUrl("login"), $this->getExpectedFormData(true))
            ->with("request")->begin()
                ->isParameter("module", "myAuth")
                ->isParameter("action", "login")
            ->end()
            ->with("response")->begin()
                ->isStatusCode(302)
            ->end()
            ->with("user")->isAuthenticated(true);

        # Svel: дерьмо, почистить при случае и выкинуть тесты авторизации в плагин
        $this->browser
            ->with('model')->check('User', array('user_login' => 'test'), 1, $foundUser)
            ->with('model')->check('myAuthRememberKey', array('user_id' => $foundUser->getFirst()->getId()), 1, $foundKey)
            ->followRedirect();

        // Проверяем наличие печенья и качество печки =)
        $this->assertEquals(1, $foundKey->count());
        $this->browser
            ->with('request')->begin()
                ->hasCookie('myAuthRememberMe', true)
                ->isCookie('myAuthRememberMe', $foundKey->getFirst()->getRememberKey())
            ->end();

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
