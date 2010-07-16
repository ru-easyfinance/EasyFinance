<?php

/**
 * User: добавляем колонки настроек напоминаний (см. Notification)
 */
class Migration109_User_Addcolumns extends Doctrine_Migration_Base
{
    /**
     * Migrate
     */
    function migrate($upDown)
    {
        $this->column($upDown, 'users', 'sms_phone', 'string', 100 );
        $this->column($upDown, 'users', 'time_zone_offset', 'float', 4, array(
            'notnull'=>true,
            'default' => 0
        ));
        $this->column($upDown, 'users', 'reminder_mail_aviable', 'integer', 1, array(
            'notnull'=>true,
            'unsigned'=>true,
            'default' => 0
        ));
        $this->column($upDown, 'users', 'reminder_mail_default_enabled', 'integer', 1, array(
            'notnull'=>true,
            'unsigned'=>true,
            'default' => 0
        ));
        $this->column($upDown, 'users', 'reminder_mail_days', 'integer', 1, array(
            'notnull'=>true,
            'unsigned'=>true,
            'default' => 0
        ));
        $this->column($upDown, 'users', 'reminder_mail_hour', 'integer', 4, array(
            'notnull'=>true,
            'unsigned'=>true,
            'default'=>11
        ));
        $this->column($upDown, 'users', 'reminder_mail_minutes', 'integer', 4, array(
            'notnull'=>true,
            'unsigned'=>true,
            'default'=>0
        ));
        $this->column($upDown, 'users', 'reminder_sms_aviable', 'integer', 1, array(
            'notnull'=>true,
            'unsigned'=>true,
            'default' => 0
        ));
        $this->column($upDown, 'users', 'reminder_sms_default_enabled', 'integer', 1, array(
            'notnull'=>true,
            'unsigned'=>true,
            'default' => 0
        ));
        $this->column($upDown, 'users', 'reminder_sms_days', 'integer', 1, array(
            'notnull'=>true,
            'unsigned'=>true,
            'default' => 0
        ));
        $this->column($upDown, 'users', 'reminder_sms_hour', 'integer', 4, array(
            'notnull'=>true,
            'unsigned'=>true,
            'default'=>11
        ));
        $this->column($upDown, 'users', 'reminder_sms_minutes', 'integer', 4, array(
            'notnull'=>true,
            'unsigned'=>true,
            'default'=>0
        ));
    }
}
