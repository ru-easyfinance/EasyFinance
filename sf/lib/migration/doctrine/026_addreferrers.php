<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Addreferrers extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->createTable('referrers', array(
             'id' => 
             array(
              'type' => 'integer',
              'fixed' => 0,
              'unsigned' => true,
              'primary' => true,
              'autoincrement' => true,
              'length' => 4,
             ),
             'host' => 
             array(
              'type' => 'string',
              'fixed' => 0,
              'unsigned' => false,
              'primary' => false,
              'notnull' => true,
              'autoincrement' => false,
              'length' => 128,
             ),
             'title' => 
             array(
              'type' => 'string',
              'fixed' => 0,
              'unsigned' => false,
              'primary' => false,
              'notnull' => false,
              'autoincrement' => false,
              'length' => 128,
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
        $this->dropTable('referrers');
    }
}