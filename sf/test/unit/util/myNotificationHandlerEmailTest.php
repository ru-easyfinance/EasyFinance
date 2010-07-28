<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Обработчик уведомлений об обперациях по email
 */
class util_myNotificationHandlerEmailTest extends myUnitTestCase
{
    /**
     * Отправить уведомление
     */
    public function testSend()
    {
        $ntfn = $this->helper->makeOperationNotification();

        $mailer = $this->getMock('sfMailer', array('send'), array(new sfEventDispatcher, array()));
        $mailer->expects($this->once())
            ->method('send');

        $handler = new myNotificationHandlerEmail($mailer);
        $this->assertTrue($handler->run($ntfn), 'Ok');
    }

}
