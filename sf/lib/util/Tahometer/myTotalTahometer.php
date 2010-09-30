<?php
/**
 * Общий, итоговый тахометр
 */
class myTotalTahometer extends myBaseTahometer
{
    /**
     * Конструктор
     */
    public function __construct($value, array $configuration, array $properties = array())
    {
        $this->initialize($configuration);

        $this->setCalculatedValue($value);

        $this->properties = $properties;
    }

}
