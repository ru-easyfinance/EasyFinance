<?php

/**
 * User: Удалить колонку time_zone_offset
 */
class Migration045_User_DropColumn_TimeZoneOffset extends myBaseMigration
{
    /**
     * Migrate
     */
    function migrate($upDown)
    {
        $upDown = ('up' == $upDown) ? 'down' : 'up';

        $this->column($upDown, 'users', 'time_zone_offset', 'float', 4, array(
            'notnull' => true,
            'default' => 0,
            'after'   => 'sms_phone',
        ));
    }

}
