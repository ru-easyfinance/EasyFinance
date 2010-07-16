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
        $fieldsToUse = array('currency_id', 'name', 'description', 'state');

        if ($this->getObject()->isNew()) {
            unset($this['id']);
            $fieldsToUse[] = 'type_id';
        } else {
            unset($this['type_id']);
        }
        $this->useFields($fieldsToUse);

        # Description
        $this->validatorSchema['description']->setOption('required', false);

        # Balance
        $this->widgetSchema['initBalance'] = new sfWidgetFormInputText();
        $this->validatorSchema['initBalance'] = new sfValidatorString(array('required' => false));


        $this->validatorSchema->setOption('allow_extra_fields', true);
        $this->disableLocalCSRFProtection();
    }
}
