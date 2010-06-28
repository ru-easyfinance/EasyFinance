<?php

/**
 * Форма для импорта операции
 */
class OperationImportForm extends BaseFormDoctrine
{
    /**
     * Setup
     */
    public function setup()
    {
        // No wiggets
        $this->setValidators(array(
            'email'         => new sfValidatorDoctrineChoice(array(
                'model'     => 'User',
                'column'    => array('user_service_mail'),
            )),
            'type'          => new sfValidatorChoice(array(
                'choices'   => array(Operation::TYPE_PROFIT, Operation::TYPE_EXPENSE)
            )),
            'amount'        => new sfValidatorNumber(),
            'description'   => new sfValidatorString(),
            'account'       => new sfValidatorString(),
            'source'        => new sfValidatorString(),
            'id'            => new sfValidatorString(),
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
     * для проверки уникальности ID-операции
     */
    protected function doBind(array $values)
    {
        $values['source_uid'] = $values['source'];
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

        // Счет для привязки операции
        $values['account_id'] = Doctrine::getTable('Account')->findLinkedWithSource($values['user_id'], $values['source']);

        // Тип операции и сумма
        $values['money'] = abs((float)$values['amount']);
        $values['drain'] = $values['type']^1;

        // Дата и время
        $values['date'] = date('Y-m-d');
        $values['time'] = date('H:i:s');

        // Черновик
        $values['accepted'] = Operation::STATUS_DRAFT;

        // Источник
        $values['source_id'] = $values['source'];
        $values['SourceOperation'] = array(
            'source_uid'           => $values['source'],
            'source_operation_uid' => $values['id'],
        );

        // Комментарий
        $values['comment'] = sprintf("%s %s %s\n",
            $values['source'], $values['description'], $values['account']);

        unset( $values['account']);
        unset($values['description']);
        unset($values['account']);
        unset($values['id']);
        unset($values['source']);

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