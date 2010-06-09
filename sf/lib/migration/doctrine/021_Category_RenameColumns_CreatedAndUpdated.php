<?php

/**
 * Категории: Переименовываем колонки с Timestampable
 */
class Migration021_Category_RenameColumns_CreatedAndUpdated extends Doctrine_Migration_Base
{
    /**
     * Up
     */
    function up()
    {
        $this->renameColumn('category', 'dt_create', 'created_at');
        $this->renameColumn('category', 'dt_update', 'updated_at');
    }


    /**
     * Down
     */
    function down()
    {
        $this->renameColumn('category', 'created_at', 'dt_create');
        $this->renameColumn('category', 'updated_at', 'dt_update');
    }

}
