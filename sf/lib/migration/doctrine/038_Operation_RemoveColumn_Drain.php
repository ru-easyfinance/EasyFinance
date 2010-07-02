<?php

/**
 * Operation: Удаляем поле drain
 */
class Migration038_Operation_RemoveColumn_Drain extends myBaseMigration
{
    /**
     * Up
     */
    public function up()
    {
        $this->removeColumn('operation', 'drain');
    }

    /**
     * Down
     */
    public function down()
    {
        $options = array(
            'unsigned' => true,
            'notnull'  => true,
            'default'  => 1
        );
        $this->addColumn('operation', 'drain', 'integer', 1, $options);
    }
}
