<?php

/**
 * Accounts: FK к currency
 */
class Migration013_Accounts_FK_AccountVsCurrency extends Doctrine_Migration_Base
{
    /**
     * Удалить потерянные объекты
     */
    public function preUp()
    {
        Doctrine_Manager::getInstance()->getConnection('doctrine')->getDbh()->query("
            DELETE a FROM accounts a
            LEFT JOIN currency c ON (a.account_currency_id=c.cur_id)
            WHERE a.account_id IS NOT NULL AND c.cur_id IS NULL
        ");
    }


    /**
     * Migrate
     */
    function migrate($upDown)
    {
        $definition = array(
            'local'        => 'account_currency_id',
            'foreign'      => 'cur_id',
            'foreignTable' => 'currency',
            'onDelete'     => null
        );
        $this->foreignKey($upDown, 'accounts', 'account_vs_currency', $definition);
    }
}
