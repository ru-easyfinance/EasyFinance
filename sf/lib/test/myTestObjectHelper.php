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
            'user_service_mail' => sprintf('user%d@example.org', $this->getUniqueCounter()),
        );
        $props = array_merge($defaultProps, $props);

        $ob = $this->makeModel('User', $props);
        return $ob;
    }


    /**
     * Создать операцию
     */
    public function makeOperation(User $user = null, array $props = array(), $save = true)
    {
        $defaultProps = array(
        );
        $props = array_merge($defaultProps, $props);

        if (!$user) {
            $user = $this->makeUser(array(), $save);
        }

        $ob = $this->makeModel('Operation', $props, false);
        $ob->setUser($user);

        if ($save) {
            $ob->save();
        }
        return $ob;
    }
}
