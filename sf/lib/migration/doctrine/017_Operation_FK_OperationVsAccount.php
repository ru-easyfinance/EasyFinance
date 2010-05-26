<?php

/**
 * Operation: Добавить FK на Account
 */
class Migration017_Operation_FK_OperationVsAccount extends Doctrine_Migration_Base
{
    /**
     * Удалить потерянные объекты
     */
    public function preUp()
    {
        Doctrine_Manager::getInstance()->getConnection('doctrine')->getDbh()->query("
            DELETE o FROM operation AS o
            LEFT JOIN accounts a ON o.account_id = a.account_id
            WHERE o.account_id IS NOT NULL
                AND a.account_id IS NULL
        ");
    }


    /**
     * Migrate
     */
    function migrate($upDown)
    {
        $definition = array(
            'local'        => 'account_id',
            'foreign'      => 'account_id',
            'foreignTable' => 'accounts',
            'onDelete'     => NULL
        );
        $this->foreignKey($upDown, 'operation', 'operation_vs_account', $definition);
    }

}
