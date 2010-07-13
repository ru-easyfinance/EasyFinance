<?php

/**
 * Operation: Переименовываем поле imp_id в transfer_amount и поле transfer в transfer_account_id
 */
class Migration034_Operation_RenameColumns extends Doctrine_Migration_Base
{
    /**
     * Up
     */
    public function up()
    {
        $this->renameColumn('operation', 'imp_id', 'transfer_amount');
        $this->renameColumn('operation', 'transfer', 'transfer_account_id');
    }

    /**
     * Down
     */
    public function down()
    {
        $this->renameColumn('operation', 'transfer_amount', 'imp_id');
        $this->renameColumn('operation', 'transfer_account_id', 'transfer');
    }
}
