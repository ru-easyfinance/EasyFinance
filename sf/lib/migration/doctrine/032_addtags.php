<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Addtags extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->createTable('tags', array(
             'id' => 
             array(
              'type' => 'integer',
              'autoincrement' => true,
              'primary' => true,
              'length' => 8,
             ),
             'user_id' => 
             array(
              'type' => 'integer',
              'fixed' => 0,
              'unsigned' => true,
              'primary' => false,
              'notnull' => true,
              'autoincrement' => false,
              'length' => 4,
             ),
             'oper_id' => 
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
              'length' => 50,
             ),
             ), array(
             'indexes' => 
             array(
             ),
             'primary' => 
             array(
              0 => 'id',
             ),
             'collate' => 'utf8_general_ci',
             'charset' => 'utf8',
             ));
    }

    public function down()
    {
        $this->dropTable('tags');
    }
}