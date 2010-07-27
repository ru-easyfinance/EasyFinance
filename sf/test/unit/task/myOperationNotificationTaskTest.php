<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Таск для отправки напоминаний о запланированных операциях
 */
class task_myOperationNotificationTaskTest extends myUnitTestCase
{
    private $_cwd;
    private $_logs = array();

    /**
     * SetUp
     */
    protected function _start()
    {
        $this->_cwd = getcwd();
        chdir(sfConfig::get('sf_root_dir'));
    }

    /**
     * TearDown
     */
    protected function _end()
    {
        chdir($this->_cwd);
    }


    /**
     * Обработчик логов таска.
     * Сохраняет все, что таск выводит в пользователю
     */
    public function handleTaskLogs(sfEvent $event)
    {
        $this->_logs = array_merge($this->_logs, $event->getParameters());
    }


    /**
     * Проверить итоговый вывод таска
     *
     * @param  int $ok      - Кол-во отправленных уведомлений
     * @param  int $errors  - Кол-во ошибок
     */
    private function checkTaskLogSummary($ok, $errors)
    {
        $message = implode($this->_logs, PHP_EOL);
        $this->assertEquals(sprintf('Done: %d, Errors: %d', $ok, $errors), $message, 'Task log summary');
    }


    /**
     * Подготовить таск (мок), запустить и проверить вызовы методов
     *
     * @param  array  $ntfnList - Массив OperationNotification
     * @param  bool   $isOk     - Успешно отправить уведомления
     */
    private function runAndCheckTask(array $ntfnList, $isOk)
    {
        // Мок таска - перекроем 'getEventsFromQueue', чтобы изолировать логику
        // выборки уведомлений. И будем свои уведомления на отправку
        $task = $this->getMock('myOperationNotificationTask', array('getEventsFromQueue'), array($dispatcher = new sfEventDispatcher, new sfFormatter));
        $task->expects($this->once())
             ->method('getEventsFromQueue')
             ->will($this->returnValue($ntfnList));

        // Создаем и регистрируем обработчик уведомлении и будем проверять его вызовы
        $handler = $this->getMock('myNotificationHandlerInterface', array('run'));
        $handler->expects($this->exactly(count($ntfnList)))
             ->method('run')
             ->will($this->returnValue($isOk));

        foreach ($ntfnList as $ntfn) {
            $task->registerHandler($ntfn->getType(), $handler);
        }

        // Повесим собственный обработчик на логи таска, чтобы их сохранять и проверять
        $dispatcher->connect('command.log', array($this, 'handleTaskLogs'));


        // Запустить таск
        $task->run(
            $args = array(),
            $options = array('env' => 'test')
        );
    }


    // Tests
    // -------------------------------------------------------------------------


    /**
     * Установить/Получить обработчик событий
     */
    public function testSetGetHandler()
    {
        $task = new myOperationNotificationTask(new sfEventDispatcher, new sfFormatter);

        $handler = $this->getMock('myNotificationHandlerInterface');
        $task->registerHandler(1, $handler);
        $this->assertSame($handler, $task->getHandler(1));
    }


    /**
     * Исключение, если обработчик не найден
     */
    public function testGetHandlerException()
    {
        $task = new myOperationNotificationTask(new sfEventDispatcher, new sfFormatter);

        $this->setExpectedException('Exception', 'Handler');
        $task->getHandler('unknown type');
    }


    /**
     * Дефолтная инициализация обработчиков
     */
    public function testDefaultHandlers()
    {
        $task = new myOperationNotificationTask(new sfEventDispatcher, new sfFormatter);

        $this->assertType('myNotificationHandlerSms',   $task->getHandler(OperationNotification::TYPE_SMS));
        $this->assertType('myNotificationHandlerEmail', $task->getHandler(OperationNotification::TYPE_EMAIL));
    }


    /**
     * Отправить одно уведомление
     */
    public function testSendOneSuccess()
    {
        $ntfn = $this->helper->makeOperationNotification();

        $this->runAndCheckTask(array($ntfn), $isOk = true);
        $this->checkTaskLogSummary($ok = 1, $errors = 0);

        // Уведомление помечено как выполненное
        $ntfn->refresh();
        $this->assertEquals(0, $ntfn->getFailCounter(), 'Notification marked as send without problems');
        $this->assertTrue($ntfn->isDone(), 'Notification marked as done');
    }


    /**
     * Ошибка при отправке
     */
    public function testSendOneError()
    {
        $ntfn = $this->helper->makeOperationNotification();

        $this->runAndCheckTask(array($ntfn), $isOk = false);
        $this->checkTaskLogSummary($ok = 0, $errors = 1);

        $ntfn->refresh();
        $this->assertEquals(1, $ntfn->getFailCounter(), 'Notification marked as problem');
        $this->assertFalse($ntfn->isDone(), 'Notification NOT marked as done');
    }


    /**
     * Превышено максимально допустимое кол-во ошибок
     */
    public function testSendOneErrorLimit()
    {
        $max = sfConfig::get('app_notification_max_errors');
        $this->assertGreaterThan(1, $max);

        $ntfn = $this->helper->makeOperationNotification(null, array(
            'fail_counter' => $max - 1, // TODO: убрать хардкод
        ));

        $this->runAndCheckTask(array($ntfn), $isOk = false);
        $this->checkTaskLogSummary($ok = 0, $errors = 1);

        $ntfn->refresh();
        $this->assertEquals($max, $ntfn->getFailCounter(), 'Notification marked as problem');
        $this->assertTrue($ntfn->isDone(), 'Notification marked AS done');
    }


    /**
     * Отправлено несколько уведомлений
     */
    public function testSendSomeSuccess()
    {
        $ntfn1 = $this->helper->makeOperationNotification();
        $ntfn2 = $this->helper->makeOperationNotification();

        $this->runAndCheckTask(array($ntfn1, $ntfn2), $isOk = true);
        $this->checkTaskLogSummary($ok = 2, $errors = 0);
    }


    /**
     * Ничего не отправлялось
     */
    public function testNothingToSend()
    {
        $this->runAndCheckTask(array(), $isOk = true);
        $this->checkTaskLogSummary($ok = 0, $errors = 0);
    }

}
