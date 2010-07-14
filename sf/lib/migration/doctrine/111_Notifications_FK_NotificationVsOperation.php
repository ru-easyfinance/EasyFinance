<?php

class Migration111_Notifications_FK_NotificationVsOperation extends Doctrine_Migration_Base
{
    function migrate($upDown)
    {

        $definition = array(
            'local'        => 'operation_id',
            'foreign'      => 'id',
            'foreignTable' => 'operation',
            'onDelete'     => 'CASCADE'
        );
        $this->foreignKey($upDown, 'operation_notifications', 'notification_vs_operation', $definition);
    }
}