<?php

/**
 * Хелпер для работы с часовыми поясами
 */
class myDateTimezoneHelper
{
    public static $zones = array(
        'Pacific/Kwajalein'    => 'Энивоток, Кваджалейн',
        'Pacific/Apia'         => 'Midway Island, Samoa',
        'Pacific/Honolulu'     => 'Hawaii',
        'America/Anchorage'    => 'Alaska',
        'America/Los_Angeles'  => 'Pacific Time (US & Canada); Tijuana',
        'America/Phoenix'      => 'Arizona',
        'America/Denver'       => 'Mountain Time (US & Canada)',
        'America/Chihuahua'    => 'Chihuahua, La Paz, Mazatlan',
        'America/Managua'      => 'Central America',
        'America/Regina'       => 'Saskatchewan',
        'America/Mexico_City'  => 'Guadalajara, Mexico City, Monterrey',
        'America/Chicago'      => 'Central Time (US & Canada)',
        'America/Indianapolis' => 'Indiana (East)',
        'America/Bogota'       => 'Bogota, Lima, Quito',
        'America/New_York'     => 'Eastern Time (US & Canada)',
        'America/Caracas'      => 'Caracas, La Paz',
        'America/Santiago'     => 'Santiago',
        'America/Halifax'      => 'Atlantic Time (Canada)',
        'America/St_Johns'     => 'Newfoundland',
        'America/Buenos_Aires' => 'Buenos Aires, Georgetown',
        'America/Godthab'      => 'Greenland',
        'America/Sao_Paulo'    => 'Brasilia',
        'America/Noronha'      => 'Mid-Atlantic',
        'Atlantic/Cape_Verde'  => 'Cape Verde Is.',
        'Atlantic/Azores'      => 'Azores',
        'Africa/Casablanca'    => 'Casablanca, Monrovia',
        'Europe/London'        => 'Greenwich Mean Time : Dublin, Edinburgh, Lisbon, London',
        'Africa/Lagos'         => 'West Central Africa',
        'Europe/Berlin'        => 'Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna',
        'Europe/Paris'         => 'Brussels, Copenhagen, Madrid, Paris',
        'Europe/Sarajevo'      => 'Sarajevo, Skopje, Warsaw, Zagreb',
        'Europe/Belgrade'      => 'Belgrade, Bratislava, Budapest, Ljubljana, Prague',
        'Africa/Johannesburg'  => 'Harare, Pretoria',
        'Asia/Jerusalem'       => 'Jerusalem',
        'Europe/Istanbul'      => 'Athens, Istanbul, Minsk',
        'Europe/Helsinki'      => 'Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius',
        'Africa/Cairo'         => 'Cairo',
        'Europe/Bucharest'     => 'Bucharest',
        'Africa/Nairobi'       => 'Nairobi',
        'Asia/Riyadh'          => 'Kuwait, Riyadh',
        'Europe/Moscow'        => 'Moscow, St. Petersburg, Volgograd',
        'Asia/Baghdad'         => 'Baghdad',
        'Asia/Tehran'          => 'Tehran',
        'Asia/Muscat'          => 'Abu Dhabi, Muscat',
        'Asia/Tbilisi'         => 'Baku, Tbilisi, Yerevan',
        'Asia/Kabul'           => 'Kabul',
        'Asia/Karachi'         => 'Islamabad, Karachi, Tashkent',
        'Asia/Yekaterinburg'   => 'Ekaterinburg',
        'Asia/Calcutta'        => 'Chennai, Kolkata, Mumbai, New Delhi',
        'Asia/Katmandu'        => 'Kathmandu',
        'Asia/Colombo'         => 'Sri Jayawardenepura',
        'Asia/Dhaka'           => 'Astana, Dhaka',
        'Asia/Novosibirsk'     => 'Almaty, Novosibirsk',
        'Asia/Rangoon'         => 'Rangoon',
        'Asia/Bangkok'         => 'Bangkok, Hanoi, Jakarta',
        'Asia/Krasnoyarsk'     => 'Krasnoyarsk',
        'Australia/Perth'      => 'Perth',
        'Asia/Taipei'          => 'Taipei',
        'Asia/Singapore'       => 'Kuala Lumpur, Singapore',
        'Asia/Hong_Kong'       => 'Beijing, Chongqing, Hong Kong, Urumqi',
        'Asia/Irkutsk'         => 'Irkutsk, Ulaan Bataar',
        'Asia/Tokyo'           => 'Osaka, Sapporo, Tokyo',
        'Asia/Seoul'           => 'Seoul',
        'Asia/Yakutsk'         => 'Yakutsk',
        'Australia/Darwin'     => 'Darwin',
        'Australia/Adelaide'   => 'Adelaide',
        'Pacific/Guam'         => 'Guam, Port Moresby',
        'Australia/Brisbane'   => 'Brisbane',
        'Asia/Vladivostok'     => 'Vladivostok',
        'Australia/Hobart'     => 'Hobart',
        'Australia/Sydney'     => 'Canberra, Melbourne, Sydney',
        'Asia/Magadan'         => 'Magadan, Solomon Is., New Caledonia',
        'Pacific/Fiji'         => 'Fiji, Kamchatka, Marshall Is.',
        'Pacific/Auckland'     => 'Auckland, Wellington',
        'Pacific/Tongatapu'    => 'Nuku\'alofa',
    );


    /**
     * Получить список подготовленных зон
     *
     * @return array
     */
    static public function getZones()
    {
        $result = array();

        foreach (self::$zones as $zoneName => $zoneTitle) {
            $tz = new DateTimeZone($zoneName);
            $date = new DateTime('now', $tz);

            $zone = array(
                'title' => $zoneTitle,
                'offset' => $date->format('H:i'),
            );
            $result[$zoneName] = $zone;
        }

        // Отсортировать по времени
        uasort($result, create_function('$a,$b', '
            if ($a["offset"] == $b["offset"]) return 0;
            return  ($a["offset"] < $b["offset"]) ? -1 : 1;
        '));

        return $result;
    }

}
