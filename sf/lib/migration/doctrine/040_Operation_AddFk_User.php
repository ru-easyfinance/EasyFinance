<?php

/**
 * Opration: добавить FK на User
 */
class Migration040_Operation_AddFk_User extends Doctrine_Migration_Base
{
    /**
     * Migrate
     */
    public function migrate($upDown)
    {
        $this->foreignKey($upDown, 'operation', 'operation_VS_user', array(
             'local'        => 'user_id',
             'foreign'      => 'id',
             'foreignTable' => 'users',
             'onDelete'     => 'CASCADE',
        ));
    }

}
