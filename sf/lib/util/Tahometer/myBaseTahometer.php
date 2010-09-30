<?php
/**
 * Тахометр: Основной функционал тахометра
 */
abstract class myBaseTahometer
{
    /**
     * Названия тахометров
     *  - в конфигурации
     */
    const NAME_MONEY  = 'Money';
    const NAME_BUDGET = 'Budget';
    const NAME_LOANS  = 'Loans';
    const NAME_DIFF   = 'Difference';
    const NAME_TOTAL  = 'Total';

    const PERCENT = 100;

    const STYLE_TEMPLATE = "<p style=\"color:#%s;font-weight:bold;\">";

    // Зоны
    protected $zones = array();
    protected $zonesCount = null;
    protected $currentZone = null;
    // Тексты
    protected $title = '';
    protected $descriptions = array();

    // дополнительные свойства
    protected $properties = array();

    // Результат рассчетов
    private $calculatedValue = null;


    /**
     * Инициализация конфигурации
     *
     * @param   array   $configuration
     * @return  void
     */
    public function initialize(array $configuration)
    {
        $this->descriptions = $configuration['description'];
        $this->title = (string) $configuration['title'];
        $this->zones = $configuration['zone'];
    }


    /**
     * Кол-во зон
     *
     * @return  integer
     */
    protected function getZonesCount()
    {
        if (!$this->zonesCount) {
            $this->zonesCount = count($this->getZones()) - 1;
        }

        return $this->zonesCount;
    }


    /**
     * Зоны
     *
     * @return  array
     */
    protected function getZones()
    {
        return $this->zones;
    }


    /**
     * Меньшая граница зон
     *
     * @return  integer
     */
    protected function getMinimalZoneBorder()
    {
        $zones = $this->getZones();
        return reset($zones);
    }


    /**
     * Большая граница зон
     *
     * @return  integer
     */
    protected function getMaximalZoneBorder()
    {
        $zones = $this->getZones();
        return end($zones);
    }


    /**
     * Индекс текущей зоны
     *
     * @return  integer
     */
    protected function getZoneIndex()
    {
        if (null === $this->currentZone) {
            //вычислим, в какую зону входит значение тахометра
            $tempZoneIndex = 0;
            //перебираем все зоны, пока проходим в следующую зону
            $zoneBorders = $this->getZones();

            // TODO: не дергать $this->calculatedValue напрямую
            while(($tempZoneIndex < $this->getZonesCount() - 1) && ($this->calculatedValue > $zoneBorders[$tempZoneIndex+1])) {
                $tempZoneIndex++;
            }
            $this->currentZone = $tempZoneIndex;
        }

        return $this->currentZone;
    }


    /**
     * В какой трети зоны находимся, индекс подзоны
     *
     * @return  integer
     */
    protected function getSubzoneIndex()
    {
        //считаем подзоны равными по ширине:
        // TODO: не хардкодить так
        $subzonesCount = 3;
        $subzoneWidth = 1.0 / $subzonesCount;

        //вычисляем, где между левой и правой границей находимся
        $pozitionInsideZone = $this->getValuePositionInZone();

        //перебираем зоны, пока значение не станет меньше следующей зоны
        $nextSubzoneIndex = 1;
        while(($nextSubzoneIndex < $subzonesCount) && (($nextSubzoneIndex * $subzoneWidth) <= $pozitionInsideZone)) {
            $nextSubzoneIndex++;
        }

        return $nextSubzoneIndex - 1;
    }


    /**
     * Положение в зоне
     */
    protected function getValuePositionInZone()
    {
        $zoneIndex = $this->getZoneIndex();
        $zoneBorders = $this->getZones();
        $leftBorder = $zoneBorders[$zoneIndex];
        $rightBorder = $zoneBorders[$zoneIndex + 1];

        return ($this->calculatedValue - $leftBorder) / ($rightBorder - $leftBorder);
    }


    /**
     * Установить итоговое значение
     *
     * @param   mixed   $value
     * @return  void
     */
    protected function setCalculatedValue($value)
    {
        // гарантируем, что не вылезем за приделы
        if($value > $this->getMaximalZoneBorder()) {
            $value = $this->getMaximalZoneBorder();
        } elseif ($value < $this->getMinimalZoneBorder()) {
            $value = $this->getMinimalZoneBorder();
        }

        $this->calculatedValue = $value;
    }


    /**
     * Заглушка
     */
    protected function isNegative()
    {
        return false;
    }


    /**
     * Получить параметр
     *
     * @param   string  $name
     * @return  mixed
     */
    protected function getProperty($name)
    {
        return $this->properties[$name];
    }


    /**
     * Получить вычисленное значение
     *
     * @return  float
     */
    public function getValue()
    {
        $result    = $this->calculatedValue;
        $minBorder = $this->getMinimalZoneBorder();
        $maxBorder = $this->getMaximalZoneBorder();

        //нормализуем, если нужно выводить в тахометр
        // шкала от 0 до 100; 100 соответствует макс. значение тахометра, 0 - минимальное
        $result = ($result - $minBorder) / ($maxBorder - $minBorder);

        //для отрицательных тахометров - обратный порядок шкалы
        if ($this->isNegative()) {
            $result = 1 - $result;
        }

        return self::PERCENT * $result;
    }


    /**
     * Название тахометра
     *
     * @return  string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * Описание, исходя из рассчитанного значения
     *
     * @return  string
     */
    public function getDescription()
    {
        $zone = $this->getZoneIndex();
        $subZone = $this->getSubzoneIndex();

        $description = '';
        if (array_key_exists($zone, $this->descriptions)) {
            if (is_array($this->descriptions[$zone]) && array_key_exists($subZone, $this->descriptions[$zone])) {
                $description = $this->descriptions[$zone][$subZone];
            } else {
                $description = $this->descriptions[$zone];
            }
        } else {
            $description = $this->descriptions;
        }

        $description = $this->coloriseDescription((string) $description, $zone);

        return (string) $description;
    }


    /**
     * Покрасить первый абзац описания
     * TODO: проверять, что заменяем именно <p> в начале строки
     *
     * @param   string  $description
     * @param   integer $zoneIndex
     * @return  string
     */
    protected function coloriseDescription($description, $zoneIndex)
    {
        $colors = $this->getProperty('colors');
        $color = sprintf(self::STYLE_TEMPLATE, $colors[$zoneIndex]);
        $description = $color . substr($description, 3, strlen($description));

        return $description;
    }


    /**
     * Результаты вычислений в виде массива данных
     *
     * @return  array
     */
    public function toArray()
    {
        return array(
            'value'       => round($this->getValue()),
            'title'       => $this->getTitle(),
            'description' => $this->getDescription(),
        );
    }

}
