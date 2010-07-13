<?php

/**
 * Operation: Обновляем поле transfer_account_id, приводим её в соответствии к accounts.account_id
 */
class Migration036_Operation_UpdateColumn_TransferAccountId extends myBaseMigration
{
    /**
     * Up
     */
    function up()
    {
        $options = array(
            'notnull'  => false,
            'default'  => null,
            'unsigned' => false,
        );
        $this->changeColumn('operation', 'transfer_account_id', 'integer', 4, $options);
    }

    /**
     * Down
     */
    function down()
    {
        $options = array(
            'notnull'  => false,
            'default'  => null,
            'unsigned' => false,
        );
        $this->changeColumn('operation', 'transfer_account_id', 'integer', 8, $options);
    }
}
