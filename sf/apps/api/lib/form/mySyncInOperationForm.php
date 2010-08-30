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
        $accounts = Doctrine_Query::create()
            ->select("a.id key, a.id value")
            ->from("Account a")
            ->andWhere(
                "a.user_id = ?", $this->getUser()->getUserRecord()->getId()
            )
            ->execute(array(), 'FetchPair');

        $categories = Doctrine_Query::create()
            ->select("c.id, c.id type_id")
            ->from("Category c")
            ->andWhere(
                "c.user_id = ?", $this->getUser()->getUserRecord()->getId()
            )
            ->execute(array(), 'FetchPair');

        $accounts[]   = null;
        $categories[] = null;

        $this->setValidators(array(
            'account_id'  => new sfValidatorChoice(
                array(
                    'choices'     => $accounts,
                    'required'    => false,
                    'empty_value' => null
                ),
                array(
                    'invalid' => 'No such account %value%'
                )
            ),
            'category_id' => new sfValidatorChoice(
                array(
                    'choices'     => $categories,
                    'required'    => false,
                    'empty_value' => null
                ),
                array(
                    'invalid' => 'No such category %value%'
                )
            ),
            'amount'      => new sfValidatorNumber(),
            'date'        => new sfValidatorDate(),
            'type'        => new sfValidatorChoice(array('choices' => Operation::getTypes())),
            'comment'     => new sfValidatorString(array('required' => false)),
            'accepted'    => new sfValidatorBoolean(
                array('empty_value' => 0)
            ),
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
     *     знак у суммы и прочая требуха
     * @see sfForm
     */
    protected function doBind(array $values)
    {
        // не используем поля в зависимости от типа операции
        switch ($values['type']) {
            case Operation::TYPE_BALANCE:
                unset($this['date'], $this['transfer_account_id'], $this['transfer_amount']);
                $values['accepted'] = true;
                break;
            case Operation::TYPE_TRANSFER:
                unset($this['category_id']);
                break;
            default:
                unset($this['transfer_account_id'], $this['transfer_amount']);
                break;
        }

        // в зависимости от подтвержденности операции отключаем необходимости полей
        if (!$values['accepted']) {
            $this->setDraftValidation();
        }

        parent::doBind($values);

        switch ($this->values['type']) {
            case Operation::TYPE_TRANSFER:
                $this->values['amount'] = -abs($this->values['amount']);
                $this->values['transfer_amount'] = abs($this->values['transfer_amount']);
                $this->values['category_id'] = $this->getUser()
                    ->getUserRecord()->getDebtCategoryId();
                break;
            case Operation::TYPE_EXPENSE:
                $this->values['amount'] = -abs($this->values['amount']);
                break;
            case Operation::TYPE_BALANCE:
                $this->values['comment'] = "Начальный остаток";
                break;
            default:
                $this->values['amount'] = abs($this->values['amount']);
                break;
        }

    }


    /**
     * Черновик: отключаем необходимости полей
     *
     * @return void
     */
    protected function setDraftValidation()
    {
        $this->validatorSchema['account_id']->addOption('required', false);
        $this->validatorSchema['date']->addOption('required', false);

        if ($this->validatorSchema['transfer_account_id']) {
            $this->validatorSchema['transfer_account_id']->setOption('required', false);
            $this->validatorSchema['transfer_amount']->setOption('required', false);
        }
    }


    /**
     * @return string Имя связанной модели
     */
    public function getModelName()
    {
        return 'Operation';
    }


    public function getUser()
    {
        return sfContext::getInstance()->getUser();
    }

}
