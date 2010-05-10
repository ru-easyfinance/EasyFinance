<?php

/**
 * Счет
 */
class Account extends BaseAccount
{
    /**
     * Типы счетов, см. `account_types`
     */
    const TYPE_CASH       = 1;  // Наличные
    const TYPE_DEBIT_CARD = 2;  // Дебетовая карта
}
