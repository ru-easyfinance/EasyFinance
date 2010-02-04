<?php
/**
 * Класс для обычных событий календаря
 */
class Calendar_Event_Developments extends Calendar_Event {

    /**
     * Конструктор
     * @param Calendar_Model $model
     * @param User $user
     */
    function __construct( Calendar_Model $model, User $user )
    {
        parent::__construct($model, $user);
    }

    /**
     * Возвращает тип события в виде строки
     * @return string
     */
    public function getType()
    {
        return 'e';
    }

    /**
     * Возвращает объект в виде массива
     * @return array mixed
     */
    public function __getArray()
    {
        return parent::__getArray();
    }


}