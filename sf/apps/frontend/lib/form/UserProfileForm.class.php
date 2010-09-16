<?php
/**
 * Форма: профиль пользователя
 *
 * @method User getObject() Returns the current form's model object
 *
 * @package    EasyFinance
 * @subpackage form
 * @author     Svel <svel.sontz@gmail.com>
 */
class UserProfileForm extends BaseFormDoctrine
{
    public function configure()
    {
        $this->disableLocalCSRFProtection();

        $this->setWidgets(array(
            'user_login'        => new sfWidgetFormInputText(array(), array('disabled' => 'disabled')),
            'user_service_mail' => new sfWidgetFormInputText(),
            'name'              => new sfWidgetFormInputText(),

            'user_mail'         => new sfWidgetFormInputText(),

            'password_new'      => new sfWidgetFormInputText(),
            'password_repeat'   => new sfWidgetFormInputText(),
            'password'          => new sfWidgetFormInputText(),

            'notify'            => new sfWidgetFormInputCheckbox(),
        ));


        $this->setValidators(array(
            'id'                => new sfValidatorChoice(array(
                'choices'       => array($this->getObject()->get('id')),
                'empty_value'   => $this->getObject()->get('id'),
                'required'      => true,
            )),
            'user_login'        => new sfValidatorDoctrineUnique(array(
                'model'         => $this->getModelName(),
                'column'        => array('user_login', 'id'),
                'required'      => true,
            ), array(
                'invalid'       => "Выберите другой логин. Этот уже занят.",
            )),
            'user_service_mail' => new sfValidatorAnd(array(
                new sfValidatorEmail(array('max_length' => 100), array(
                    'invalid'   => 'В названии ящика есть недопустимые символы.' . "<br />\n" .
                                   'Постарайтесь задать почту латинскими буквами, цифрами, ' .
                                   'без пробелов или других непонятных знаков.',
                )),
                new sfValidatorDoctrineUnique(array(
                    'model'     => $this->getModelName(),
                    'column'    => array('user_service_mail', 'id')
                ), array(
                    'invalid'   => "Такой адрес уже существует.<br />\nПожалуйста, выберите другой адрес.",
                )),
            ), array('required' => false)),
            'name'              => new sfValidatorString(array('max_length' => 100, 'required' => true), array('required' => "Нужно заполнить имя")),
            'user_mail'         => new sfValidatorEmail(array('max_length' => 100, 'required' => false), array('invalid' => "В адресе e-mail есть недопустимые символы.", 'required' => "Нужно заполнить адрес e-mail")),

            'password_new'      => new sfValidatorString(array('required' => false, 'max_length' => 40)),
            'password'          => new sfValidatorAnd(array(
                new sfValidatorString(array('required' => false, 'max_length' => 40)),
                new sfValidatorCallback(array('callback' => array($this, 'checkPassword'))),
            ), array('required' => false)),

            'notify'            => new sfValidatorBoolean(),
        ));

        $this->widgetSchema->setNameFormat('%s');

        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

        $this->setupInheritance();

        // пароль обязателен, если установлено поле e-mail
        // пароль обязателен, если установлен новый пароль
        $this->mergePreValidator(new sfValidatorCallback(array(
            'callback' => array($this, 'checkPasswordNotEmpty'),
        )));

    }


    /**
     * Получить название модели
     *
     * @return string
     */
    public function getModelName()
    {
        return 'User';
    }


    /**
     *
     */
    protected function doBind(array $values)
    {
        $values['id'] = $this->getObject()->getId();

        // эту х-ню делать в модели
        if (isset($values['user_service_mail']) && !empty($values['user_service_mail'])) {
            $values['user_service_mail'] = $values['user_service_mail'] . '@mail.easyfinance.ru';
        }

        parent::doBind($values);

        // не обновлять e-mail если идентичен или когда не введен пароль
        if (isset($this->values['user_mail']) && !empty($this->values['user_mail']) && ($this->getObject()->getUserMail() != $this->values['user_mail'])) {
            if (!isset($this->values['password']) || empty($this->values['password'])) {
                unset($this->values['user_mail'], $this['user_mail'], $this->values['password']);
            }
        }

        // обновить пароль, если задан новый (и не пустой) и старый
        if (isset($this->values['password']) && !empty($this->values['password']) && isset($this->values['password_new']) && !empty($this->values['password_new'])) {
            $this->values['password'] = $this->values['password_new'];
            unset($this->values['password_new'], $this['password_new']);
        // не трогать пароль без необходимости
        } else {
            unset($this->values['password'], $this->values['password_new'], $this['password'], $this['password_new']);
        }

        return $values;
    }


    /**
     * Проверить пароль
     *
     * @see User
     */
    public function checkPassword($validator, $value)
    {
        if (!$this->getObject()->checkPassword($value)) {
            $error = new sfValidatorError($validator, 'Неверный пароль', array('value' => $value));

            // throw an error bound to the field
            throw new sfValidatorErrorSchema($validator, array('password' => $error));
        }

        return $value;
    }


    /**
     * checkUserMailVsPassword
     */
    public function checkPasswordNotEmpty(sfValidatorBase $validator, array $values)
    {
        if (!empty($values['user_mail']) && ($values['user_mail'] != $this->getObject()->getUserMail()) && empty($values['password'])) {
            $error = new sfValidatorError($validator, 'Нужно заполнить пароль для обновления', array('value' => $values['user_mail']));

            // throw an error bound to the field
            throw new sfValidatorErrorSchema($validator, array('password' => $error));
        }

        if (!empty($values['password_new']) && empty($values['password'])) {
            $error = new sfValidatorError($validator, 'Нужно заполнить пароль для замены', array('value' => $values['password_new']));

            // throw an error bound to the field
            throw new sfValidatorErrorSchema($validator, array('password' => $error));
        }

        return $values;
    }

}
