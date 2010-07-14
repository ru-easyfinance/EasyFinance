<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';

/**
 * Операции
 */
class task_sendEmailAndSmsNotifyTaskTest extends myUnitTestCase
{
    private $_tmpFile;
    private $_cwd;


    /**
     * SetUp
     */
    protected function _start()
    {
        $zPath = sfConfig::get('sf_root_dir') . '/lib/vendor';
        set_include_path($zPath . PATH_SEPARATOR . get_include_path());

        $this->_tmpFile = tempnam(sys_get_temp_dir(), __CLASS__);

        $this->_cwd = getcwd();
        chdir(sfConfig::get('sf_root_dir'));
    }


    /**
     * TearDown
     */
    protected function _end()
    {
        chdir($this->_cwd);
        unlink($this->_tmpFile);
    }


    /**
     * Запустить команду и проверить ответ
     *
     * @param  string $inputData       - Строка на вход скрипту
     * @param  int    $expectedCode    - Код ответа
     * @return void
     */
    public function checkCmd($inputData, $expectedCode)
    {
        #Max: а какая связь между $inputData и запуском теста. Здесь ее не видно.
        #     Это либо плохой тест, либо плохая реализация
        file_put_contents($this->_tmpFile, $inputData);
        $task = new sendEmailAndSmsNotifyTask(new sfEventDispatcher, new sfFormatter);
        $code = $task->run(
            $args = array(),
            $options = array('env' => 'test')
        );
        $this->assertEquals($expectedCode, $code, "Expected exit code `{$expectedCode}`");
    }


    /**
     * Проверка отправки Email уведомлений
     */
    public function testEmail()
    {
        $notification =
        $this->createNotification( sendEmailAndSmsNotifyTask::TYPE_EMAIL,
            date('Y-m-d H:i:s', time()-1 ),
            date('Y-m-d H:i:s', time()+3600 )
        );

        // Выполняем таск, ожидаем 1 отправленное оповещение
        $this->checkCmd(null, $code = 1);

        $email = "";
        $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . sendEmailAndSmsNotifyTask::TMP_FILENAME;
        // Если есть заглушка читаем, удаляем и проверяем
        if (file_exists($file))
        {
            $email = file_get_contents($file);
            unlink($file);
        }

        //Скармливаем письмо зенду и проверяем отправителя, получателя и body
        $message = new Zend_Mail_Message(array('raw' => $email));
        $headers = $message->getHeaders();

        // Получаем тело, но не проверяем, т.к. пока непонятно что там должно быть
        $body = quoted_printable_decode( $message->getContent() );

        // Проверим некоторые параметры сообщения
        $this->AssertEquals( $headers['from'], sendEmailAndSmsNotifyTask::EMAIL_NAME . " <" . sendEmailAndSmsNotifyTask::EMAIL_FROM . ">" );
        $this->AssertEquals( $headers['to'], $notification->getOperation()->getUser()->getUserMail() );
        $this->AssertEquals( $headers['content-type'], "text/plain; charset=utf-8" );
    }


    /**
     * Тест отправки смс
     */
    public function testSMS()
    {
        $this->createNotification( sendEmailAndSmsNotifyTask::TYPE_SMS,
            date('Y-m-d H:i:s', time()-1 ),
            date('Y-m-d H:i:s', time()+3600 )
        );

        //Выполняем таск, ожидаем 1 отправленное оповещение
        $this->checkCmd(null, $code = 1);
    }


    /**
     * Тест с просроченой услугой
     */
    public function testServiceStopped()
    {
        $this->createNotification( sendEmailAndSmsNotifyTask::TYPE_SMS,
            date('Y-m-d H:i:s', time()-1 ),
            date('Y-m-d H:i:s', time()-3600 )
        );

        //Выполняем таск, ожидаем 0 отправленных оповещений
        $this->checkCmd(null, $code = 0);

    }


    /**
     * Тест для нескольких операция
     */
    public function testMultiple()
    {
        // Пройдет
        $this->createNotification( sendEmailAndSmsNotifyTask::TYPE_SMS,
            date('Y-m-d H:i:s', time()-1 ),
            date('Y-m-d H:i:s', time()+3600 )
        );

        // Не пройдет
        $this->createNotification( sendEmailAndSmsNotifyTask::TYPE_SMS,
            date('Y-m-d H:i:s', time()-1 ),
            date('Y-m-d H:i:s', time()+3600 ),
            1   // Отправлено
        );

       // Не пройдет
        $this->createNotification( sendEmailAndSmsNotifyTask::TYPE_SMS,
            date('Y-m-d H:i:s', time()-1 ),
            date('Y-m-d H:i:s', time()+3600 ),
            0,
            1   // Отработано
        );

        // Пройдет
        $this->createNotification( sendEmailAndSmsNotifyTask::TYPE_SMS,
            date('Y-m-d H:i:s', time()-1 ),
            date('Y-m-d H:i:s', time()+3600 )
        );

        //Выполняем таск, ожидаем 2 отправленных оповещений
        $this->checkCmd(null, $code = 2);

    }


    /**
     * Создать оповещение
     * @param int $type тип оповещения
     * @param string $date дата оповещения
     * @param string $subscribeDate дата действия услуги
     */
    private function createNotification( $type, $date, $subscribeDate, $isSent=0, $isDone=0 )
    {
        // Операция
        $op = $this->helper->makeOperation();
        $op->save();

        // Нужен телефон для отправки смс
        $op->getUser()->setSmsPhone('+7 900 000-0000')->save();

        // И емейл для отправки почты
        $op->getUser()->setUserMail('test@ef.ru')->save();

        // Оповещение
        $notification = new OperationNotification();
        $notification->setOperation( $op );
        $notification->setDateTime( $date );
        $notification->setType( $type );
        $notification->setIsSent( $isSent );
        $notification->setIsDone( $isDone );
        $notification->save();

        // Услуга (создается 1 раз)
        if (!($service = Doctrine::getTable('Service')->find(sendEmailAndSmsNotifyTask::NOTIFICATION_SERVICE_ID)))
        {
            $service = new Service();
            $service->setId( sendEmailAndSmsNotifyTask::NOTIFICATION_SERVICE_ID );
            $service->setName( 'test' );
            $service->setPrice( 100 );
            $service->save();
        }

        // Подписка
        $subscription = new ServiceSubscription();
        $subscription->setUser( $op->getUser() );
        $subscription->setService( $service );
        $subscription->setSubscribedTill( $subscribeDate );
        $subscription->save();

        return $notification;
    }
}