<?php

/**
 * Форма для обработки запросов на синхронизацию операций
 */
class mySyncInOperationForm extends BaseFormDoctrine
{
    /**
     * Config
     */
    public function configure()
    {
        $this->setValidators(array(
            'account_id'  => new sfValidatorPass(),
            'category_id' => new sfValidatorPass(),
            'amount'      => new sfValidatorNumber(),
            'date'        => new sfValidatorDate(),
            'time'        => new sfValidatorTime(),
            'type'        => new sfValidatorInteger(),
            'comment'     => new sfValidatorString(array('required' => false)),
            //'source_id'   => new sfValidatorString(array('max_length' => 8, 'required' => false)),
            //'accepted'    => new sfValidatorInteger(array('required' => false)),
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
        return 'Operation';
    }

}
