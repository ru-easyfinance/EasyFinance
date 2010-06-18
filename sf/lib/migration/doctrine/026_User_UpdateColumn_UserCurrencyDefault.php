<?php

/**
 * User: user_currency_default приводим тип к универсальному решению
 */
class Migration026_User_UpdateColumn_UserCurrencyDefault extends Doctrine_Migration_Base
{
    /**
     * Up
     */
    function up()
    {
        $options = array(
            'notnull'  => true,
            'default'  => 1,
            'unsigned' => false,
        );

        $this->changeColumn('users', 'user_currency_default', 'integer', 4, $options);
    }


    /**
     * Down
     */
    function down()
    {
        $options = array(
            'notnull'  => true,
            'default'  => 1,
            'unsigned' => true,
        );

        $this->changeColumn('users', 'user_currency_default', 'integer', 1, $options);
    }

}
