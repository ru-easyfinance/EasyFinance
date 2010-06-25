<?php
/**
 * Форма авторизации
 */
class myAuthForm extends BaseForm
{
    /**
     * @see sfForm
     */
    public function setup()
    {
        $this->setValidators(array(
            'login'    => new sfValidatorString(),
            'password' => new sfValidatorString(),
        ));

        $this->validatorSchema->setPostValidator(new myUserValidator());

        $this->widgetSchema->setNameFormat('[%s]');

        $this->disableLocalCSRFProtection();
    }

    /**
     * Вернуть объект пользователя
     *
     * @see    myUserValidator
     * @return myApiUser
     */
    public function getUser()
    {
        return $this->values['user'];
    }
}
