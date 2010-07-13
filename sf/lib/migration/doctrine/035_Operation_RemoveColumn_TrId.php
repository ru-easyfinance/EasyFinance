<?php

/**
 * Operation: Удаляем поле tr_id
 */
class Migration035_Operation_RemoveColumn_TrId extends myBaseMigration
{
    /**
     * Up
     */
    public function up()
    {
        $this->removeColumn('operation', 'tr_id');
    }

    /**
     * Down
     */
    public function down()
    {
        $options = array(
            'unsigned' => true,
            'notnull'  => false,
            'default'  => null
        );
        $this->addColumn('operation', 'tr_id', 'integer', 8, $options);

        // BC, см M-032
        $definition = array(
            'fields'=>array('tr_id')
        );
        $this->addIndex('operation', 'tr_id', $definition);
    }
}
