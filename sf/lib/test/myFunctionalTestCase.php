<?php

/**
 * Base test class for all functional tests
 */
abstract class myFunctionalTestCase extends sfPHPUnitFunctionalTestCase
{
    /**
     * Inject your own functional testers
     *
     * @see sfTestFunctionalBase::setTesters()
     *
     * @return array
     *          'request'  => 'sfTesterRequest',
     *          'response' => 'sfTesterResponse',
     *          'user'     => 'sfTesterUser',
     */
    protected function getFunctionalTesters()
    {
        return array(
            'request'  => 'myFunctionalTesterRequest',
            'response' => 'myFunctionalTesterResponse',
            'model'    => 'sfTesterDoctrine',
        );
    }


    /**
     * Создать и авторизовать пользователя
     *
     * @param  User $user
     * @return User
     */
    protected function authenticateUser(User $user = null)
    {
        if (!$user) {
            $user = $this->helper->makeUser();
        }

        // При создании браузера инициализируется сессия
        $context = $this->browser->getContext();

        $context->getUser()->signIn($user);

        // Пользователь сбрасывает данные в SessionStorage
        $context->getUser()->shutdown();
        // Сохраняет сессию в файл
        $context->getStorage()->shutdown();

        return $user;
    }

}
