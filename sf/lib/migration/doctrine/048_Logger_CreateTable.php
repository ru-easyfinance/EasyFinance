<?php

/**
 * Создаём таблицу my_logger_logs
 */
class Migration048_Logger_CreateTable extends myBaseMigration
{
    public  function up() {
        $q = "CREATE TABLE IF NOT EXISTS `my_logger_logs` (
                `id` BIGINT AUTO_INCREMENT,
                `state` VARCHAR(255) DEFAULT 'info' NOT NULL,
                `component` VARCHAR(255) NOT NULL,
                `label` VARCHAR(255) NOT NULL,
                `result` TEXT NOT NULL,
                `context` LONGTEXT,
                `user_id` INT UNSIGNED,
                `model_id` INT UNSIGNED,
                `created_at` DATETIME NOT NULL,
            PRIMARY KEY(`id`)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB";
        $this->rawQuery($q);
    }


    public  function down() {
        $this->rawQuery($q = "DROP TABLE IF EXISTS `my_logger_logs`;");
    }

}
