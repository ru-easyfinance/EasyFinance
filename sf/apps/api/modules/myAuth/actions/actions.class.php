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
                    return $this->raiseError('Subscription required');
                }

                return sfView::SUCCESS;
            }

            return $this->raiseError($form->getErrorSchema());

        // Форвард из других контроллеров
        } else {
            return $this->raiseError('Authentification required');
        }
    }


    protected function raiseError($errorMessage)
    {
        $this->getResponse()->setStatusCode(401);

        $this->setTemplate('error', 'common');
        $this->setVar('code',    401);
        $this->setVar('message', $errorMessage);

        return sfView::ERROR;
    }


    protected function checkSubscription()
    {
        $subscription = Doctrine::getTable('ServiceSubscription')
            ->getActiveUserServiceSubscription(
                $this->getUser()->getUserRecord()->getId(),
                Service::SERVICE_IPHONE
            );

        if (!$subscription) {
            return false;
        }

        $this->setVar('subscribedTill', $subscription->getSubscribedTill());
        return true;
    }

}
