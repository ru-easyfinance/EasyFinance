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
     * Сейчас на operation.chain_id нет FK на calendar_chain.
     * Т. е. СУБД не проверяет ссылочную целостность.
     * Этот тест просто фиксирует текущее положение дел.
     * Потом, если FK появится, то тест надо будет переписать, чтобы проверять
     * срабатывает ли ON DELETE RESTRICT/ON DELETE NULL etc.
     */
    public function testFailedDeleteCalendarChainIfConnectedWithOperation()
    {
        $account    = $this->helper->makeAccount();
        $cc         = $this->helper->makeCalendarChain($account);
        $operation  = $this->helper->makeCalendarOperation($cc, $account);

        $cc->delete();
        $op_stored = Doctrine::getTable('Operation')->find($operation->getId());
        $this->assertType('Operation', $op_stored);
    }



}
