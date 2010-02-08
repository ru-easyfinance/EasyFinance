<?php
/**
 * Класс для событий периодических операций
 */
class Calendar_Event_Periodic extends Calendar_Event {

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
     * Возвращает сумму операции
     * @return float
     */
    public function getAmount ()
    {
        return $this->model->amount;
    }

    /**
     * Возвращает ид категории
     * @return int
     */
    public function getCat ()
    {
        return $this->model->cat_id;
    }

    /**
     * Возвращает ид счёта
     * @return int
     */
    public function getAccount ()
    {
        return $this->model->account_id;
    }

    /**
     * Возвращает тип операции
     * Расход, Доход , Перевод со счёта
     * @return int
     */
    public function getOpType ()
    {
        return $this->model->op_type;
    }

    /**
     * Возвращает теги
     *
     * @return array
     */
    public function getTags ()
    {
        return $this->model->tags;
    }
    
    /**
     * Возвращает тип события в виде строки
     * @return string
     */
    public function getType()
    {
        return 'p';
    }

    /**
     * Возвращает объект в виде массива
     * @return array mixed
     */
    public function __getArray()
    {
        $array = parent::__getArray();
        $array['amount']  = $this->getAmount();
        $array['cat']     = $this->getCat();
        $array['account'] = $this->getAccount();
        $array['account'] = $this->getAccount();
        $array['op_type'] = $this->getOpType();
        $array['tags']    = $this->getTags();
        return $array;
    }
}