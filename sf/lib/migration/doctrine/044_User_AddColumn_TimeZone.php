<?php

/**
 * User: Добавить колонку time_zone
 */
class Migration044_User_AddColumn_TimeZone extends myBaseMigration
{
    /**
     * Migrate
     */
    function migrate($upDown)
    {
        $options = array(
            'notnull'  => true,
            'default'  => 'Europe/Moscow',
            'after'    => 'sms_phone',
        );
        $this->column($upDown, 'users', 'time_zone', 'string', 32, $options);
    }


    /**
     * Расставить правильные зоны
     */
    public function postUp()
    {
        $zones = array(
            '-12.00' => 'Pacific/Kwajalein',
            '-11.00' => 'Pacific/Apia',
            '-10.00' => 'Pacific/Honolulu',
            '-9.00'  => 'America/Anchorage',
            '-8.00'  => 'America/Los_Angeles',
            '-7.00'  => 'America/Denver',
            '-6.00'  => 'America/Chicago',
            '-5.00'  => 'America/New_York',
            '-4.00'  => 'America/Halifax',
            '-3.50'  => 'America/St_Johns',
            '-3.00'  => 'America/Buenos_Aires',
            '-2.00'  => 'America/Noronha',
            '-1.00'  => 'Atlantic/Azore',
            '0.00'   => 'Europe/Moscow',   // Всем дефолтам - Москву
            '1.00'   => 'Europe/Paris',
            '2.00'   => 'Africa/Johannesburg',
            '3.00'   => 'Europe/Moscow',
            '3.50'   => 'Asia/Tehran',
            '4.00'   => 'Asia/Tbilisi',
            '4.50'   => 'Asia/Kabul',
            '5.00'   => 'Asia/Yekaterinburg',
            '5.50'   => 'Asia/Calcutta',
            '6.00'   => 'Asia/Novosibirsk',
            '7.00'   => 'Asia/Bangkok',
            '8.00'   => 'Asia/Irkutsk',
            '9.00'   => 'Asia/Tokyo',
            '9.50'   => 'Australia/Darwin',
            '10.00'  => 'Asia/Vladivostok',
            '11.00'  => 'Asia/Magadan',
            '12.00'  => 'Pacific/Fiji',
        );
        $zoneOffset = implode("','", array_keys($zones));
        $zoneNames  = implode("','", array_values($zones));

        $this->rawQuery("
            UPDATE users SET time_zone = ELT(FIELD(time_zone_offset, '{$zoneOffset}'), '{$zoneNames}');
            UPDATE users SET time_zone = 'Europe/Moscow' WHERE time_zone = '';
        ");
    }

}
