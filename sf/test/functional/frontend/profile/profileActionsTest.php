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
            ))
            ->with('request')->checkModuleAction('profile', 'save')
            ->with('response')->isStatusCode(200)
            ->with('form')->begin()
                ->isInstanceOf('UserProfileForm')
                ->hasErrors(false)
            ->end()
            ->with('response')->begin()
                ->checkJsonContains('result', array('text' => 'Данные успешно сохранены'))
            ->end();
    }


    /**
     * Сохранить профиль пользователя Рамблёра
     */
    public function testRamblerProfileSave()
    {
        // change default server host: @see sfBrowserBase::246
        //
        // Init test browser
        $this->browser = new sfTestFunctional($browser = new sfPhpunitTestBrowser('https://rambler.ef.test/'), new sfPHPUnitLimeAdapter($this), $this->getFunctionalTesters());
        $this->initBrowser($browser);

        $user = $this->authenticateUser();

        $this->browser
            ->post($this->generateUrl('profile_save', array('sf_format'  => 'json')), $input = array(
                'mailIntegration' => 'unique.integra',
                'nickname'        => 'Билл Гей-тсс',
            ))
            ->with('request')->checkModuleAction('profile', 'save')
            ->with('response')->isStatusCode(200)
            ->with('form')->begin()
                ->isInstanceOf('UserProfileForm')
                ->hasErrors(false)
            ->end()
            ->with('response')->begin()
                ->checkJsonContains('result', array('text' => 'Данные успешно сохранены'))
            ->end();

        $this->browser
            ->with('model')->check('User', array_merge(array('id' => $user->getId(), 'name' => $input['nickname'])));
    }

}
