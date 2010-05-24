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
     * Выбрать список измененных объектов для синка
     */
    public function testQueryFindModifiedForSync()
    {
        $table = Doctrine::getTable('Currency');

        $c1 = $table->find(1);
        $c1->setDateTimeObject('updated_at', $this->_makeDate(1000));
        $c1->save();

        $c2 = $table->find(2);
        $c2->setDateTimeObject('updated_at', $this->_makeDate(2000));
        $c2->save();

        $found = $table->queryFindModifiedForSync($this->_makeDateRange(1000, 1001), null)
            ->execute();
        $this->assertEquals(1, $found->count());
        $this->assertEquals($c1->getId(), $found->getFirst()->getId());
    }


    /**
     * Выбрать список созданных объектов для синка
     */
    public function testQueryFindCreatedForSync()
    {
        $table = Doctrine::getTable('Currency');

        $c1 = $table->find(1);
        $c1->setDateTimeObject('created_at', $this->_makeDate(1000));
        $c1->save();

        $c2 = $table->find(2);
        $c2->setDateTimeObject('created_at', $this->_makeDate(2000));
        $c2->save();

        $found = $table->queryFindModifiedForSync($this->_makeDateRange(1000, 1001), null)
            ->execute();
        $this->assertEquals(1, $found->count());
        $this->assertEquals($c1->getId(), $found->getFirst()->getId());
    }

}
