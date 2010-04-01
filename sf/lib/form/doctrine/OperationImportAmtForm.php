<?php

/**
 * Форма для импорта операции из AMT
 */
class OperationImportAmtForm extends BaseFormDoctrine
{
    /**
     * Setup
     */
    public function setup()
    {
        // No wiggets

        $this->setValidators(array(
            'id'        => new sfValidatorString(),
            'email'     => new sfValidatorDoctrineChoice(array(
                'model'  => 'User',
                'column' => array('user_service_mail'),
            )),
            'type'        => new sfValidatorChoice(array('choices' => array(Operation::TYPE_PROFIT, Operation::TYPE_EXPENSE))),
            'timestamp'   => new sfValidatorDateTime(),
            'amount'      => new sfValidatorNumber(),
            'description' => new sfValidatorString(),
            'account'     => new sfValidatorString(array('required' => false)),
            'place'       => new sfValidatorString(array('required' => false)),
            'balance'     => new sfValidatorString(array('required' => false)),
        ));


        $this->validatorSchema->setPostValidator(
           new sfValidatorDoctrineUnique(array(
                'model'  => 'SourceOperation',
                'column' => array('source_uid', 'source_operation_uid'),
            ), array(
                'invalid' => 'An operation with same UID already exists.',
            ))
        );

        // см. doBind()
        $this->validatorSchema->setOption('allow_extra_fields', true);
        $this->validatorSchema->setOption('filter_extra_fields', false);


        $this->widgetSchema->setNameFormat('%s');
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
        $this->disableLocalCSRFProtection();

        parent::setup();
    }


    /**
     * Подмешать во входящие параметры поля, которые сможет использовать PostValidator
     * для проверки уникальности ID-операции AMT
     */
    protected function doBind(array $values)
    {
        $values['source_uid'] = Operation::SOURCE_AMT;
        $values['source_operation_uid'] = isset($values['id']) ? $values['id'] : '';

        parent::doBind($values);
    }


    /**
     * Маппинг входящих значений к свойствам объекта операции
     *
     * @param  array $values - исходные значения
     * @return array         - преобразованные значения
     */
    public function processValues($values)
    {
        // User
        // TODO: переделать через один запрос вместе с валидацией
        $values['user_id'] = Doctrine::getTable('User')->findByUserServiceMail($values['email'])
            ->getFirst()->getId();
        unset($values['email']);


        // Тип операции и сумма
        $amount = abs($values['amount']);
        switch ($values['type']) {

            case Operation::TYPE_PROFIT:
                $values['money'] = $amount;
                break;

            case Operation::TYPE_EXPENSE:
                $values['money'] = -$amount;
                break;

            default:
                throw new Exception("Unxpected operation type `{$values['type']}`");
        }
        $values['drain'] = $values['type'];
        unset($values['amount']);


        // Дата и время
        $date = new DateTime($values['timestamp']);
        $values['date'] = $date->format('Y-m-d');
        $values['time'] = $date->format('H:i:s');
        unset($values['timestamp']);


        // Черновик
        $values['accepted'] = Operation::STATUS_DRAFT;


        // Источник
        $values['source_id'] = Operation::SOURCE_AMT;
        $values['SourceOperation'] = array(
            'source_uid'           => Operation::SOURCE_AMT,
            'source_operation_uid' => $values['id'],
        );
        unset($values['id']);


        // Комментарий
        $values['comment'] = sprintf("%s\n\nНомер счета: %s\nМесто совершения операции: %s\nТекущий баланс: %s",
            $values['description'], $values['account'], $values['place'], $values['balance']);
        unset($values['description'], $values['account'], $values['place'], $values['balance']);


        return $values;
    }


    /**
     * Model
     */
    public function getModelName()
    {
        return 'Operation';
    }

}
