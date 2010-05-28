<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Таблица: Валюта
 */
class model_CurrencyTableTest extends myUnitTestCase
{
    /**
     * Создать дату с указанным смещением в секундах от `сегодня`
     *
     * @param  int $shift - кол-во секунд
     * @return DateTime
     */
    private function _makeDate($shift)
    {
        return new DateTime(date(DATE_ISO8601, time()+$shift));
    }


    /**
     * Создать интервал
     *
     * @param  int $shiftStart - смещение для первой даты
     * @param  int $shiftEnd   - смещение для второй даты
     * @return myDatetimeRange
     */
    private function _makeDateRange($shiftStart, $shiftEnd)
    {
        return new myDatetimeRange($this->_makeDate($shiftStart), $this->_makeDate($shiftEnd));
    }


    /**
     * Для синхронизации надо выбирать только активные валюты
     */
    public function testFindActiveForSync()
    {
        Doctrine::getTable('Currency')->createQuery()
            ->update()
            ->set('is_active', 0)
            ->where('id > ?', 1)
            ->execute();

        $found = Doctrine::getTable('Currency')
            ->queryFindModifiedForSync(new myDatetimeRange(new DateTime('-1year'), new DateTime), null)
            ->execute();
        $this->assertEquals(1, $found->count());
        $this->assertEquals(1, $found->getFirst()->getId());
    }

}
