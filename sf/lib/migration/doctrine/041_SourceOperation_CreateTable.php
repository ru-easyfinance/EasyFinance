<?php

/**
 * SourceOperation: создать таблицу
 */
class Migration041_SourceOperation_CreateTable extends Doctrine_Migration_Base
{
    /**
     * Migrate
     */
    public function migrate($upDown)
    {
        $this->table($upDown, 'source_operations',
            $columns = array(
                'operation_id' => array(
                    'type'     => 'integer',
                    'unsigned' => true,
                    'length'   => 8,
                    'primary'  => true,
                ),
                'source_uid' => array(
                    'type'    => 'string',
                    'length'  => 8,
                    'fixed'   => true,
                    'notnull' => true,
                ),
                'source_operation_uid' => array(
                    'type'    => 'string',
                    'length'  => 32,
                    'notnull' => true,
                ),
            ),
            $options = array(
                'indexes' => array(
                    'source_operation' => array(
                        'fields' => array('source_uid', 'source_operation_uid'),
                        'type'   => 'unique',
                    ),
                ),
                'type'    => 'INNODB',
                'charset' => 'utf8',
            )
        );


        if ('up' == $upDown) {
            $this->createForeignKey('source_operations', 'source_operation_VS_operation', array(
                 'local'        => 'operation_id',
                 'foreign'      => 'id',
                 'foreignTable' => 'operation',
                 'onDelete'     => 'CASCADE',
            ));
        }

    }
}
