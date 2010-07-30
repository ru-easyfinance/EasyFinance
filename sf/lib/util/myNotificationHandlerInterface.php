<?php

interface myNotificationHandlerInterface
{
    /**
     * Отправить уведомление
     *
     * @param  OperationNotification $notification
     * @return bool
     */
    public function run(OperationNotification $notification);
}
