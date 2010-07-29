<?php


    /**
     * Вывести дату в формате ISO 8601
     *
     * @param  string $dateStr
     * @return string
     */
    function sync_date($dateStr)
    {
        if (false !== strpos($dateStr, "0000-00-00")) {
            return "";
        }

        $date = new DateTime($dateStr);
        return $date->format(DATE_ISO8601);
    }


    /**
     * Вывести строку с заменой небезопасных xml-символов сущностями
     * @see EscapingHelper.php
     * @see http://www.php.net/manual/en/function.htmlspecialchars.php
     *
     * @param  string $value
     * @return string
     */
    function esc_xml($value)
    {
        return str_replace('&#039;', '&apos;', htmlspecialchars($value, ENT_QUOTES, mb_internal_encoding()));
    }
    define('ESC_XML', 'esc_xml');
