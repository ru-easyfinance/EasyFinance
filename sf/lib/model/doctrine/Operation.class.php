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


    public function preSave($event)
    {
        if ($this->getType() == self::TYPE_BALANCE) {
            $balanceOperation = $this->getTable()->findByDql(
                'account_id = ? AND type = ?',
                array($this->getAccountId(), self::TYPE_BALANCE),
                Doctrine::HYDRATE_ARRAY
            );

            if (isset($balanceOperation[0])) {
                $amount = $this->getAmount();
                $this->assignIdentifier($balanceOperation[0]['id']);
                $this->fromArray($balanceOperation[0]);
                $this->load();
                $this->setAmount($amount);
            }
        }

        if (!$this->getAccountId()) {
            $this->setAccepted(0);
        }
    }

    /**
     * Хук для починки операций перевода
     * @param Doctrine_Event $event
     * @see vendor/doctrine/Doctrine/Doctrine_Record::preHydrate()
     */
    public function preHydrate($event)
    {
        $data = $event->data;

        if (
            isset($data['type'])
            && $data['type'] == self::TYPE_TRANSFER
            && $data['transfer_amount'] == 0
            && isset($data['amount'])
            && isset($data['account_id'])
            && isset($data['transfer_account_id'])
        ) {
            $account = Doctrine::getTable('Account')
                ->findOneById($data['account_id']);
            $transferAccount = Doctrine::getTable('Account')
                ->findOneById($data['transfer_account_id']);

            $rate = sfContext::getInstance()->getMyCurrencyExchange()
                ->getRate(
                    $account->getCurrencyId(),
                    $transferAccount->getCurrencyId()
                );

            $data['transfer_amount'] = ($rate ? $rate : 1) * abs($data['amount']);
        }

        $event->data = $data;
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
