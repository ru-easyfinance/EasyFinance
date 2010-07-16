<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Планирование событий в календаре
 */
class model_CalendarChainTest extends myUnitTestCase
{
    /**
     * Отношения
     */
    public function testRelations()
    {
        $cc = new CalendarChain;

        // Пользователь
        $this->assertType('User', $cc->User);
    }

    /**
     * Создание объекта, алиасы
     */
    public function testMakeRecord()
    {
        $user = $this->helper->makeUser();
        $account = $this->helper->makeAccount($user);

        $data = array(
            'user_id'     => $user->getId(),
            'date_start'  => '2010-07-01',
            'date_end'    => '2011-07-01',
            'every_day'   => 30,
            'repeat'      => 1,
        );
        $this->checkModelDeclaration('CalendarChain', $data);
    }

    /**
     * Невозможно удалить запись в календаре, если у нее есть операции
     */
    public function testFailedDeleteCalendarChainIfConnectedWithOperation()
    {
        $account    = $this->helper->makeAccount();
        $cc         = $this->helper->makeCalendarChain($account);
        $operation  = $this->helper->makeCalendarOperation($cc, $account);

        $this->setExpectedException('Doctrine_Connection_Mysql_Exception', 'foreign key constraint fails');
        $cc->delete();
    }



}
