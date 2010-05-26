<?php

/**
 * Operation: Удалить каскадное удаление операций
 */
class Migration011_Operation_ReplaceFK_OperationVsUser extends Doctrine_Migration_Base
{
    /**
     * Up
     */
    function up()
    {
        $this->dropForeignKey('operation', 'operation_VS_user');

        $definition = array(
            'local'        => 'user_id',
            'foreign'      => 'id',
            'foreignTable' => 'users',
        );
        $this->createForeignKey('operation', 'operation_VS_user', $definition);
    }


    /**
     * Down
     */
    function down()
    {
        $this->dropForeignKey('operation', 'operation_VS_user');

        $definition = array(
            'local'        => 'user_id',
            'foreign'      => 'id',
            'foreignTable' => 'users',
            'onDelete'     => 'CASCADE'
        );
        $this->createForeignKey('operation', 'operation_VS_user', $definition);
    }

}
