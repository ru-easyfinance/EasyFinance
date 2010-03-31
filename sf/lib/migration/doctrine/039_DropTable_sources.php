<?php

/**
 * Удалить неиспользуемую таблицу `sources`
 */
class Migration039_DropTable_sources extends Doctrine_Migration_Base
{
    /**
     * Up
     */
    public function up()
    {
        $this->dropTable('sources');
    }


    /**
     * Down
     */
    public function down()
    {
        $this->createTable('sources', array(
             'id' =>
             array(
              'type' => 'integer',
              'fixed' => 0,
              'unsigned' => true,
              'primary' => false,
              'notnull' => true,
              'autoincrement' => false,
              'length' => 4,
             ),
             'name' =>
             array(
              'type' => 'string',
              'fixed' => 0,
              'unsigned' => false,
              'primary' => false,
              'notnull' => true,
              'autoincrement' => false,
              'length' => 255,
             ),
             'url' =>
             array(
              'type' => 'string',
              'fixed' => 0,
              'unsigned' => false,
              'primary' => false,
              'notnull' => true,
              'autoincrement' => false,
              'length' => NULL,
             ),
             'comment' =>
             array(
              'type' => 'string',
              'fixed' => 0,
              'unsigned' => false,
              'primary' => false,
              'notnull' => true,
              'autoincrement' => false,
              'length' => NULL,
             ),
             'image' =>
             array(
              'type' => 'string',
              'fixed' => 0,
              'unsigned' => false,
              'primary' => false,
              'notnull' => true,
              'autoincrement' => false,
              'length' => 255,
             ),
             ), array(
             'indexes' =>
             array(
             ),
             'primary' =>
             array(
             ),
             'collate' => 'utf8_general_ci',
             'charset' => 'utf8',
             ));
    }

}
