<?php

/**
 * myTestObjectHelper
 */
class myTestObjectHelper extends sfPHPUnitObjectHelper
{
    /**
     * Создать пользователя
     */
    public function makeUser(array $props = array(), $save = true)
    {
        $defaultProps = array(
            'user_service_mail' => sprintf('user%d@mail.easyfinance.ru', $this->getUniqueCounter()),
        );
        $props = array_merge($defaultProps, $props);

        $ob = $this->makeModel('User', $props);

        return $ob;
    }


    /**
     * Создать счет
     */
    public function makeAccount(User $user = null, array $props = array(), $save = true)
    {
        $defaultProps = array(
            'name'        => $this->makeText('Название счета'),
            'type_id'     => Account::TYPE_CASH,
            'currency_id' => 1,
            'description' => $this->makeText('Описание счета'),
        );
        $props = array_merge($defaultProps, $props);

        if (!$user) {
            $user = $this->makeUser(array(), $save);
        }

        // Properties
        if (isset($props['props'])) {
            $props['Properties'] = array();
            foreach ($props['props'] as $item) {
                $props['Properties'][] = array(
                    'field_id'    => $item[0],
                    'field_value' => $item[1],
                );
            }
        }

        $ob = $this->makeModel('Account', $props, false);
        $ob->setUser($user);

        if ($save) {
            $ob->save();
        }
        return $ob;
    }


    /**
     * Создать коллекцию счетов
     */
    public function makeAccountCollection($count, User $user = null, array $props = array(), $save = true)
    {
        $coll = Doctrine_Collection::create('Account');

        if (!$user) {
            $user = $this->makeUser(array(), $save);
        }

        for ($i=0; $i<(int)$count; $i++) {
            if (isset($props[$i])) {
                $itemProps = $props[$i];
            } else {
                $itemProps = array();
            }
            $coll->add($this->makeAccount($user, $itemProps, $save));
        }

        return $coll;
    }


    /**
     * Создать операцию
     */
    public function makeOperation(Account $account = null, array $props = array(), $save = true)
    {
        $defaultProps = array(
            'amount'   => -$this->getUniqueCounter() - 0.99,
            'date'     => date('Y-m-d', time()-86400),
            'type'     => Operation::TYPE_EXPENSE,
            'comment'  => $this->makeText('Комментарий к операции'),
            'accepted' => Operation::STATUS_ACCEPTED,
        );
        $props = array_merge($defaultProps, $props);

        if (!$account) {
            $account = $this->makeAccount(null, array(), $save);
        }
        $user = $account->getUser();

        $ob = $this->makeModel('Operation', $props, false);
        $ob->setAccount($account);
        $ob->setUser($account->getUser());

        if (!array_key_exists('category_id', $props) OR !is_null($props['category_id'])) {
            $ob->setCategory($this->makeCategory($user));
        }

        if ($save) {
            $ob->save();
        }
        return $ob;
    }


    /**
     * Создать коллекцию операций
     */
    public function makeOperationCollection($count, Account $account = null, array $props = array(), $save = true)
    {
        $coll = Doctrine_Collection::create('Operation');

        for ($i=0; $i<(int)$count; $i++) {
            if (isset($props[$i])) {
                $itemProps = $props[$i];
            } else {
                $itemProps = array();
            }
            $coll->add($this->makeOperation($account, $itemProps, $save));
        }

        return $coll;
    }


    /**
     * Создать балансовую операцию (начальный баланс)
     * @see Operation
     */
    public function makeBalanceOperation(Account $account = null, $amount = null, $save = true)
    {
        $props = array(
            'amount'      => -$this->getUniqueCounter() - 0.99,
            'date'        => '0000-00-00',
            'type'        => Operation::TYPE_BALANCE,
            'comment'     => '',
            'accepted'    => Operation::STATUS_ACCEPTED,
            'category_id' => null,
        );

        if ($amount) {
            $props['amount'] = (float) $amount;
        }

        $props['drain'] = ($props['amount'] > 0) ? 1 : 0;

        if (!$account) {
            $account = $this->makeAccount(null, array(), $save);
        } else {
            $ob = Doctrine_Query::create()
                ->from('Operation o')
                ->andWhere('o.account_id = ?', $account->getId())
                ->fetchOne();

            if ($ob) {
                $ob->fromArray($props);

                if ($save) {
                    $ob->save();
                }
                return $ob;
            }
        }

        $ob = $this->makeOperation($account, $props, $save);

        return $ob;
    }


    /**
     * Создать категорию
     */
    public function makeCategory(User $user = null, array $props = array(), $save = true)
    {
        $defaultProps = array(
            'parent_id' => 0,
            'system_id' => 1,
            'name'      => $this->makeText('Название категории'),
            'type'      => 1,
        );
        $props = array_merge($defaultProps, $props);

        if (!$user) {
            $user = $this->makeUser(array(), $save);
        }

        $ob = $this->makeModel('Category', $props, false);
        $ob->setUser($user);

        if ($save) {
            $ob->save();
        }
        return $ob;
    }


    /**
     * Создать категорию бюджета
     */
    public function makeBudgetCategory(User $user = null, array $props = array(), $save = true)
    {
        $defaultProps = array(
            'key'           => $this->getUniqueCounter(),
            'category_id'   => $this->getUniqueCounter(),
            'drain'         => 1, // Расход
            'amount'        => rand(1000, 10000) + 0.05,
            'date_start'    => date('Y-m-01'), // Текущий месяц
        );
        $props = array_merge($defaultProps, $props);

        if (!$user) {
            $user = $this->makeUser(array(), $save);
        }

        $ob = $this->makeModel('BudgetCategory', $props, false);
        $ob->setUser($user);

        if ($save) {
            $ob->save();
        }
        return $ob;
    }


    /**
     * Создать финансовую цель
     */
    public function makeTarget(Account $account = null, array $props = array(), $save = true)
    {
        $defaultProps = array(
            'title'         => $this->makeText(sprintf('Название цели %d', $this->getUniqueCounter())),
            'type'          => 'r',
            'amount'        => rand(10000, 100000) + 0.05,
            'date_begin'    => date('Y-m-01'), // Текущий месяц
            'date_end'      => date('Y-m-28', time() + 60*60*24*6), // 6 месяцев вперед
            'percent_done'  => 0,
            'forecast_done' => 0,
            'visible'       => false,
            'photo'         => '',
            'url'           => '',
            'comment'       => $this->makeText('Описание цели'),
            'amount_done'   => 0,
            'close'         => 0,
            'done'          => false,
        );
        $props = array_merge($defaultProps, $props);

        if (!$account) {
            $account = $this->makeAccount(null, array(), $save);
        }

        $user = $account->getUser();
        # Svel: что-то я перестал понимать, какие категории для чего :/
        $category = $this->makeCategory($user, array(), $save);

        $ob = $this->makeModel('Target', $props, false);
        $ob->setUser($user);
        $ob->setAccount($account);
        $ob->setCategory($category);

        if ($save) {
            $ob->save();
        }

        return $ob;
    }


    /**
     * Создать перевод на финансовую цель
     */
    public function makeTargetTransaction(Target $target = null, array $props = array(), $save = true)
    {
        $defaultProps = array(
            'amount'        => rand(10000, 100000) + 0.05,
            'comment'       => $this->makeText('Описание перевода на цель'),
        );
        $props = array_merge($defaultProps, $props);

        if (!$target) {
            $target = $this->makeTarget(null, array(), $save);
        }

        $user = $target->getUser();
        $account = $target->getAccount();

        $ob = $this->makeModel('TargetTransaction', $props, false);
        $ob->setTarget($target);
        $ob->setUser($user);
        $ob->setAccount($account);

        if ($save) {
            $ob->save();
        }

        return $ob;
    }

}
