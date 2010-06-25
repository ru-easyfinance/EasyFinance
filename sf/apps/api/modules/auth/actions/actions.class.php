<?php
/**
 * Авторизация
 */
class authActions extends sfActions
{
    /**
     * Авторизация пользователя / сообщение о неавторизованности
     *
     * @param sfRequest $request A request object
     */
    public function executeLogin(sfRequest $request)
    {
        $this->setLayout("layout");

        $user = $this->getUser();
        if ($user->isAuthenticated()) {
            return sfView::SUCCESS;
        }

        if ($request->isMethod('post')) {
            $form = new myAuthForm();
            $form->bind($request->getPostParameters());
            if ($form->isValid()) {
                $user->signIn($form->getUser());

                return sfView::SUCCESS;
            }

            return $this->raiseError($form);
        } else {
            return $this->raiseError();
        }
    }


    protected function raiseError(myAuthForm $form = null)
    {
        $this->getResponse()->setHttpHeader('WWW_Authenticate', "Authentification required");
        $this->getResponse()->setStatusCode(401);

        $this->setVar('form', $form);

        return sfView::ERROR;
    }

}
