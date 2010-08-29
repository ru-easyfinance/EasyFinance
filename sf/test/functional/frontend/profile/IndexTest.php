<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';

/**
 * Профиль пользователя: показать формы
 */
class frontend_profile_IndexTest extends myFunctionalTestCase
{
    protected $app = 'frontend';


    /**
     * Открыть страницу профиля
     */
    public function testProfileForm()
    {
        $user = $this->helper->makeUser();
        $this->authenticateUser($user);

        $this->browser
            ->getAndCheck('profile', 'index', $this->generateUrl('profile_form'), 200);

        $this->browser
            ->with('response')->begin()
                ->checkElement("#profile .formRegister", 1)
                ->checkElement("#reminders #remindersOptions", 1)
            ->end();
    }

}
