<?php

/**
 * Таск для отправки напоминаний о запланированных операциях
 */
class myOperationNotificationTask extends sfBaseTask
{
    /**
     * array myNotificationHandlerInterface
     */
    private $_handlers = array();


    /**
     * Конфигурация
     */
    public function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
        ));

        $this->namespace = 'cron';
        $this->name      = 'notify';

        $this->briefDescription    = 'Send operation notifications';
        $this->detailedDescription =
            "SMS and Email notification service" . PHP_EOL . PHP_EOL
          . "[./symfony cron:notify]";
    }


    /**
     * Зарегистрировать обработчик уведомлений
     *
     * @param  string                         $type
     * @param  myNotificationHandlerInterface $handler
     * @return void
     */
    public function registerHandler($type, myNotificationHandlerInterface $handler)
    {
        $this->_handlers[$type] = $handler;
    }


    /**
     * Получить обработчик
     *
     * @param  string $type
     * @return myNotificationHandlerInterface
     */
    public function getHandler($type)
    {
        if (!isset($this->_handlers[$type])) {
            throw new Exception(__METHOD__.": Handler for `{$type}` not found");
        }
        return $this->_handlers[$type];
    }


    /**
     * Инициализировать дефолтные обработчки
     *
     * @return void
     */
    private function _initDefaultHandlers()
    {
        if (!$this->_handlers) {
            $this->registerHandler(OperationNotification::TYPE_SMS,   new myNotificationHandlerSms);
            $this->registerHandler(OperationNotification::TYPE_EMAIL, new myNotificationHandlerEmail($this->getMailer()));
        }
    }


    /**
     * Выбрать уведомления из очереди
     *
     * @return array OperationNotification
     */
    public function getEventsFromQueue()
    {
        return Doctrine::getTable('OperationNotification')
            ->queryFindUnprocessed()->execute()
            ->getData();
    }


    /**
     * Run
     */
    public function execute($arguments = array(), $options = array())
    {
        $countOk    = 0;
        $countError = 0;

        // Надо подключить конфиг
        $configuration = $this->createConfiguration('frontend', $options['env']);
        // Инициализировать соединение с БД
        $databaseManager = new sfDatabaseManager($this->configuration);

        $this->_initDefaultHandlers();

        foreach ($this->getEventsFromQueue() as $notification) {

            $handler = $this->getHandler($notification->getType());

            // OK
            if ($handler->run($notification)) {
                $notification->setIsSent(1);
                $notification->setIsDone(1);
                $countOk++;

            // Error
            } else {
                $failsCounter = $notification->getFailCounter() + 1;
                $notification->setFailCounter($failsCounter);

                // Если количество ошибок привысило максимально допустимое,
                // завершаем с этим оповещением
                if ($failsCounter >= sfConfig::get('app_notification_max_errors')) {
                    $notification->setIsDone(1);
                }
                $countError++;
            }
            $notification->save();
        }

        $this->log(sprintf('Done: %d, Errors: %d', $countOk, $countError));
    }

}
