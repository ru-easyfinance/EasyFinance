<?php

class myFrontendUser extends sfBasicSecurityUser
{

    protected $user = null;

   /**
    * Получить объект пользователя по идентификатору в сессии
    *
    * @return User
    */
    public function getUserRecord() {
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
    * Принудительный выход пользователя
    */
    public function signOut() {
        $this->getAttributeHolder()->removeNamespace('user');
        $this->user = null;
        $this->clearCredentials();
        $this->setAuthenticated(false);
    }

    /**
     * Получить имя пользователя
     *
     * @return string
     */
    public function getName() {
        if (!$this->user) {
            $this->getUserRecord();
        }

        return $this->user->getUserName();
    }

}
