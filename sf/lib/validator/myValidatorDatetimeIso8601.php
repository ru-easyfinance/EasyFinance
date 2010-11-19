<?php

/**
 * Валидатор дат в формате ISO 8601
 * Поддерживает только ограниченное число форматов, и только те,
 * где указан часовой пояс. Подробнее см. тест.
 */
class myValidatorDatetimeIso8601 extends sfValidatorDate
{
    private $_pattern = '/^(?:-?)\d{4}(-?)\d{2}(-?)\d{2}[T\s]\d{2}(:?)\d{2}((:?)\d{2})?([zZ]|([\+\-])([01]\d|2[0-3]):?([0-5]\d)?)$/';


    /**
     * Config
     */
    protected function configure($options = array(), $messages = array())
    {
        $this->addMessage('invalid', 'Invalid date format. Expected ISO 8601 like "2010-05-21T18:55:30+04:00"');
    }


    /**
     * Валидация
     *
     * @return string
     */
    protected function doClean($value)
    {
        $value = (string) $value;
        if (!preg_match($this->_pattern, $value, $match)) {
            throw new sfValidatorError($this, 'invalid');
        }
        $value = $match[0];

        try {
            $date = new DateTime($value);
        } catch (Exception $e) {
            throw new sfValidatorError($this, 'invalid');
        }

        $date->setTimezone(new DateTimeZone(date_default_timezone_get()));

        return $date->format(DATE_ISO8601);
    }

}
