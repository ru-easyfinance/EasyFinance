<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Валюта: таблица
 */
class model_CurrencyTableTest extends myUnitTestCase
{
    /**
     * Выбрать список измененных объектов для синка
     */
    public function testQueryFindModifiedForSync()
    {
        $table = Doctrine::getTable('Currency');

        // Деактивируем все валюты, кроме первой
        $table->createQuery('c')
            ->update()
            ->set('c.is_active', 0)
            ->where('c.id > ?', 1)
            ->execute();

        $found = $table->queryFindModifiedForSync(array())->execute();
        $this->assertEquals(1, $found->count());
        $this->assertEquals(1, $found->getFirst()->getId());
    }

}
