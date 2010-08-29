<?php

/**
 * Пользователь системы
 */
class User extends BaseUser
{
    /**
     * Сравнить строку с паролем пользователя
     *
     * @param  string  $password
     * @return boolean
     */
    public function checkPassword($password)
    {
        return $this->getPassword() == sha1($password);
    }


    /**
     * Установить пароль
     */
    public function setPassword($password)
    {
        $this->_set('password', sha1($password));

        return $this->_get('password');
    }

}
