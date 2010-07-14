<?php
/**
 * Базовые действия авторизации
 */
class BasemyAuthActions extends sfActions
{
    /**
     * Авторизация пользователя
     */
    public function executeLogin(sfRequest $request)
    {
        $this->setLayout("layout");

        $user = $this->getUser();
        if ($user->isAuthenticated()) {
            return $this->redirect('@homepage');
        }

        // Запрос на авторизацию
        if ($request->isMethod('post')) {
            $this->form = new myAuthForm();
            $params = $request->getPostParameters();
            $this->form->bind($params['auth']);

            if ($this->form->isValid()) {
                $user->signIn($this->form->getUser());
                return $this->redirect('@homepage');
            }

        // Форвард из других контроллеров
        } else {
            $this->form = new myAuthForm();
        }

        return sfView::SUCCESS;
    }

    /**
     * Деавторизация пользователя
     */
    public function executeLogout(sfRequest $request)
    {
        $this->setLayout("layout");
        $user = $this->getUser();

        if ($user->isAuthenticated()) {
            $user->signOut();
        }

        return $this->redirect('@homepage');
    }

}