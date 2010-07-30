<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Хелпер для работы с часовыми поясами
 */
class helper_myDateTimezoneHelperTest extends myUnitTestCase
{
    /**
     * Получить список подготовленных зон
     */
    public function testGetZones()
    {
        $zones = myDateTimezoneHelper::getZones();

        $this->assertTrue(isset($zones['Europe/Moscow']));

        $date = new DateTime('now', new DateTimeZone('Europe/Moscow'));
        $this->assertEquals($date->format('H:i'), $zones['Europe/Moscow']['offset']);
    }
}
