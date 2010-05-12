<?php

/**
 * Downloads: Добавляем новую колонку `info` text
 */
class Migration004_CreateTable_Anketa_Tks extends Doctrine_Migration_Base
{
    /**
       * Migrate
       */
    public  function migrate($upDown)
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
        );

        $options = array(
            'type'     => 'INNODB',
            'charset' => 'utf8'
        );

        $this->table($upDown, 'anketa_tks', $fields, $options);
    }
}
