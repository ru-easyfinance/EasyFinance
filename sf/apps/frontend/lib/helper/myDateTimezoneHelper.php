<?php

/**
 * Хелпер для работы с часовыми поясами
 */
class myDateTimezoneHelper
{
    public static $zones = array(
        'Pacific/Kwajalein'    => 'Энивоток, Кваджалейн',
        'Pacific/Apia'         => 'Остров Мидуэй, Самоа',
        'Pacific/Honolulu'     => 'Гаваи',
        'America/Anchorage'    => 'Аляска',
        'America/Los_Angeles'  => 'Тихоокеанское время (США &amp; Canada)',
        'America/Phoenix'      => 'Аризона',
        'America/Denver'       => 'Горное время (США &amp; Канада)',
        'America/Chihuahua'    => 'Ла Паз, Мазатлан, Чихуахуа',
        'America/Managua'      => 'Центральноамериканское время',
        'America/Regina'       => 'Саскачеван',
        'America/Mexico_City'  => 'Гвадалахара, Мехико, Монтеррей',
        'America/Chicago'      => 'Центральное время (США &amp; Канада)',
        'America/Indianapolis' => 'Индиана (Восток)',
        'America/Bogota'       => 'Богота, Лима, Кито',
        'America/New_York'     => 'Восточное время (США &amp; Канада)',
        'America/Caracas'      => 'Каракас, Асунсьон',
        'America/Santiago'     => 'Сантьяго',
        'America/Halifax'      => 'Атлантическое время (Канада)',
        'America/St_Johns'     => 'Ньюфаундленд',
        'America/Buenos_Aires' => 'Буэнос Айрос, Джоржтаун',
        'America/Godthab'      => 'Гренландия',
        'America/Sao_Paulo'    => 'Бразилиа',
        'America/Noronha'      => 'Среднеатлантическое время',
        'Atlantic/Cape_Verde'  => 'Кабо-Верде',
        'Atlantic/Azores'      => 'Азорские острова',
        'Africa/Casablanca'    => 'Касабланка, Монровия',
        'Europe/London'        => 'Дублин, Эдинбург, Лиссабон, Лондон',
        'Africa/Lagos'         => 'Западное центральноафриканское время',
        'Europe/Berlin'        => 'Амстердам, Берлин, Берн, Рим, Стокгольм, Вена',
        'Europe/Paris'         => 'Брюссель, Копенгаген, Мадрид, Париж',
        'Europe/Sarajevo'      => 'Сараево, Скопье, Варашава, Загреб',
        'Europe/Belgrade'      => 'Белград, Братислава, Будапешт, Варшава, Любляна, Прага',
        'Africa/Johannesburg'  => 'Хараре, Претория',
        'Asia/Jerusalem'       => 'Иерусалим',
        'Europe/Istanbul'      => 'Афины, Стамбул, Минск',
        'Europe/Helsinki'      => 'Хельсинки, Киев, Рига, София, Таллин, Вильнюс',
        'Africa/Cairo'         => 'Каир',
        'Europe/Bucharest'     => 'Будапешт',
        'Africa/Nairobi'       => 'Найроби',
        'Asia/Riyadh'          => 'Кувейт, Эр-Рияд',
        'Europe/Moscow'        => 'Москва, Санкт-Петербург, Волгоград',
        'Asia/Baghdad'         => 'Багдад',
        'Asia/Tehran'          => 'Тегеран',
        'Asia/Muscat'          => 'Абу Даби, Мускат',
        'Asia/Tbilisi'         => 'Баку, Тбилиси, Ереван',
        'Asia/Kabul'           => 'Кабул',
        'Asia/Karachi'         => 'Исламабад, Карачи, Ташкент',
        'Asia/Yekaterinburg'   => 'Екатеринбург',
        'Asia/Calcutta'        => 'Калькутта, Мумбай, Нью Дели, Ченнай',
        'Asia/Katmandu'        => 'Катманду',
        'Asia/Colombo'         => 'Шри Джаяварденепура',
        'Asia/Dhaka'           => 'Астана, Дакка',
        'Asia/Novosibirsk'     => 'Алмата, Новосибирск',
        'Asia/Rangoon'         => 'Рангун',
        'Asia/Bangkok'         => 'Банкок, Ханой, Джакарта',
        'Asia/Krasnoyarsk'     => 'Красноярск',
        'Australia/Perth'      => 'Перт',
        'Asia/Taipei'          => 'Тайпей',
        'Asia/Singapore'       => 'Куала Лумпур, Сингапур',
        'Asia/Hong_Kong'       => 'Гонк Конг, Beijing, Chongqing, Urumqi',
        'Asia/Irkutsk'         => 'Иркутск, Улан Батор',
        'Asia/Tokyo'           => 'Осака, Саппоро, Токио',
        'Asia/Seoul'           => 'Сеул',
        'Asia/Yakutsk'         => 'Якутск',
        'Australia/Darwin'     => 'Дарвин',
        'Australia/Adelaide'   => 'Аделаида',
        'Pacific/Guam'         => 'Гуам, Порт Моресби',
        'Australia/Brisbane'   => 'Брисбей',
        'Asia/Vladivostok'     => 'Владивосток',
        'Australia/Hobart'     => 'Хобарт',
        'Australia/Sydney'     => 'Канбера, Мальбурн, Сидней',
        'Asia/Magadan'         => 'Магадан, Сахалин, Соломоновы острова, Новая Каледония',
        'Pacific/Fiji'         => 'Камчатка, Маршалловы острова, Фиджи',
        'Pacific/Auckland'     => 'Окленд, Веллингтон',
        'Pacific/Tongatapu'    => 'Нуку-алофа',
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
