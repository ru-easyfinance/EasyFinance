<?php

/**
 * Модель парсера
 */
class EmailParser extends BaseEmailParser
{
	/**
	 * Типы направлений движения средств
	 *
	 * @var array
	 */
	static private $typeChoices = array(
       Operation::TYPE_PROFIT   => "доход",
       Operation::TYPE_EXPENSE  => "расход"
    );

    /**
     * Получить название отправителя
     *
     * @return string
     */
    public function getSourceName()
    {
        return $this->getEmailSource()->getName();
    }

    /**
     * Получить массив типов направлений движения
     *
     * @return array
     */
    public static function getTypeChoices()
    {
        return self::$typeChoices;
    }

    /**
     * Получить строку с названием типа направления движения
     *
     * @return string
     */
    public function getTypeName()
    {
        return self::$typeChoices[ ( $this->getType() ) ? 1 : 0 ];
    }
}
