<?php

/**
 * Robokassa: создание FK
 */
class Migration029_Billing_AddFks extends Doctrine_Migration_Base
{
    /**
     * Migrate
     */
    public function migrate($upDown)
    {
        // FKs
        $definition = array(
            'local'         => 'user_id',
            'foreign'       => 'id',
            'foreignTable'  => 'users',
            'onDelete'      => 'CASCADE'
        );

        $this->foreignKey($upDown, 'billing_transactions', 'bt_user_id_foreign_key', $definition);
        $this->foreignKey($upDown, 'billing_subscriptions', 'bs_user_id_foreign_key', $definition);

        $definition = array(
            'local'         => 'service_id',
            'foreign'       => 'id',
            'foreignTable'  => 'billing_services',
            'onDelete'      => 'CASCADE'
        );

        // При удалении услуги - удаляются подписки пользователей на эту услугу, но не транзакции
        $this->foreignKey($upDown, 'billing_subscriptions', 'bs_service_id_foreign_key', $definition);
    }
}
