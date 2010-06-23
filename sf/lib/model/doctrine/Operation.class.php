<?php

/**
 * Операция
 */
class Operation extends BaseOperation
{
    /**
     * Типы операций
     */
    const TYPE_EXPENSE  = 0; // Расход
    const TYPE_PROFIT   = 1; // Доход
    const TYPE_TRANSFER = 2; // Перевод
    const TYPE_BALANCE  = 3; // Начальный баланс

    /**
     * Статус
     */
    const STATUS_ACCEPTED = 1; // Операция подтверждена
    const STATUS_DRAFT    = 0; // Черновик

    /**
     * Источник (кто создал операцию)
     */
    const SOURCE_AMT = 'amt';


    /**
     * Установить счет и инициализировать user_id
     *
     * @param  Account $account
     * @return void
     */
    public function setAccount(Account $account)
    {
        $this->_set('Account', $account);
        $this->_set('user_id', $account->getUserId());
    }


    /**
     * Вернуть типы операций
     *
     * @return array
     */
    static public function getTypes()
    {
        return array(
            self::TYPE_EXPENSE,
            self::TYPE_PROFIT,
            self::TYPE_TRANSFER,
            self::TYPE_BALANCE,
        );
    }

}
