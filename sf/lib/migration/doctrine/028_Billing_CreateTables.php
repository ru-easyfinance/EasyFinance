<?php

/**
 * Robokassa: создание таблиц
 */
class Migration028_Billing_CreateTables extends Doctrine_Migration_Base
{
    /**
     * Migrate
     */
    public  function migrate($upDown)
    {
        $options = array(
            'type'     => 'INNODB',
            'charset' => 'utf8'
        );


        // billing_services
        $fieldsServices = array(
            'id' => array(
                'type' => 'integer',
                'length' => 4,
                'unsigned' => 1,
                'notnull' => 1,
                'autoincrement' => true,
                'primary' => true,
            ),
            'name' => array(
                'type' => 'string',
                'length' => 64,
            ),
            'price' => array(
                'type' => 'float',
            ),
            'created_at' => array(
                'type' => 'datetime'
            ),
            'updated_at' => array(
                'type' => 'datetime'
            )
        );
        $this->table($upDown, 'billing_services', $fieldsServices, $options);


        // billing_transactions
        $fieldsTransactions = array(
            'id' => array(
                'type' => 'integer',
                'length' => 4,
                'unsigned' => 1,
                'notnull' => 1,
                'autoincrement' => true,
                'primary' => true,
            ),
            'user_id' => array(
                'type' => 'integer',
                'length' => 4,
                'unsigned' => 1,
            ),
            'paysystem' => array(
                'type' => 'string',
                'length' => 32,
            ),
            'service_id' => array(
                'type' => 'integer',
                'length' => 4,
                'unsigned' => 1,
            ),
            'subscription_id' => array(
                'type' => 'integer',
                'length' => 4,
                'unsigned' => 1,
            ),
            'price' => array(
                'type' => 'float',
            ),
            'term' => array(
                'type' => 'integer',
                'length' => 4,
                'unsigned' => 1,
            ),
            'total' => array(
                'type' => 'float',
            ),
            'status' => array(
                'type' => 'tinyint',
                'length' => 1,
                'unsigned' => 1,
            ),
            'success' => array(
                'type' => 'tinyint',
                'length' => 1,
                'unsigned' => 1,
            ),
            'error_code' => array(
                'type' => 'integer',
                'length' => 4,
                'unsigned' => 1,
            ),
            'error_message' => array(
                'type' => 'string',
                'length' => 64,
            ),
            'created_at' => array(
                'type' => 'datetime'
            ),
            'updated_at' => array(
                'type' => 'datetime'
            )
        );
        $this->table($upDown, 'billing_transactions', $fieldsTransactions, $options);


        // billing_subscriptions
        $fieldsSubscriptions = array(
            'id' => array(
                'type' => 'integer',
                'length' => 4,
                'unsigned' => 1,
                'notnull' => 1,
                'autoincrement' => true,
                'primary' => true,
            ),
            'user_id' => array(
                'type' => 'integer',
                'length' => 4,
                'unsigned' => 1,
            ),
            'service_id' => array(
                'type' => 'integer',
                'length' => 4,
                'unsigned' => 1,
            ),
            'price' => array(
                'type' => 'float',
            ),
            'subscribed_till' => array(
                'type' => 'datetime'
            ),
            'created_at' => array(
                'type' => 'datetime'
            ),
            'updated_at' => array(
                'type' => 'datetime'
            )
        );
        $this->table($upDown, 'billing_subscriptions', $fieldsSubscriptions, $options);
    }

}
