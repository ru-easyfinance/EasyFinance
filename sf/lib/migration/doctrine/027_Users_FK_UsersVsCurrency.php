<?php

/**
 * User: FK на currency
 */
class Migration027_Users_FK_UsersVsCurrency extends myBaseMigration
{
    /**
     * Migrate
     */
    function migrate($upDown)
    {
        $definition = array(
            'local'        => 'user_currency_default',
            'foreign'      => 'cur_id',
            'foreignTable' => 'currency',
            'onDelete'     => NULL
        );
        $this->foreignKey($upDown, 'users', 'user_vs_currency', $definition);
    }
}
