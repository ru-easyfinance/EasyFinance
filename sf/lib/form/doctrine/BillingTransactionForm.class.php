<?php

/**
 * Транзакции биллинга
 *
 */
class BillingTransactionForm extends BaseBillingTransactionForm
{
    /**
     * Конфигурация
     *
     */
    public function configure()
    {
        // Убираем поля, которые не должны участвовать в форме
        unset($this['created_at'], $this['updated_at']);
    }
}
