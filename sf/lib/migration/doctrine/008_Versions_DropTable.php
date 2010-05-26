<?php

/**
 * Удалить таблицу `versions`
 */
class Migration008_Versions_DropTable extends myBaseMigration
{
    /**
     * Up
     */
    public function up()
    {
        $this->rawQuery("
            DROP TABLE IF EXISTS versions
        ");
    }


    /**
     * Down
     */
    public function down()
    {
        $sql = "
            CREATE TABLE `versions` (
              `id` int(10) unsigned NOT NULL COMMENT 'Ид версии скрипта апдейтера',
              `datetime` datetime NOT NULL COMMENT 'Время и дата создания SQL скрипта',
              `username` varchar(10) NOT NULL COMMENT 'Логин пользователя, кто создал скрипт',
              KEY `id_idx` (`id`),
              KEY `dt_idx` (`datetime`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Версии изменения базы данных';
        ";
        $this->rawQuery($sql);
    }

}
