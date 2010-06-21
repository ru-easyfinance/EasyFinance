<?php

/**
 * Форма услуг
 */
class ServiceForm extends BaseServiceForm
{
    /**
     * Конфигурация
     *
     */
    public function configure()
    {
        // Убираем поля, которые не должны участвовать в форме
        unset($this['created_at'], $this['updated_at']);
    }
}
