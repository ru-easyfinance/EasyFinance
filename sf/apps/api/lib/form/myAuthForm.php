<?php
/**
 * Форма авторизации
 */
class myAuthForm extends BaseMyAuthForm
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
}
