<?php

/**
 * Category: Удалить колонку `cat_active`
 */
class Migration022_Category_DropColumn_CatActive extends Doctrine_Migration_Base
{
    /**
     * Migrate
     */
    function migrate($upDown)
    {
        $upDown = ('up' == $upDown) ? 'down' : 'up';

        $this->column($upDown, 'category', 'cat_active', 'integer', 4, array(
            'notnull' => true,
            'default' => 1,
        ));
    }

}
