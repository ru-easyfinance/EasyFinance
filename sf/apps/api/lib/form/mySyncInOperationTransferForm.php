<?php

/**
 * Форма для обработки запросов на синхронизацию операций
 */
class mySyncInOperationTransferForm extends mySyncInOperationForm
{
    /**
     * Config
     */
    public function configure()
    {
        $this->setValidators(array(
            'account_id'  => new sfValidatorInteger(array('min' => 1)),
            'category_id' => new sfValidatorPass(),
            'amount'      => new sfValidatorNumber(),
            'date'        => new sfValidatorDate(),
            'type'        => new sfValidatorChoice(array('choices' => Operation::getTypes())),
            'comment'     => new sfValidatorString(array('required' => false)),
            'accepted'    => new sfValidatorBoolean(),
            'created_at'  => new myValidatorDatetimeIso8601(),
            'updated_at'  => new myValidatorDatetimeIso8601(),
            'deleted_at'  => new myValidatorDatetimeIso8601(array('required' => false)),
            'transfer'    => new sfValidatorInteger(array('min' => 1)),
        ));

        if ($this->isNew()) {
            $this->setValidator('user_id', new sfValidatorPass());
        }

        $this->validatorSchema->setOption('allow_extra_fields', true);

        $this->widgetSchema->setNameFormat('%s');
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
        $this->disableLocalCSRFProtection();
    }

}
