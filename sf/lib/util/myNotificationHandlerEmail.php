<?php

class myNotificationHandlerEmail implements myNotificationHandlerInterface
{
    /**
     * sfMailer
     */
    private $_mailer;

    /**
     * Шаблон сообщения
     */
    private $_template = "
Дата: %s
Категория: %s
Счет: %s
Сумма: %s %s
Комментарий: %s
    ";


    /**
     * Конструктор
     *
     * @param
     * @return void
     */
    public function __construct(sfMailer $mailer)
    {
        $this->_mailer = $mailer;
    }


    /**
     * Отправить уведомление
     *
     * @param  OperationNotification $notification
     * @return bool
     */
    public function run(OperationNotification $notification)
    {
        $message = $this->_makeMessage($notification->getOperation());

        $fails = array();
        $this->_mailer->send($message, $fails);

        return !(bool) $fails;
    }


    /**
     * Полное сообщение с описанием операции (для email)
     *
     * @param  Operation $operation
     * @return Swift_Message
     */
    private function _makeMessage(Operation $operation)
    {
        $from = array(sfConfig::get('app_notification_email_from') => sfConfig::get('app_notification_email_name'));
        $to   = $operation->getUser()->getUserMail();

        $subject = "Easyfinance.ru - напоминание об операции";

        $body = sprintf($this->_template,
            $operation->getDateTimeObject('date')->format('d.m.y'),
            $operation->getCategory(),
            $operation->getAccount(),
            abs($operation->getAmount()), $operation->getAccount()->getCurrency()->getCode(),
            $operation->getComment()
        );

        return $this->_mailer->compose($from, $to, $subject, $body);
    }

}
