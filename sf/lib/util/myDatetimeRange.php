<?php

/**
 * Период времени
 */
class myDatetimeRange
{
    private
        $_dateStart,
        $_dateEnd;


    /**
     * Конструктор
     *
     * @param  DateTime $dateStart - дата/время начала периода
     * @param  DateTime $dateEnd   - дата/время окончания периода
     * @return void
     */
    public function __construct(DateTime $dateStart, DateTime $dateEnd)
    {
        if ($dateStart > $dateEnd) {
            throw new InvalidArgumentException(__METHOD__.": Expected start date is less than end date");
        }
        $this->_dateStart = clone $dateStart;
        $this->_dateEnd   = clone $dateEnd;

    }


    /**
     * Получить дату/время начала периода
     *
     * @return DateTime
     */
    public function getStart()
    {
        return clone $this->_dateStart;
    }


    /**
     * Получить дату/время окончания периода
     *
     * @return DateTime
     */
    public function getEnd()
    {
        return clone $this->_dateEnd;
    }

}
