<?php

/**
 * Создать таблицу уведомлений
 */
class Migration110_Notifications_CreateTables extends Doctrine_Migration_Base
{
    /**
     * Migrate
     */
    function migrate($upDown)
    {
        $fields = array(
            'id' => array(
                'type'     => 'integer',
                'length'   => 4,
                'unsigned' => true,
                'notnull'  => true,
                'primary'  => true,
                'autoincrement' => true,
            ),
            'operation_id' => array(
                'type'     => 'integer',
                'length'   => 8,
                'unsigned' => true,
                'notnull'  => true,
            ),
            'schedule' => array(
                'type'     => 'timestamp',
                'length'   => '25',
                'notnull'  => true,
            ),
            'type' => array(
                'type'     => 'integer',
                'length'   => 1,
                'unsigned' => true,
                'notnull'  => true,
                'default'  => '1',
            ),
            'fail_counter' => array(
                'type'     => 'integer',
                'length'   => 1,
                'unsigned' => true,
                'notnull'  => true,
                'default'  => '0',
            ),
            'is_sent' => array(
                'type'     => 'boolean',
                'unsigned' => true,
                'notnull'  => true,
                'default'  => '0',
            ),
            'is_done' => array(
                'type'     => 'boolean',
                'unsigned' => true,
                'notnull'  => true,
                'default'  => '0',
            ),
            'created_at' => array(
                'type'     => 'timestamp',
                'length'   => 25,
                'notnull'  => true,
            ),
            'updated_at' => array(
                'type'     => 'timestamp',
                'length'   => 25,
                'notnull'  => true,
            ),
        );

        $options = array(
            'type'    => 'INNODB',
            'charset' => 'utf8'
        );

        $this->table($upDown, 'operation_notifications', $fields, $options);
    }

}
