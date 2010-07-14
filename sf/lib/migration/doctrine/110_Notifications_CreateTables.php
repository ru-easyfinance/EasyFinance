<?php

class Migration110_Notifications_CreateTables extends Doctrine_Migration_Base
{
    function migrate($upDown)
    {

        $fieldsNotifications =
        array(
            'id' => array(
                'type' => 'integer',
                'length' => 4,
                'unsigned' => 1,
                'notnull' => 1,
                'autoincrement' => true,
                'primary' => true,
            ),
            'operation_id' => array(
                'type' => 'integer',
                'length' => 20,
                'unsigned' => 1,
                'notnull' => 1,
            ),
            'date_time' => array(
                'type' => 'datetime',
            ),
            'type' => array(
                'type' => 'integer',
                'length' => 1,
                'unsigned' => 1,
                'notnull' => 1,
                'default' => 1,
            ),
            'is_sent' => array(
                'type' => 'integer',
                'length' => 1,
                'unsigned' => 1,
                'notnull' => 1,
                'default' => 0,
            ),
            'fail_counter' => array(
                'type' => 'integer',
                'length' => 2,
                'unsigned' => 1,
                'notnull' => 1,
                'default' => 0,
            ),
            'is_done' => array(
                'type' => 'integer',
                'length' => 1,
                'unsigned' => 1,
                'notnull' => 1,
                'default' => 0,
            ),
            #Max: created_at, см. Doctrine Timestampable + указать в схеме
            'dt_create' => array(
                'type' => 'datetime'
            ),
            'dt_update' => array(
                'type' => 'datetime'
            )
        );

        $options = array(
            'type'     => 'INNODB',
            'charset' => 'utf8'
        );

        $this->table($upDown, 'operation_notifications', $fieldsNotifications, $options);
    }
}