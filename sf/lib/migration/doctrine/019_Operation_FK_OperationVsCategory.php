<?php

/**
 * Operation: FK на category
 */
class Migration019_Operation_FK_OperationVsCategory extends Doctrine_Migration_Base
{
    /**
     * Удалить потерянные объекты
     */
    public function preUp()
    {
        Doctrine_Manager::getInstance()->getConnection('doctrine')->getDbh()->query("
            DELETE o FROM operation AS o
            LEFT JOIN category c ON o.cat_id=c.cat_id
            WHERE o.cat_id IS NOT NULL AND c.cat_id IS NULL
        ");
    }


    /**
     * Migrate
     */
    function migrate($upDown)
    {
        $definition = array(
            'local'        => 'cat_id',
            'foreign'      => 'cat_id',
            'foreignTable' => 'category',
            'onDelete'     => NULL
        );
        $this->foreignKey($upDown, 'operation', 'operation_vs_category', $definition);
    }
}
