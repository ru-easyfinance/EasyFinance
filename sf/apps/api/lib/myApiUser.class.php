<?php

class myApiUser extends sfBasicSecurityUser
{
    protected $user = null;

   /**
    * Получить объект пользователя по идентификатору в сессии
    *
    * @return User
    */
    public function getUserRecord()
    {
        if (!$this->user && $id = $this->getAttribute('id', null, 'user')) {
            $this->user = Doctrine::getTable('User')->find($id);

            if (!$this->user) {
                $this->signOut();
                throw new sfException('The user does not exist anymore in the database.');
            }
        }

        return $this->user;
    }


    /**
     * Авторизовать пользователя
     *
     * @param  User $user
     * @return void
     */
    public function signIn(User $user)
    {
        $this->user = $user;
        $this->setAttribute('id', $user->getId(), 'user');

        $this->setAuthenticated(true);
        $this->clearCredentials();
    }


    /**
     * Принудительный выход пользователя
     */
    public function signOut() {
        $this->getAttributeHolder()->removeNamespace('user');
        $this->user = null;
        $this->clearCredentials();
        $this->setAuthenticated(false);
    }


    /**
     * Получить идентификатор пользователя
     */
    public function getId()
    {
        return $this->getUserRecord()->getId();
    }

}
