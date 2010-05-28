<?php

/**
 * Валюты: изменить колонку rate и фикстуры
 */
class Migration020_Currency_UpdateColumn_Rate extends Doctrine_Migration_Base
{
    /**
     * Up
     */
    public function up()
    {
        // account_id
        $options = array(
            'scale'    => 6,
            'notnull'  => true,
            'default'  => 1,
        );
        $this->changeColumn('currency', 'rate', 'decimal', 20, $options);
    }


    /**
     * Обновить фикстуры
     */
    public function postUp()
    {
        Doctrine_Manager::getInstance()->getConnection('doctrine')->getDbh()->query("
            UPDATE currency SET rate = 1 WHERE cur_id = 1;
            UPDATE currency SET created_at = NOW(), updated_at = NOW();
        ");
    }


    /**
     * Down
     */
    public function down()
    {
        // account_id
        $options = array(
            'scale'    => 6,
            'notnull'  => true,
            'default'  => 0,
        );
        $this->changeColumn('currency', 'rate', 'decimal', 20, $options);
    }

}
