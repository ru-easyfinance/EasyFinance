<?php

/**
 * Добавляем расходные категории
 */
class Migration048_Create_Debt_Categories extends myBaseMigration
{
    /**
     * Up
     */
    function up()
    {
        $this->rawQuery(
            "INSERT INTO `system_categories` (`id`, `name`)
            VALUES (24, 'Долги'), (25, 'Выплата долгов');"
        );

        $this->rawQuery(
            "INSERT INTO category (
                `cat_parent`, 
                `user_id`,
                `system_category_id`, 
                `cat_name`,
                `type`
             ) (SELECT 0, id, 24, 'Долги', -1 FROM users)"
        );
        
        $this->rawQuery(
            "INSERT INTO category (
                `cat_parent`, 
                `user_id`,
                `system_category_id`, 
                `cat_name`,
                `type`
             ) (
                SELECT 
                    cat_id,
                    id, 
                    25,
                    'Выплата долгов',
                    -1
                FROM users u
                INNER JOIN category c ON u.id = c.user_id
                WHERE c.system_category_id = 24
             )"
        );

        $this->rawQuery(
            "UPDATE category c1
             LEFT JOIN category c2
                ON c2.system_category_id = 24
                AND c2.user_id = c1.user_id
                AND c1.system_category_id = 15
             SET c1.`cat_parent` = c2.`cat_id`"
        );
    }

    /**
     * Down
     */
    function down()
    {
        $this->rawQuery(
            "UPDATE `category` SET `cat_parent` = 0
            WHERE `system_category_id` = 15;"
        );

        $this->rawQuery(
            "DELETE FROM `category` WHERE `system_category_id` IN (24, 25);"
        );

        $this->rawQuery(
            "DELETE FROM `system_categories` WHERE `id` IN (24, 25);"
        );
    }
}
