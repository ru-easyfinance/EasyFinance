<?php

/**
 * Базовый запрос для выборки объектов для синхронизации
 */
abstract class mySyncOutQuery
{
    /**
     * Doctrine_Query
     */
    private $_query;


    /**
     * Получить название модели
     *
     * @return string
     */
    abstract public function getModelName();


    /**
     * Конструктор
     * Инициализирует запрос
     *
     * @param  myDatetimeRange $range
     * @param  int             $userId
     * @param  string          $alias
     * @return void
     */
    public function __construct(myDatetimeRange $range, $userId, $alias = 'a')
    {
        $modelName = $this->getModelName();
        $table = Doctrine::getTable($modelName);

        $dateStart = $range->getStart()->format('Y-m-d H:i:s');
        $dateEnd   = $range->getEnd()->format('Y-m-d H:i:s');

        $this->_query = Doctrine_Query::create()
            ->from("{$modelName} {$alias}")
            ->andWhere("{$alias}.updated_at BETWEEN CAST(? AS DATETIME) AND CAST(? AS DATETIME)", array(
                $dateStart, $dateEnd
            ));

        if ($table->hasColumn('user_id')) {
            $this->_query->andWhere("{$alias}.user_id = ?", (int)$userId);
        }

        $this->_extendQuery($range, $userId, $alias);
    }


    /**
     * Хук для уточнения запроса для конкретной модели
     *
     * @param  myDatetimeRange $range
     * @param  int             $userId
     * @param  string          $alias
     * @return void
     */
    protected function _extendQuery(myDatetimeRange $range, $userId, $alias)
    {
    }


    /**
     * Получить инициализированный запрос
     *
     * @return Doctrine_Query
     */
    public function getQuery()
    {
        return $this->_query;
    }

}
