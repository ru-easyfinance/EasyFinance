<?php

/**
 * Уведомление об операции
 */
class OperationNotification extends BaseOperationNotification
{
    const TYPE_SMS   = 0;
    const TYPE_EMAIL = 1;


    /**
     * Выполнено
     *
     * @return bool
     */
    public function isDone()
    {
        return (bool) $this->_get('is_done');
    }
}
