<?php

/**
 * Operation: Обновляем колоки в таблице "Category"
 */
class Migration018_Category_UpdateColumn_Id extends Doctrine_Migration_Base
{
    /**
     * Up
     */
    function up()
    {
        $options = array(
            'notnull'       => false,
            'default'       => null,
            'unsigned'      => false,
            'autoincrement' => true,
        );
        $this->changeColumn('category', 'cat_id', 'integer', 4, $options);
    }


    /**
     * Down
     */
    public function down()
    {
        $options = array(
            'notnull'  => true,
            'default'  => 0,
            'unsigned' => false,
        );
        $this->changeColumn('category', 'cat_id', 'integer', 8, $options);
    }
}
