<?php

/**
 * Operation: изменить тип `source_id` на string(8)
 */
class Migration038_Operation_UpdateColumn_Source extends Doctrine_Migration_Base
{
    /**
     * Up
     */
    public function up()
    {
        $this->changeColumn('operation', 'source_id', 'string', 8, array(
            'fixed'   => true,
            'notnull' => false,
        ));
    }


    /**
     * Down
     */
    public function down()
    {
        $this->changeColumn('operation', 'source_id', 'integer', 4, array(
            'unsigned' => true,
            'default' => 1,
            'notnull' => true,
        ));
    }

}
