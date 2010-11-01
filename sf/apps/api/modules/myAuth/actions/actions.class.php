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

        if (!$user->isAuthenticated()) {
            if ($request->isMethod('post')) {
                $form = new myAuthForm();
                $form->bind($request->getPostParameters());

                if ($form->isValid()) {
                    $userRecord = $form->getUser();
                } else {
                    return $this->raiseError($form->getErrorSchema());
                }
            } else {
                return $this->raiseError('Authentification required');
            }

            $user->signIn($userRecord);
        }

        if (!$this->checkSubscription()) {
            $user->signOut();
            return $this->raiseError('Payment required', 402);
        }

        return sfView::SUCCESS;
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
