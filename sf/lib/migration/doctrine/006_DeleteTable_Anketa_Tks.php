<?php

/**
 * Downloads: Добавляем новую колонку `info` text
 */
class Migration006_DeleteTable_Anketa_Tks extends Doctrine_Migration_Base
{
    /**
     * Удаляем таблицу
     */
    function up()
    {
        $this->dropTable('anketa_tks');
    }

    /**
     * Создаём таблицу
     */
    function down()
    {
        $fields = array(
            'id' => array(
                'type' => 'integer',
                'length' => 4,
                'unsigned' => 1,
                'notnull' => 1,
                'autoincrement' => true,
                'primary' => true,
            ),
            'user_id' => array(
                'type' => 'integer',
                'length' => 4,
            ),
            'name' => array(
                'type' => 'string',
                'length' => 255,
            ),
            'surname' => array(
                'type' => 'string',
                'length' => 255,
            ),
            'patronymic' => array(
                'type' => 'string',
                'length' => 255,
            ),
            'phone' => array(
                'type' => 'string',
                'length' => 50,
            ),
            'created_at' => array(
                'type' => 'timestamp'
            ),
        );

        $options = array(
            'type'     => 'INNODB',
            'charset' => 'utf8'
        );

        $this->createTable('anketa_tks', $fields, $options);
    }
}
