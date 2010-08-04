<?php
/**
 * Форма авторизации
 */
class BaseMyAuthForm extends BaseForm
{
    /**
     * @see sfForm
     */
    public function setup()
    {
        $this->setValidators(array(
            'login'    => new sfValidatorString(),
            'password' => new sfValidatorString(),
            'remember' => new sfValidatorBoolean(),
        ));

        $this->setWidgets(array(
            'login'    => new sfWidgetFormInputText(),
            'password' => new sfWidgetFormInputPassword(),
            'remember' => new sfWidgetFormInputCheckbox(),
        ));

        $this->widgetSchema->setLabels(array(
            'login'    => 'Логин',
            'password' => 'Пароль',
            'remember' => 'Запомнить&nbsp;меня',
        ));

        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

        $validator = new myValidatorAuthUser();
        $validator->setMessage('invalid', 'Неверный логин и/или пароль.');
        $validator->setOption('throw_global_error', true);

        $this->validatorSchema->setPostValidator($validator);

        $this->widgetSchema->setNameFormat('auth[%s]');

        $this->disableLocalCSRFProtection();
    }


    /**
     * Вернуть объект пользователя
     *
     * @see    myUserValidator
     * @return myAuthSecurityUser
     */
    public function getUser()
    {
        return $this->values['user'];
    }


    # а это используется?
    public function getModelName()
    {
        return 'Auth';
    }

}
