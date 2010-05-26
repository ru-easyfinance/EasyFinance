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
     * Создать операцию
     */
    public function makeOperation(Account $account = null, array $props = array(), $save = true)
    {
        $defaultProps = array(
        );
        $props = array_merge($defaultProps, $props);

        if (!$account) {
            $account = $this->makeAccount(null, array(), $save);
        }
        $user = $account->getUser();
        $cat  = $this->makeCategory($user);

        $ob = $this->makeModel('Operation', $props, false);
        $ob->setAccount($account);
        $ob->setUser($account->getUser());
        $ob->setCategory($cat);

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
