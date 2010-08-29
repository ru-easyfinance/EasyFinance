<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';

/**
 * Профиль пользователя: показать формы
 */
class frontend_profile_SaveTest extends myFunctionalTestCase
{
    protected $app = 'frontend';


    /**
     * Сохранить профиль пользователя
     */
    public function testProfileSave()
    {
        $user = $this->helper->makeUser();
        $this->authenticateUser($user);

        $this->browser
            ->post($this->generateUrl('profile_save', array('sf_format'  => 'json')), array(
                'login'     => 'Qwer',
                'nickname'  => 'Querty',
            ))
            ->with('request')->checkModuleAction('profile', 'save')
            ->with('response')->isStatusCode(200)
            ->with('form')->begin()
                ->isInstanceOf('UserProfileForm')
                ->hasErrors(false)
            ->end()
            ->with('response')->checkJsonContains('result', array('text' => 'Данные успешно сохранены'));
    }

}
