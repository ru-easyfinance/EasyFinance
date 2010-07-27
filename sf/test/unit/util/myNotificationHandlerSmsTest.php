<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Обработчик уведомлений об обперациях по sms
 *
 * TODO:
 * - обработка телефона
 * - телефон не указан
 * - принимать xml от шлюза
 */
class util_myNotificationHandlerSmsTest extends myUnitTestCase
{
    /**
     * Отправить уведомление
     */
    public function testSend()
    {
        // Убедиться что url указан к конфиге
        $url = sfConfig::get('app_notification_sms_url');
        $this->assertTrue(strlen($url)>0, 'app_notification_sms_url');

        $ntfn = $this->helper->makeOperationNotification();

        $handler = $this->getMock('myNotificationHandlerSms', array('_sendRequest'));
        $handler->expects($this->once())
            ->method('_sendRequest')
            ->will($this->returnValue('error_num=OK'))
            ->with($url);
            //->with($url, array(...)); - можно проверить входящие параметры

        $this->assertTrue($handler->run($ntfn), 'Ok');
    }


    /**
     * Ошибка отправки
     */
    public function testError()
    {
        $ntfn = $this->helper->makeOperationNotification();

        $handler = $this->getMock('myNotificationHandlerSms', array('_sendRequest'));
        $handler->expects($this->once())
            ->method('_sendRequest')
            ->will($this->returnValue('error_num=1'));

        $this->assertFalse($handler->run($ntfn), 'Is Error');
    }

}
