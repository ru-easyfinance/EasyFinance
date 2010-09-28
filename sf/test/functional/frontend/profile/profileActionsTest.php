<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';

/**
 * Профиль пользователя: показать формы
 */
class frontend_profile_profileActionsTest extends myFunctionalTestCase
{
    protected $app = 'frontend';


    /**
     * Открыть страницу профиля
     */
    public function testProfileForm()
    {
        $this->authenticateUser();

        $this->browser
            ->getAndCheck('profile', 'index', $this->generateUrl('profile_form'), 200);

        $this->browser
            ->with('response')->begin()
                ->checkElement("#profile .formRegister", 1)
                ->checkElement("#reminders #remindersOptions", 1)
            ->end();
    }


    /**
     * Сохранить профиль пользователя
     */
    public function testProfileSave()
    {
        $this->authenticateUser();

        $this->browser
            ->post($this->generateUrl('profile_save', array('sf_format'  => 'json')), array(
                'login'     => 'Qwer',
                'nickname'  => 'Querty',
                'guide'     => 0,
            ))
            ->with('request')->checkModuleAction('profile', 'save')
            ->with('response')->isStatusCode(200)
            ->with('form')->begin()
                ->isInstanceOf('UserProfileForm')
                ->hasErrors(false)
            ->end()
            ->with('response')->begin()
                ->checkJsonContains('result', array('text' => 'Данные успешно сохранены'))
                ->setsCookie('guide', false)
            ->end();
    }


    /**
     * Сохранить профиль, установив куку на "гайд"
     */
    public function testProfileSaveWithGuide()
    {
        $this->authenticateUser();

        $this->browser
            ->post($this->generateUrl('profile_save', array('sf_format'  => 'json')), array(
                'login'     => 'Qwer',
                'nickname'  => 'Querty',
                'guide'     => 1,
            ))
            ->with('request')->checkModuleAction('profile', 'save')
            ->with('response')->isStatusCode(200)
            ->with('form')->begin()
                ->isInstanceOf('UserProfileForm')
                ->hasErrors(false)
            ->end()
            ->with('response')->begin()
                ->checkJsonContains('result', array('text' => 'Данные успешно сохранены'))
                ->setsCookie('guide', 'uyjsdhf')
            ->end();
    }


    /**
     * Сохранить состояние куки на гайд
     */
    public function testGuideCookie()
    {
        $this->authenticateUser();

        // убрать куку
        $this->browser
            ->post($this->generateUrl('profile_guide', array('sf_format'  => 'json')), array('state' => '0'))
            ->with('request')->checkModuleAction('profile', 'guide')
            ->with('response')->isStatusCode(200)
            ->with('response')->setsCookie('guide', false);

        // поставить куку
        $this->browser
            ->post($this->generateUrl('profile_guide', array('sf_format'  => 'json')), array('state' => '1'))
            ->with('request')->checkModuleAction('profile', 'guide')
            ->with('response')->isStatusCode(200)
            ->with('response')->setsCookie('guide', 'uyjsdhf');
    }

}
