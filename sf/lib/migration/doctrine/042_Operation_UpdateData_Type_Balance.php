<?php

/**
 * Operation: Обновляем поле type
 * Обратного хода нет
 */
class Migration042_Operation_UpdateData_Type_Balance extends myBaseMigration
{
    /**
     * Up
     */
    function up()
    {
        $query = "
            UPDATE operation o SET
                o.date = '0000-00-00',
                o.type = " . Operation::TYPE_BALANCE . ",
                o.cat_id = NULL,
                o.source_id = NULL,
                o.chain_id = 0,
                o.accepted = 1,
                o.transfer_account_id = NULL,
                o.transfer_amount = NULL,
                o.tags = NULL
            WHERE
                    o.comment LIKE BINARY 'Начальный остаток'
                AND o.`cat_id` IS NOT NULL
        ";

        $this->rawQuery($query);
    }

}
