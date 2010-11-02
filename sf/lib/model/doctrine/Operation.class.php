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
     * Конвертирует сумму перевода в нужную валюту автоматом
     */
    protected function convertAmounts($data)
    {
        $table = Doctrine::getTable('Account');
        $account         = $table->findOneById($data['account_id']);
        $transferAccount = $table->findOneById($data['transfer_account_id']);

        $rate = sfContext::getInstance()->getMyCurrencyExchange()
            ->getRate(
                $account->getCurrencyId(),
                $transferAccount->getCurrencyId()
            );

        $data['transfer_amount'] = ($rate ? $rate : 1) * abs($data['amount']);

        return $data;
    }


    public function preSave($event)
    {
        switch ($this->getType()) {
            case self::TYPE_BALANCE:
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
            break;
        }

        if (!$this->getAccountId()) {
            $this->setAccepted(0);
        }

        // надоело уже руками править по формам, у трансферной операции
        // сумма из счета - отрицательна, на счет - положительна
        // TODO: есть исключения
        if ($this->getType() == self::TYPE_TRANSFER) {
            $this->amount = abs($this->getAmount()) * -1;
            if (!$this->transfer_amount) {
                $this->convertAmounts($this);
            }
        }

        if ($this->getType() == self::TYPE_PROFIT) {
            $this['amount'] = abs($this['amount']);
        }

        if ($this->getType() == self::TYPE_EXPENSE) {
            $this['amount'] = abs($this['amount']) * -1;
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
            && ($data['type'] == self::TYPE_TRANSFER)
            && ($data['transfer_amount'] == 0)
            && isset($data['amount'])
            && (abs($data['amount']) != 0)
            && isset($data['account_id'])
            && isset($data['transfer_account_id'])
        ) {
            $data['amount'] = abs($data['amount']) * -1;
            $data = $this->convertAmounts($data);
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


    /**
     * Возвращает сумму в указанной валюте
     * @param Currency $currency валюта в которой вернуть сумму
     * @param bool $signed
     * @return float
     */
    public function getAmountForBudget(Currency $currency, $signed)
    {
        if ($this->getType() == self::TYPE_BALANCE) {
            throw new InvalidOperationTypeException(
                'В бюджете не должно быть балансовых операций'
            );
        }

        if (
            $this->getTransferAccount()
            && $this->getTransferAccount()->getCurrency() == $currency
        ) {
            $amount = $this->getTransferAmount();
        } else {
            $amount = $currency->convert(
                $this->getAmount(),
                $this->getAccount()->getCurrency()
            );
        }

        // Операции без категорий в бюджете не нужны
        // Это обычные внутренние переводы
        $sign = $this->getCategory() ?
            (Category::TYPE_PROFIT == $this->getCategory()->getType() ? 1 : -1)
            : 0 ;

        return $signed ? $sign * abs($amount) : abs($amount);
    }


    /**
     * Возвращает категорию
     * Применяется в бюджете
     */
    public function getCategory()
    {
        if (
            $this->getTransferAccount()->isDebt()
            &&
            !$this->getAccount()->isDebt()
        ) {
            return Category::getDebtCategoryInstance();
        } else {
            return parent::getCategory();
        }
    }


    public function isFromCalendar()
    {
        return (bool) $this->getChainId();
    }


    public function isAccepted()
    {
        return (bool) $this->getAccepted();
    }
}
