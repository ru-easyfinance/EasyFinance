<?php
/**
 * Тахометр
 */
class myTahometer extends myBaseTahometer
{
    // Вес?
    protected $weight = 0;
    // Как вычисляем
    protected $calculationMethod = null;

    /**
     * Конструктор
     */
    public function __construct(array $configuration, array $properties = array())
    {
        $this->initialize($configuration);

        $this->properties = $properties;
    }


    /**
     * Инициализация конфигурации
     *
     * @param   array   $configuration
     * @return  void
     */
    public function initialize(array $configuration)
    {
        parent::initialize($configuration);

        $this->calculationMethod = (string) $configuration['method'];
        $this->weight            = (int)    $configuration['weight'];
    }


    /**
     * Вес тахометра
     *
     * @return  integer
     */
    protected function getWeight()
    {
        return (int) $this->weight;
    }


    /**
     * Получить взвешенное значение
     *
     * @return  float
     */
    public function getWeightedValue()
    {
        return ($this->getZoneIndex() + $this->getValuePositionInZone()) * $this->getWeight();
    }


    /**
     * Метод вычисления, тип подсчета
     *
     * @return  string
     */
    protected function getCalculationMethod()
    {
        return $this->calculationMethod;
    }


    /**
     * Обратный ли метод вычисления
     *
     * @return  boolean
     */
    protected function isNegative()
    {
        return (bool) ($this->getCalculationMethod() == 'negative');
    }


    /**
     * Установить значения
     * установка значения как отношения 2-х величин, используемых в данном тахометре, в процентах
     * в итоге получаем некий процент, но для разных тахометров он может считаться по-разному
     *
     * @param   float   $dividend
     * @param   float   $divisor
     * @return  void
     */
    public function setParams($dividend, $divisor)
    {
        //если не задан числитель, ставим минимальное значение данного тахометра
        if($dividend == 0) {
            $this->setCalculatedValue(
                $this->IsNegative() ? $this->getMaximalZoneBorder() : $this->getMinimalZoneBorder()
            );

        //если не задан знаменатель, ставим максимальное значение для данного тахометра
        } elseif($divisor == 0) {
            $this->setCalculatedValue(
                $this->IsNegative() ? $this->getMinimalZoneBorder() : $this->getMaximalZoneBorder()
            );
        } else {
            //значение считаем в зависимости от типа подсчета значения в тахометре
            switch ($this->getCalculationMethod()) {
                //прямое отношение величин
                case 'direct':
                    $this->setCalculatedValue($dividend / $divisor);
                    break;

                //1 - отношение (считаем оставшуюся величину в процентах)
                case 'negative':
                    $this->setCalculatedValue((1 - $dividend / $divisor) * self::PERCENT);
                    break;

                //перекрытие (считаем превышение в процентах)
                case 'over':
                    $this->setCalculatedValue(($dividend / $divisor - 1) * self::PERCENT);
                    break;
            }
        }
    }

}
