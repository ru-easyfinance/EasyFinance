<?php

/**
 * Operation: Удаляем операции, счета которых уже удалены
 */
class Migration043_Operation_UpdateData_SetDeleted extends myBaseMigration
{
    /**
     * Up
     */
    function up()
    {
        $this->rawQuery("
            UPDATE operation o, accounts a
            SET o.deleted_at = a.deleted_at,
                o.updated_at = a.deleted_at
            WHERE o.account_id=a.account_id
                AND a.deleted_at IS NOT NULL AND o.deleted_at IS NULL;

            UPDATE operation o, accounts a
            SET o.deleted_at = a.deleted_at,
                o.updated_at = a.deleted_at
            WHERE o.transfer_account_id=a.account_id
                AND a.deleted_at IS NOT NULL AND o.deleted_at IS NULL
        ");
    }

}
