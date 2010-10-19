<?php
/**
 * Авторизация
 */
class myAuthActions extends sfActions
{
    /**
     * Авторизация пользователя / сообщение о неавторизованности
     */
    public function executeLogin(sfRequest $request)
    {
        $this->setLayout("layout");

        $user = $this->getUser();
        if ($user->isAuthenticated()) {
                if (!$this->checkSubscription()) {
                    $user->signOut();
                    return $this->raiseError('Payment required', 402);
                }

            return sfView::SUCCESS;
        }

        // Запрос на авторизацию
        if ($request->isMethod('post')) {
            $form = new myAuthForm();
            $form->bind($request->getPostParameters());
            if ($form->isValid()) {
                $user->signIn($form->getUser());
                if (!$this->checkSubscription()) {
                    $user->signOut();
                    return $this->raiseError('Payment required', 402);
                }

                return sfView::SUCCESS;
            }

            return $this->raiseError($form->getErrorSchema());

        // Форвард из других контроллеров
        } else {
            return $this->raiseError('Authentification required');
        }
    }


    protected function raiseError($errorMessage, $code = 401)
    {
        $this->getResponse()->setStatusCode($code);

        $this->setTemplate('error', 'common');
        $this->setVar('code',    $code);
        $this->setVar('message', $errorMessage);

        return sfView::ERROR;
    }


    protected function checkSubscription()
    {
        $this->setVar('subscribedTill', date('Y-m-d', 0));

        $subscription = Doctrine::getTable('ServiceSubscription')
            ->getActiveUserServiceSubscription(
                $this->getUser()->getUserRecord(),
                Service::SERVICE_IPHONE
            );

        if (!$subscription) {
            return false;
        }

        $this->setVar('subscribedTill', $subscription->getSubscribedTill());
        return true;
    }

}
