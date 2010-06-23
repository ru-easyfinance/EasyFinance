<?php

/**
 * Форма для обработки запросов на синхронизацию операций
 */
class mySyncInOperationTransferCompleteForm extends mySyncInOperationForm
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
            'transfer_id' => new sfValidatorInteger(array('min' => 1)),
            'transfer_amount' => new sfValidatorNumber(),
        ));

        if ($this->isNew()) {
            $this->setValidator('user_id', new sfValidatorPass());
        }

        $this->validatorSchema->setOption('allow_extra_fields', true);

        $this->widgetSchema->setNameFormat('%s');
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
        $this->disableLocalCSRFProtection();
    }


    protected function doBind(array $values)
    {
        parent::doBind($values);

        // у переводов всегда drain = 1 и нет категории
        if ($this->values['type'] == Operation::TYPE_TRANSFER) {
            $this->values['drain'] = 1;
            $this->values['category_id'] = null;

            // входящие средства всегда положительны
            $this->values['amount'] = abs($this->values['amount']);
            $this->values['transfer_amount'] = abs($this->values['transfer_amount']);
        }

    }


}
