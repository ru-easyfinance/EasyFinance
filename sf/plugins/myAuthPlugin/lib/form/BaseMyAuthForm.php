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
        // В форме ошибка отображается как 'Required' вместо 'Обязательное поле'
        // Почему не понятно
        $validator->setMessage('required', 'Обязательное поле.');
        $validator->setMessage('invalid', 'Неверный логин и/или пароль.');

        $this->validatorSchema->setPostValidator($validator);

        $this->widgetSchema->setNameFormat('auth[%s]');

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

    public function getModelName()
    {
        return 'Auth';
    }
}