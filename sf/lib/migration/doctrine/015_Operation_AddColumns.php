<?php

/**
 * Operation: Добавляем колонки со статистикой
 */
class Migration015_Operation_AddColumns extends Doctrine_Migration_Base
{
    /**
     * Up
     */
    function up()
    {
        $this->renameColumn('operation', 'dt_create', 'created_at');
        $this->renameColumn('operation', 'dt_update', 'updated_at');
        $this->addColumn('operation', 'deleted_at', 'timestamp');
    }


    /**
     * Down
     */
    function down()
    {
        $this->renameColumn('operation', 'created_at', 'dt_create');
        $this->renameColumn('operation', 'updated_at', 'dt_update');
        $this->removeColumn('operation', 'deleted_at');
    }

}
