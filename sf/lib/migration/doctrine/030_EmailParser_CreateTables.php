<?php

/**
 * Таблицы парсинга почтовых нотификаторов
 */
class Migration030_EmailParser_CreateTables extends Doctrine_Migration_Base
{
    /**
     * Migrate
     */
    public  function migrate($upDown)
    {
        $options = array(
            'type'     => 'INNODB',
            'charset' => 'utf8'
        );

    	// Отправители
        $fieldsEmailSource =
        array(
            'id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => 1,
                'autoincrement' => true,
                'primary' => true,
            ),
            'name' => array(
                'type' => 'string',
                'length' => 128,
            ),
            'email_list' => array(
                'type' => 'string',
                'length' => 255,
            ),
        );
        $this->table($upDown, 'email_sources', $fieldsEmailSource, $options);


        // Правила парсинга
        $fieldsEmailParsers =
        array(
            'id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => 1,
                'autoincrement' => true,
                'primary' => true,
            ),
            'email_source_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => 1,
            ),
            'name' => array(
                'type' => 'string',
                'length' => 128,
            ),
            'subject_regexp' => array(
                'type' => 'string',
                'length' => 255,
            ),
            'account_regexp' => array(
                'type' => 'string',
                'length' => 255,
            ),
            'total_regexp' => array(
                'type' => 'string',
                'length' => 255,
            ),
            'description_regexp' => array(
                'type' => 'string',
                'length' => 255,
            ),
            'sample' => array(
                'type' => 'text',
                'notnull' => 0,
            ),
            'type' => array(
                'type' => 'tinyint',
                'length' => 1,
            ),
        );
        $this->table($upDown, 'email_parsers', $fieldsEmailParsers, $options);


        // FK
        if ('up' == $upDown) {
            $definition = array(
                'local'        => 'email_source_id',
                'foreign'      => 'id',
                'foreignTable' => 'email_sources',
                'onDelete'     => 'CASCADE'
            );
            $this->foreignKey($upDown, 'email_parsers', 'email_source_FK', $definition);
        }
    }

}
