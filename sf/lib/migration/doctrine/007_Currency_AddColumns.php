<?php

/**
 * Currency: Добавляем колонки со статистикой и курс для обмена к рублю
 */
class Migration007_Currency_AddColumns extends Doctrine_Migration_Base
{
    /**
     * Migrate
     */
    function migrate($upDown)
    {
        $this->column($upDown, 'currency', 'rate', 'decimal', 20, array(
            'scale' => 6,
            'unsigned' => true,
            'notnull' => true,
        ));
        $this->column($upDown, 'currency', 'created_at', 'timestamp', 25, array('notnull' => true));
        $this->column($upDown, 'currency', 'updated_at', 'timestamp', 25, array('notnull' => true));
    }
}
