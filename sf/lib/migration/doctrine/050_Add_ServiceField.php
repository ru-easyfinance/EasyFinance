<?php

/**
 * Service: Добавляем новое поле keyword для мнемонического обращения к услугам
 */
class Migration050_Add_ServiceField extends myBaseMigration
{
    /**
     * Migrate
     */
    function migrate($upDown)
    {
        $options = array(
            'notnull'  => true,
            'after'    => 'name',
        );

        $this->column(
            $upDown,
            'billing_services',
            'keyword',
            'string',
            32,
            $options
        );

        $definition = array(
            'fields' => array(
                'keyword' => array()
            ),
            'type' => 'unique'
        );

        $this->index(
            $upDown,
            'billing_services',
            'service_keyword',
            $definition
        );
    }

    /**
     * Расставить правильные зоны
     */
    public function postUp()
    {
        // Харкодовые id первых услуг
        $sms    = 1;
        $iphone = 2;

        $this->rawQuery("
            UPDATE billing_services SET keyword = 'sms'    WHERE id = $sms;
            UPDATE billing_services SET keyword = 'iphone' WHERE id = $iphone;
        ");
    }
}
