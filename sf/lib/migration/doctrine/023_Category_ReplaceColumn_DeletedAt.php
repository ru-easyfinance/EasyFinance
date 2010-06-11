<?php

/**
 * Категории:
 *  - добавить колонку `deleted_at`
 *  - удалить колонку `visible`
 *  - удалить все записи помеченные как visible=0
 */
class Migration023_Category_RenameColumns_CreatedAndUpdated extends myBaseMigration
{
    /**
     * PreUp
     * Удалить все записи помеченные как visible=0
     */
    public function preUp()
    {
        // Сбросить категорию у операций
        $this->rawQuery("
            UPDATE operation AS o
                INNER JOIN category AS c ON (o.cat_id=c.cat_id)
            SET o.cat_id = NULL
            WHERE c.visible <> 1
        ");

        // Удалить категории
        $this->rawQuery("DELETE FROM category WHERE visible <> 1");
    }


    /**
     * Migrate
     */
    public function migrate($upDown)
    {
        $this->column($upDown, 'category', 'deleted_at', 'timestamp');

        // Удалить колонку `visible`
        $upDown = ('up' == $upDown) ? 'down' : 'up';
        $this->column($upDown, 'category', 'visible', 'integer', 1, array(
            'notnull' => true,
            'default' => 1,
        ));
    }

}
