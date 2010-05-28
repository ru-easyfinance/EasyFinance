<?php

/**
 * Базовый класс для всех миграций
 */
class myBaseMigration extends Doctrine_Migration_Base
{
    /**
     * Выполнить raw sql
     */
    public function rawQuery($sql)
    {
        return Doctrine_Manager::getInstance()->getConnection('doctrine')->getDbh()->query($sql);
    }
}
