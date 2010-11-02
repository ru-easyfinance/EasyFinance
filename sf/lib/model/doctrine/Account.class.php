<?php

/**
 * Счет
 */
class Account extends BaseAccount
{
    /**
     * Типы счетов, см. `account_types`
     */
    const TYPE_CASH         = 1;  // Наличные
    const TYPE_DEBIT_CARD   = 2;  // Дебетовая карта

    const TYPE_DEPOSIT      = 5;
    const TYPE_LOAN_GIVE    = 6;
    const TYPE_LOAN_GET     = 7;
    const TYPE_CREDIT_CARD  = 8;
    const TYPE_CREDIT       = 9;
    const TYPE_METALLIC     = 10;
    const TYPE_SHARE        = 11;
    const TYPE_PIF          = 12;
    const TYPE_OFBU         = 13;
    const TYPE_PROPERTY     = 14;
    const TYPE_ELECT_PURSE  = 15;
    const TYPE_BANK_ACC     = 16;

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
     * Удаляет операции по удалённому счёту
     * Почему-то не работает onDelete CASCADE для softDelete
     * @see vendor/doctrine/Doctrine/Doctrine_Record::postDelete()
     */
    public function postDelete($event) {
        $query = Doctrine_Query::create()
            ->update('Operation o')
            ->set('o.deleted_at', '?', array(date('Y-m-d H:i:s')))
            ->where('o.account_id = ?', $this->getId())
            ->execute();
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


    public function isDebt()
    {
        return in_array(
            $this->getTypeId(),
            array(
                Account::TYPE_CREDIT,
                Account::TYPE_CREDIT_CARD,
                Account::TYPE_LOAN_GET,
            )
        );
    }
}
