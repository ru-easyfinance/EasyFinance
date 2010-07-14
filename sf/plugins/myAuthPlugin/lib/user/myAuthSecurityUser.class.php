<?php

class myAuthSecurityUser extends sfBasicSecurityUser
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
     * @param  bool $remember
     * @return void
     */
    public function signIn(User $user, $remember = false)
    {
        $this->user = $user;
        $this->setAttribute('id', $user->getId(), 'user');

        $this->setAuthenticated(true);
        $this->clearCredentials();

        if ($remember)
        {
            $expiration_age = sfConfig::get('app_my_auth_plugin_remember_key_expiration_age', 15 * 24 * 3600);

            // убить все старые кючи
            Doctrine::getTable('myAuthRememberKey')->createQuery()
            ->delete()
            ->where('created_at < ?', date('Y-m-d H:i:s', time() - $expiration_age))
            ->execute();

            // убить все ключи этого пользователя
            Doctrine::getTable('myAuthRememberKey')->createQuery()
            ->delete()
            ->where('user_id = ?', $user->getId())
            ->execute();

            // создать новый ключ
            $key = $this->generateRandomKey();

            // сохранить ключ
            $rk = new myAuthRememberKey();
            $rk->setRememberKey($key);
            $rk->setUser($user);
            $rk->setIpAddress($_SERVER['REMOTE_ADDR']);
            $rk->save();

            // отдать ключ в виде печенья
            $remember_cookie = sfConfig::get('app_my_auth_plugin_remember_cookie_name', 'myRemember');
            sfContext::getInstance()->getResponse()->setCookie($remember_cookie, $key, time() + $expiration_age);
        }
    }


    /**
     * Принудительный выход пользователя
     */
    public function signOut() {
        $this->getAttributeHolder()->removeNamespace('user');
        $this->user = null;
        $this->clearCredentials();
        $this->setAuthenticated(false);
        $expiration_age = sfConfig::get('app_my_auth_plugin_remember_key_expiration_age', 15 * 24 * 3600);
        $remember_cookie = sfConfig::get('app_my_auth_plugin_remember_cookie_name', 'myRemember');
        sfContext::getInstance()->getResponse()->setCookie($remember_cookie, '', time() - $expiration_age);
    }


    /**
     * Получить идентификатор пользователя
     */
    public function getId()
    {
        return $this->getUserRecord()->getId();
    }

    /**
     * Делает случайный ключ.
     *
     * @param int $len длина промежуточного ключа
     * @return string
     */
    protected function generateRandomKey($len = 20)
    {
        $string = '';
        $pool   = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $max    = strlen($pool) - 1;
        for ($i = 1; $i <= $len; $i++) {
            $string .= substr($pool, rand(0, $max), 1);
        }

        return md5($string);
    }
}