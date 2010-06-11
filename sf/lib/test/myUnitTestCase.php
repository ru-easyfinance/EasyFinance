<?php

/**
 * Base test class for all app unit tests
 */
abstract class myUnitTestCase extends sfPHPUnitTestCase
{
    /**
     * Returns database connection to wrap tests with transaction
     */
    protected function getConnection()
    {
        return Doctrine_Manager::getInstance()->getConnection('doctrine');
    }


    /**
     * Creates new helper
     *
     * @return myTestObjectHelper
     */
    protected function makeHelper()
    {
        return new myTestObjectHelper;
    }


    /**
     * Создание объекта, алиасы
     *
     * @param  string $model           - Название класса модели
     * @param  array  $props           - Массив свойств для инициализации объекта
     * @param  bool   $isTimestampable
     * @return void
     */
    public function checkModelDeclaration($model, array $props, $isTimestampable = false, $isSoftDelete = false)
    {
        $ob = new $model;
        $ob->fromArray($props, false);

        $expectedData = array_intersect_key($ob->toArray(false), $props);
        $this->assertEquals($props, $expectedData, "[{$model}] Alias column mapping");

        $date = date('Y-m-d H:i:s');
        $ob->save();
        $this->assertTrue((bool)$ob->getId());

        // Timestampable
        if ($isTimestampable) {
            $this->assertEquals($date, $ob->getCreatedAt(), '[{$model}] CreatedAt');
            $this->assertEquals($date, $ob->getUpdatedAt(), '[{$model}] UpdatedAt');
        }

        $this->assertEquals(1, $this->queryFind($model, $props)->count(),
            "[{$model}] Expected object saved properly");


        // SoftDelete
        if ($isSoftDelete) {
            $ob->delete();
            $this->assertNotNull($ob->getDeletedAt());
            $this->assertEquals($ob->getUpdatedAt(), $ob->getDeletedAt());
        }

    }


    /**
     * Создать дату с указанным смещением от текущей
     *
     * @param  int    $shift - Смещение в секундах
     * @return string
     */
    protected function _makeDate($shift)
    {
        return date(DATE_ISO8601, time()+$shift);
    }

}
