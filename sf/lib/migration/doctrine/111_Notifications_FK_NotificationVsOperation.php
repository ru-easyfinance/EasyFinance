<?php

/**
 * OperationNotification: FK на operation
 */
class Migration111_Notifications_FK_NotificationVsOperation extends Doctrine_Migration_Base
{
    /**
     * Migrate
     */
    function migrate($upDown)
    {
        $definition = array(
            'local'        => 'operation_id',
            'foreign'      => 'id',
            'foreignTable' => 'operation',
            'onDelete'     => 'CASCADE',
        );

        $this->foreignKey($upDown, 'operation_notifications', 'notification_vs_operation', $definition);
    }

}
