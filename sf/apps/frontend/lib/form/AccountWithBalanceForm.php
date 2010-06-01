<?php

/**
 * Форма для создания/редактирования счета с начальным балансом
 */
class AccountWithBalanceForm extends BaseAccountForm
{
    /**
     * Config
     */
    public function configure()
    {
        unset($this['id']);
        $this->useFields(array('type_id', 'currency_id', 'name', 'description'));

        # Description
        $this->validatorSchema['description']->setOption('required', false);

        # Balance
        $this->widgetSchema['initPayment'] = new sfWidgetFormInputText();
        $this->validatorSchema['initPayment'] = new sfValidatorNumber(array('required' => false));


        $this->validatorSchema->setOption('allow_extra_fields', true);
        $this->disableLocalCSRFProtection();
    }


    /**
     * Маппинг входящих значений к свойствам объекта операции
     *
     * @param  array $values - исходные значения
     * @return array         - преобразованные значения
     */
    public function processValues($values)
    {
        $operation = array(
            'user_id'     => $this->getObject()->getUserId(),
            'amount'      => $values['initPayment'],
            'date'        => '0000-00-00',
            'time'        => '00:00:00',
            'drain'       => 0,
            'type'        => 1,
            'comment'     => 'Начальный остаток',
            'accepted'    => 1,
        );

        $values = parent::processValues($values);
        $values['Operations'] = array($operation);

        return $values;
    }

}
