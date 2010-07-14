<?php

/**
 * Создаём таблицу auth_remember_keys
 */
class Migration047_Create_Table_Auth_Remember_Keys extends myBaseMigration
{
    public  function up() {
        $q = "CREATE TABLE `auth_remember_keys` (
            `id` INT AUTO_INCREMENT,
            `user_id` INT(100) UNSIGNED,
            `remember_key` VARCHAR(32),
            `ip_address` VARCHAR(50),
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `user_id_idx` (`user_id`),
            PRIMARY KEY(`id`, `ip_address`),
            CONSTRAINT `auth_remember_keys_user_id_users_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = INNODB;";
        $this->rawQuery($q);
    }


    public  function down() {
        $this->rawQuery($q = "DROP TABLE IF EXISTS `auth_remember_keys`;");
    }

}
