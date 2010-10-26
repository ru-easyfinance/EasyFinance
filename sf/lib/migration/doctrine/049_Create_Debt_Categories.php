<?php

/**
 * Добавляем расходные категории
 */
class Migration049_Create_Debt_Categories extends myBaseMigration
{
    /**
     * Долги
     */
    const DEBT = 24;
    /**
     * Выплата долгов
     */
    const DEBT_PAYMENTS = 25;
    /**
     * Проценты по кредитам
     */
    const INTEREST_ON_LOANS = 15;

    /**
     * Up
     */
    function up()
    {
        // Создаём системные категории для долгов
        $this->rawQuery(
            sprintf(
                "INSERT INTO `system_categories` (`id`, `name`)
                VALUES (%d, 'Долги'), (%d, 'Выплата долгов');",
                self::DEBT,
                self::DEBT_PAYMENTS
            )
        );

        // Создаём всем пользователям категорию "Долги"
        $this->rawQuery(
            sprintf(
                "INSERT INTO category (
                    `cat_parent`,
                    `user_id`,
                    `system_category_id`,
                    `cat_name`,
                    `type`
                 ) (SELECT 0, id, %d, 'Долги', -1 FROM users)",
                 self::DEBT
            )
        );

        // Создаём всем пользователям категорию "Выплата долгов"
        $this->rawQuery(
            sprintf(
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
                        %d,
                        'Выплата долгов',
                        -1
                    FROM users u
                    INNER JOIN category c ON u.id = c.user_id
                    WHERE c.system_category_id = %d
                 )",
                 self::DEBT,
                 self::DEBT_PAYMENTS
             )
        );

        // Делаем категорию "Проценты по кредитам" подкатегорией "Долгов"
        $this->rawQuery(
            sprintf(
                "UPDATE category c1
                 LEFT JOIN category c2
                    ON c2.system_category_id = %d
                    AND c2.user_id = c1.user_id
                    AND c1.system_category_id = %d
                 SET c1.`cat_parent` = c2.`cat_id`
                 WHERE c1.system_category_id = %d",
                 self::DEBT,
                 self::INTEREST_ON_LOANS,
                 self::INTEREST_ON_LOANS
             )
        );
    }

    /**
     * Down
     */
    function down()
    {
        // Убираем родительскую категорию у "Процентов по кредитам"
        $this->rawQuery(
            sprintf(
                "UPDATE `category` SET `cat_parent` = 0
                WHERE `system_category_id` = %d;",
                self::INTEREST_ON_LOANS
            )
        );

        // Удаляем долговые категории у всех операций
        $this->rawQuery(
            sprintf(
                "UPDATE `operation` INNER JOIN category
                ON category.cat_id = operation.cat_id
                SET `operation`.`cat_id` = NULL
                WHERE `system_category_id` IN (%d, %d)",
                self::DEBT,
                self::DEBT_PAYMENTS
            )
        );

        // Удаляем долговые категории
        $this->rawQuery(
            sprintf(
                "DELETE FROM `category` WHERE `system_category_id` IN (%d, %d)",
                self::DEBT,
                self::DEBT_PAYMENTS
            )
        );

        // Удяляем системные долговые категории
        $this->rawQuery(
            sprintf(
                "DELETE FROM `system_categories` WHERE `id` IN (%d, %d);",
                self::DEBT,
                self::DEBT_PAYMENTS
            )
        );
    }
}
