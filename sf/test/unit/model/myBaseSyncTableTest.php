<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Базовая таблица для объектов, которые поддерживают синхронизацию
 */
class model_myBaseSyncTableTest extends myUnitTestCase
{
    protected $app = 'api';

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
    public function testFindModified()
    {
        $table = Doctrine::getTable('Account');

        $accounts = $this->helper->makeAccountCollection(3, null, array(
            array('updated_at' => $this->_makeDate(1000)->format(DATE_ISO8601)),
            array('updated_at' => $this->_makeDate(1500)->format(DATE_ISO8601)),
            array('updated_at' => $this->_makeDate(2000)->format(DATE_ISO8601)),
        ));
        // Счет чужого пользователя
        $accountA = $this->helper->makeAccount(null, array('updated_at' => $this->_makeDate(1500)->format(DATE_ISO8601)));

        $found = $table->createBaseSyncQuery($this->_makeDateRange(1400, 1500), $accounts[0]->getUserId())
            ->execute();
        $this->assertEquals(1, $found->count());
        $this->assertEquals($accounts[1]->getId(), $found->getFirst()->getId());
    }


    /**
     * Выбрать удаленные объекты
     */
    public function testFindDeleted()
    {
        $table = Doctrine::getTable('Account');

        $account  = $this->helper->makeAccount(null, array('updated_at' => $this->_makeDate(-1500)->format(DATE_ISO8601)));
        $account->delete();

        $found = $table->createBaseSyncQuery($this->_makeDateRange(-100, 100), $account->getUserId())
            ->execute();
        $this->assertEquals(1, $found->count());
        $this->assertEquals($account->getId(), $found->getFirst()->getId());
    }


    /**
     * Выбрать список у которых нет пользователя
     */
    public function testFindModifiedWithoutUser()
    {
        $table = Doctrine::getTable('Currency');

        $c1 = $table->find(1);
        $c1->setDateTimeObject('updated_at', $this->_makeDate(1000));
        $c1->save();

        $found = $table->queryFindModifiedForSync($this->_makeDateRange(1000, 1001), null)
            ->execute();
        $this->assertEquals(1, $found->count());
        $this->assertEquals($c1->getId(), $found->getFirst()->getId());
    }

}
