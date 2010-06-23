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

        if (empty($props['category_id'])) {
            $ob->setCategory($this->makeCategory($user));
        }

        if ($save) {
            $ob->save();
        }
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

}
