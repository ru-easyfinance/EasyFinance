<?php
/**
 * Общий, итоговый тахометр
 */
class myTotalTahometer extends myBaseTahometer
{
    /**
     * Конструктор
     */
    public function __construct($value, array $configuration)
    {
        $this->initialize($configuration);

        $this->setCalculatedValue($value);
    }

}
