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
        parent::doBind($values);

        $balanceOperation = false;

        // это у нас балансовая операция, даты глючат от валидации
        if ((null == $this->values['category_id']) && ($values['date'] == "0000-00-00")) {
            unset($this->values['date']);
            $balanceOperation = true;

            $this->values['drain'] = 0;
            $this->values['comment'] = "Начальный остаток";

            if ($this->values['amount'] <= 0) {
                $this->values['type'] = Operation::TYPE_EXPENSE;
            } else {
                $this->values['type'] = Operation::TYPE_PROFIT;
            }

            return true;
        }

        // если расход - сумма отрицательна
        if ($this->values['type'] == Operation::TYPE_EXPENSE) {
            $this->values['amount'] = ($this->values['amount'] > 0)
                                    ? -$this->values['amount'] : $this->values['amount'];
        } elseif ($this->values['type'] == Operation::TYPE_PROFIT) {
            $this->values['amount'] = ($this->values['amount'] < 0)
                                    ? -$this->values['amount'] : $this->values['amount'];
        }

        // установить drain (расход) = 1, если это тип "расход" и не балансовая операция
        if (($this->values['type'] == Operation::TYPE_EXPENSE) && !$balanceOperation) {
            $this->values['drain'] = 1;
        } else {
            $this->values['drain'] = 0;
        }

        // у переводов всегда drain = 1 и нет категории
        if ($this->values['type'] == Operation::TYPE_TRANSFER) {
            $this->values['drain'] = 1;
            $this->values['category_id'] = null;
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
