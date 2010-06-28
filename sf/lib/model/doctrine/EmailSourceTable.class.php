<?php


class EmailSourceTable extends Doctrine_Table
{

    public static function getInstance()
    {
        return Doctrine_Core::getTable('EmailSource');
    }

    public function getByEmail( $from )
    {
        $from = trim( $from );
        return $this->findByDql("email_list LIKE ?", array( "%$from%"))->getFirst();
    }
}