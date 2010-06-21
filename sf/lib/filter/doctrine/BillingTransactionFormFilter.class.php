<?php

/**
 * Настройки фильтров для транзакций
 *
 */
class BillingTransactionFormFilter extends BaseBillingTransactionFormFilter
{
    /**
     * Конфигурация
     *
     */
    public function configure()
    {
        $this->setWidgets(array(
          'user_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('User'), 'add_empty' => true)),
          'paysystem'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
          'service_id'      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Service'), 'add_empty' => true)),
          'subscription_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ServiceSubscription'), 'add_empty' => true)),
          'price'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
          'term'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
          'total'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
          'status'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
          'success'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
          'error_code'      => new sfWidgetFormFilterInput(),
          'error_message'   => new sfWidgetFormFilterInput(),
          'created_at'      => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormJQueryDate(array('config' => '{}')), 'to_date' => new sfWidgetFormJQueryDate(array('config' => '{}')), 'with_empty' => false)),
          'updated_at'      => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormJQueryDate(array('config' => '{}')), 'to_date' => new sfWidgetFormJQueryDate(array('config' => '{}')), 'with_empty' => false)),
        ));

        $this->widgetSchema->setNameFormat('billing_transaction_filters[%s]');
    }
}
