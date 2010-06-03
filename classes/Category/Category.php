<?php

class Category
{
    const TYPE_PROFIT = 1;
    const TYPE_WASTE = -1;
    const TYPE_UNIVERSAL = 0;

    private static $types = array(
        self::TYPE_PROFIT         => 'profit',
        self::TYPE_WASTE         => 'waste',
        self::TYPE_UNIVERSAL     => 'universal',
    );

    /**
     * Возвращает массив типов операций
     *
     */
    final public static function getTypesArray()
    {
        return self::$types;
    }
}
