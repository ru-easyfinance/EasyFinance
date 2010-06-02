<?php

/**
 * Форма для обработки запросов на синхронизацию счетов
 */
class mySyncInAccountForm extends BaseFormDoctrine
{
    /**
     * Config
     */
    public function configure()
    {
        $this->setValidators(array(
            'user_id'     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('User'))),
            'type_id'     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('AccountType'))),
            'currency_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Currency'))),
            'id'          => new sfValidatorPass(),
            'name'        => new sfValidatorString(array('max_length' => 255)),
            'description' => new sfValidatorString(array('max_length' => 255)),
            'created_at'  => new myValidatorDatetimeIso8601(),
            'updated_at'  => new myValidatorDatetimeIso8601(),
            'deleted_at'  => new myValidatorDatetimeIso8601(array('required' => false)),
        ));

        $this->widgetSchema->setNameFormat('%s');
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
        $this->disableLocalCSRFProtection();
    }

    /**
     * @return string Имя связанной модели
     */
    public function getModelName()
    {
        return 'Account';
    }


}
