<?php

/**
 * Accounts: FK к currency
 */
class Migration014_Accounts_FK_AccountVsAccountTypes extends myBaseMigration
{
    /**
     * Добавить недостающий тип счета
     */
    public function preUp()
    {
        $this->rawQuery("
            INSERT INTO `account_types` VALUES (16,'Банковский счёт')
        ");
    }


    /**
     * Migrate
     */
    function migrate($upDown)
    {
        $definition = array(
            'local'        => 'account_type_id',
            'foreign'      => 'account_type_id',
            'foreignTable' => 'account_types',
        );
        $this->foreignKey($upDown, 'accounts', 'account_vs_type', $definition);
    }


    /**
     * Откатить фикстуру
     */
    public function postDown()
    {
        $this->rawQuery("
            DELETE FROM `account_types` WHERE account_type_id =16
        ");
    }
}
