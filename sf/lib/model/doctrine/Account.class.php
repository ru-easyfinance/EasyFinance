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

    /**
     * Состояние счёта - обычный
     */
    const STATE_NORMAL    = 0;

    /**
     * Состояние счёта - избранный
     */
    const STATE_FAVORITE  = 1;

    /**
     * Состояние счёта - в архиве
     */
    const STATE_ARCHIVE   = 2;


    /**
     * Балансовая операцияs
     * @var Operation
     */
    private $balanceOperation;

    /**
     * Возвращает операцию начального остатка
     * @return Operation
     */
    private function getBalanceOperation()
    {
        if (is_null($this->balanceOperation)) {
            $params = array($this->getId(), Operation::TYPE_BALANCE);
            $this->balanceOperation = Doctrine_Query::create()
                    ->from('Operation o')
                    ->andWhere("account_id = ? AND type = ?", $params)
                    ->fetchOne();

            if (!$this->balanceOperation) {
                $params = array(
                    'user_id'     => $this->getUserId(),
                    'amount'      => 0,
                    'date'        => '0000-00-00',
                    'category_id' => NULL,
                    'type'        => Operation::TYPE_BALANCE,
                    'comment'     => 'Начальный остаток',
                    'accepted'    => 1,
                );

                $this->balanceOperation = new Operation;
                $this->balanceOperation->fromArray($params);
            }

            $this->balanceOperation->setAccount($this);
        }

        return $this->balanceOperation;
    }

    /**
     * Возвращает начальный баланс
     * @return float начальный баланс
     */
    public function getInitBalance()
    {
        return $this->getBalanceOperation()->getAmount();
    }

    /**
     * Устанавливает начальный баланс счёта
     * @param int $balance начальный баланс
     */
    public function setInitBalance($balance)
    {
        $balance = (float) $balance;
        $this->getBalanceOperation()->setAmount($balance);
    }

    /**
     * Сразу поле сохранения счёта надо сохранить его балансовую операцию
     * @see vendor/doctrine/Doctrine/Doctrine_Record::postSave()
     */
    public function postSave($event) {
        $this->getBalanceOperation()->setAccountId($this->getId())->save();
    }


    /**
     * ToString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

}
