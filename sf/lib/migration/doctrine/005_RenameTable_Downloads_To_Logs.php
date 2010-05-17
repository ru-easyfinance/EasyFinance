<?php

/**
 * Downloads: Переименовываем таблицу
 */
class Migration005_RenameTable_Downloads_To_Logs extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->renameTable('downloads', 'logs');
    }

    public function down()
    {
        $this->renameTable('logs', 'downloads');
    }

}
