<?php

/**
 * Accounts: Добавляем новое поле состояния счёта, для архивных и избранных счетов
 */
class Migration046_Add_AccountField extends myBaseMigration
{
    public function migrate()
    {
        $options = array(
            'unsigned' => 'false',
            'notnull'  => 'true',
            'default'  => 0,
            'after'    => 'user_id',
        );
        $this->addColumn('accounts', 'account_state', 'integer', 1, $options);
    }
}
