<?php

/**
 * Accounts: Добавляем колонки со статистикой
 */
class Migration010_Accounts_AddColumns extends Doctrine_Migration_Base
{
    /**
     * Migrate
     */
    function migrate($upDown)
    {
        $this->column($upDown, 'accounts', 'created_at', 'timestamp', 25, array('notnull' => true));
        $this->column($upDown, 'accounts', 'updated_at', 'timestamp', 25, array('notnull' => true));
        $this->column($upDown, 'accounts', 'deleted_at', 'timestamp', 25);
    }


    /**
     * PostUp
     */
    public function postUp()
    {
        Doctrine_Manager::getInstance()->getConnection('doctrine')->getDbh()->query("
            UPDATE accounts SET created_at = NOW(), updated_at = NOW()
        ");
    }
}
