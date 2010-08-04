<?php
/**
 * Валидатор пользователя (логина/пароля)
 */
class myValidatorAuthUser extends sfValidatorBase
{

    public function configure($options = array(), $messages = array())
    {
        $this->addOption('username_field', 'login');
        $this->addOption('password_field', 'password');
        $this->addOption('throw_global_error', false);

        $this->setMessage('invalid', 'The username and/or password is invalid.');
    }


    protected function doClean($values)
    {
        $username = isset($values[$this->getOption('username_field')]) ? $values[$this->getOption('username_field')] : '';
        $password = isset($values[$this->getOption('password_field')]) ? $values[$this->getOption('password_field')] : '';

        if ($username) {
            if ($user = $this->getTable()->findOneByUserLogin($username)) {
                // password is ok?
                if ($user->checkPassword($password)) {
                    return array_merge($values, array('user' => $user));
                }
            }
        }

        if ($this->getOption('throw_global_error')) {
            throw new sfValidatorError($this, 'invalid');
        }

        throw new sfValidatorErrorSchema($this, array($this->getOption('username_field') => new sfValidatorError($this, 'invalid')));
    }


    protected function getTable()
    {
        return Doctrine::getTable('User');
    }

}
