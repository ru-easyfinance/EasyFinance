<?php

/**
 * Operation: FK на transfer_account_id
 */
class Migration037_Operation_FK_OperationVsAccountTransfer extends myBaseMigration
{
    /**
     * Зачищаем данные, перед установкой FK
     */
    function preUp()
    {
        $sql = "DELETE o FROM operation o
                LEFT JOIN accounts a ON (o.transfer_account_id=a.account_id)
                WHERE o.transfer_account_id IS NOT NULL AND a.account_id IS NULL AND o.`type`=2";
        $this->rawQuery($sql);

        $sql = "UPDATE operation o SET transfer_account_id = NULL WHERE transfer_account_id = 0";
        $this->rawQuery($sql);
    }


    /**
     * Migrate
     */
    function migrate($upDown)
    {
        $definition = array(
            'local'        => 'transfer_account_id',
            'foreign'      => 'account_id',
            'foreignTable' => 'accounts',
            'onDelete'     => NULL
        );
        $this->foreignKey($upDown, 'operation', 'operation_vs_transfer', $definition);
    }

}
