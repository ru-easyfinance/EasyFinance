<?php

/**
 * Operation: Обновляем колонки
 */
class Migration016_Operation_UpdateColumns_AccountAndCategory extends myBaseMigration
{
    /**
     * Up
     */
    public function up()
    {
        // account_id
        $options = array(
            'notnull'  => false,
            'default'  => null,
            'unsigned' => false,
        );
        $this->changeColumn('operation', 'account_id', 'integer', 4, $options);

        // cat_id
        $options = array(
            'notnull'  => false,
            'default'  => null,
            'unsigned' => false,
        );
        $this->changeColumn('operation', 'cat_id', 'integer', 4, $options);
    }


    /**
     * Обновить значения
     * После обновления все 0 так и остались 0, а нам нужны NULL
     */
    public function postUp()
    {
        // Заполняем NULL, где все account_id = 0
        $sql = "UPDATE operation SET account_id = NULL WHERE account_id=0;";
        $this->rawQuery($sql);

        // Заполняем NULL, где все cat_id = 0
        $sql = "UPDATE operation SET cat_id = NULL WHERE cat_id=0;";
        $this->rawQuery($sql);
    }


    /**
     * Down
     */
    function down()
    {
        // account_id
        $options = array(
            'notnull'  => true,
            'default'  => 0,
            );
        $this->changeColumn('operation', 'account_id', 'integer', 8, $options);

        // cat_id
        $options = array(
            'notnull'  => true,
            'default'  => 0,
            );
        $this->changeColumn('operation', 'cat_id', 'integer', 8, $options);
    }

}
