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
        $this->setWidgets(array(
          'id'          => new sfWidgetFormInputHidden(),
          'email'       => new sfWidgetFormInputHidden(),
          'type'        => new sfWidgetFormInputHidden(),
          'timestamp'   => new sfWidgetFormInputHidden(),
          'amount'      => new sfWidgetFormInputHidden(),
          'description' => new sfWidgetFormInputHidden(),
          'account'     => new sfWidgetFormInputHidden(),
          'place'       => new sfWidgetFormInputHidden(),
          'balance'     => new sfWidgetFormInputHidden(),
        ));

        $this->setValidators(array(
            'id'          => new sfValidatorString(),
            'email'       => new sfValidatorDoctrineChoice(array(
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


        $this->widgetSchema->setNameFormat('%s');
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
        $this->disableLocalCSRFProtection();

        parent::setup();
    }


    /**
     * Маппинг входящих значений с свойствам операции
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
