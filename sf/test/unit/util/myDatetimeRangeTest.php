<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Период времени
 */
class util_myDatetimeRangeTest extends myUnitTestCase
{
    /**
     * Создать объект и получить даты
     */
    public function testMakeRangeAndGetDates()
    {
        $dateStart = new DateTime('2000-01-01 15:16:17');
        $dateEnd   = new DateTime('2000-01-20 16:17:18');
        $range = new myDatetimeRange($dateStart, $dateEnd);

        $this->assertEquals($dateStart, $range->getStart(), 'Start date');
        $this->assertEquals($dateEnd,   $range->getEnd(),   'End date');

        // Not same
        $this->assertNotSame($dateStart, $range->getStart(), 'NOT same: Start date');
        $this->assertNotSame($dateEnd,   $range->getEnd(),   'NOT same: End date');
    }


    /**
     * Исключение, если дата начала интервала больше даты окончания
     */
    public function testExceptionIfRangeIsInvalid()
    {
        $this->setExpectedException('InvalidArgumentException', 'start date is less than end date');
        $range = new myDatetimeRange(new DateTime('2000-01-20 16:17:18'), new DateTime('2000-01-01 15:16:17'));
    }

}
