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
            'type_id'     => new sfValidatorPass(),
            'currency_id' => new sfValidatorPass(),
            'name'        => new sfValidatorString(array('max_length' => 255)),
            'description' => new sfValidatorString(array('max_length' => 255)),
            'created_at'  => new myValidatorDatetimeIso8601(),
            'updated_at'  => new myValidatorDatetimeIso8601(),
            'deleted_at'  => new myValidatorDatetimeIso8601(array('required' => false)),
        ));

        if ($this->isNew()) {
            $this->setValidator('user_id', new sfValidatorPass());
        }

        $this->validatorSchema->setOption('allow_extra_fields', true);

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
