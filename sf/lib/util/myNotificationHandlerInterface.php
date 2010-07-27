<?php

interface myNotificationHandlerInterface
{
    public function run(OperationNotification $notification);
}
