<?php

/**
 * Accounts: FK к user
 */
class Migration012_Accounts_FK_AccountVsUser extends Doctrine_Migration_Base
{
    /**
     * Удалить потерянные объекты
     */
    public function preUp()
    {
        Doctrine_Manager::getInstance()->getConnection('doctrine')->getDbh()->query("
            DELETE a FROM accounts a
            LEFT JOIN users u ON (a.user_id=u.id)
            WHERE a.account_id IS NOT NULL AND u.id IS NULL
        ");
    }


    /**
     * Migrate
     */
    function migrate($upDown)
    {
        $definition = array(
            'local'        => 'user_id',
            'foreign'      => 'id',
            'foreignTable' => 'users',
        );
        $this->foreignKey($upDown, 'accounts', 'account_vs_user', $definition);
    }

}
