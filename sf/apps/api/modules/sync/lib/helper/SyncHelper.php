<?php


    /**
     * Вывести дату в формате ISO 8601
     *
     * @param  string $dateStr
     * @return string
     */
    function sync_date($dateStr)
    {
        $date = new DateTime($dateStr);
        return $date->format(DATE_ISO8601);
    }
