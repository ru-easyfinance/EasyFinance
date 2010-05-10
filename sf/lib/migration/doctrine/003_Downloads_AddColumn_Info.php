<?php

/**
 * Downloads: Добавляем новую колонку `info` text
 */
class Migration003_Downloads_AddColumn_Info extends Doctrine_Migration_Base
{
    /**
     * Migrate
     */
    public  function migrate($upDown)
    {
        // clob (65532) - это поле MYSQL TEXT
        $this->column($upDown, 'downloads', 'info', 'clob', 65532);
    }
}
