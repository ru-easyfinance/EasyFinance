<?php

/**
 * Удалить таблицу `category_often`
 */
class Migration024_CategoryOften_DropTable extends myBaseMigration
{
    /**
     * Up
     */
    public function up()
    {
        $this->rawQuery("
            DROP TABLE IF EXISTS categories_often
        ");
    }


    /**
     * Down
     */
    public function down()
    {
        $sql = "
            CREATE TABLE `categories_often` (
              `user_id` int(100) unsigned NOT NULL,
              `category_id` int(11) NOT NULL,
              `cnt` int(11) NOT NULL,
              KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ";
        $this->rawQuery($sql);
    }

}
