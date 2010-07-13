<?php

/**
 * Operation: Добавляем индексы
 */
class Migration032_Operation_AddIndex extends myBaseMigration
{
    /**
     * Up
     */
    public function up()
    {
        // Индекс по счёту перевода
        $definition = array(
            'fields'=>array('transfer')
        );
        $this->addIndex('operation', 'transfer_account_id', $definition);

        // Индекс, который нам понадобится на некоторое время
        $definition = array(
            'fields'=>array('tr_id')
        );
        $this->addIndex('operation', 'tr_id', $definition);
    }


    /**
     * Down
     */
    public function down()
    {
        $this->removeIndex('operation', 'transfer_account_id');
        $this->removeIndex('operation', 'tr_id');
    }
}
