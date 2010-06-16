<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * Запрос для выборки валют для синхронизации
 */
class model_sync_mySyncOutCurrenctQueryTest extends myUnitTestCase
{
    protected $app = 'api';


    /**
     * Для синхронизации надо выбирать только активные валюты
     */
    public function testFindActiveForSync()
    {
        // Деактивировать все валюты кроме первой
        Doctrine::getTable('Currency')->createQuery()
            ->update()
            ->set('is_active', 0)
            ->where('id > ?', 1)
            ->execute();

        $q = new mySyncOutCurrencyQuery(new myDatetimeRange(new DateTime('-1year'), new DateTime), null);
        $found = $q->getQuery()->execute();

        $this->assertEquals(1, $found->count());
        $this->assertEquals(1, $found->getFirst()->getId());
    }

}
