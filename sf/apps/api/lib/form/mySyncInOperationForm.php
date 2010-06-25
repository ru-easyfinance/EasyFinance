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
            'transfer_account_id' => new sfValidatorInteger(array('min' => 1)),
            'transfer_amount'     => new sfValidatorNumber(),
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
     * Подмешивает дополнительные поля и значения:
     *     drain, знак у суммы и прочая требуха
     * @see sfForm
     */
    protected function doBind(array $values)
    {

        switch ($values['type']) {
            case Operation::TYPE_BALANCE:
                unset($this['date'], $this['transfer_account_id'], $this['transfer_amount']);
                break;
            case Operation::TYPE_TRANSFER:
                unset($this['category_id']);
                break;
            default:
                unset($this['transfer_account_id'], $this['transfer_amount']);
                break;
        }

        parent::doBind($values);

        $this->values['drain'] = 0; // @deprecated

        switch ($this->values['type']) {
            case Operation::TYPE_TRANSFER:
                $this->values['amount'] = -abs($this->values['amount']);
                $this->values['transfer_amount'] = abs($this->values['transfer_amount']);
                $this->values['drain'] = 1;
                $this->values['category_id'] = null;
                break;
            case Operation::TYPE_EXPENSE:
                $this->values['amount'] = -abs($this->values['amount']);
                $this->values['drain'] = 1;
                break;
            case Operation::TYPE_BALANCE:
                $this->values['comment'] = "Начальный остаток";
                if ($this->values['amount'] < 0) {
                    $this->values['drain'] = 1;
                }
                break;
            default:
                $this->values['amount'] = abs($this->values['amount']);
                break;
        }

    }


    /**
     * @return string Имя связанной модели
     */
    public function getModelName()
    {
        return 'Operation';
    }

}
