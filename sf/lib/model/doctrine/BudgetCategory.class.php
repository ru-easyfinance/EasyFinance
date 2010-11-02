<?php

/**
 * BudgetCategory
 */
class BudgetCategory extends BaseBudgetCategory
{
    const TYPE_EXPENCE = 1;
    const TYPE_PROFIT  = 0;
    //среднее значение за месяцы, предшествующие выбранному
    public $mean = 0;

    //траты вне календаря за выбранный месяц
    public $adhoc = 0;

    //подтвержденные траты в календаре за выбранный месяц
    public $calendarAccepted = 0;

    //будущие (неподтвержденные) траты в календаре за выбранный месяц
    public $calendarFuture = 0;

    public function getData()
    {
        //throw new Exception("форматировать данные должен вызывающий код контроллера! вынесите это туда.");
        $data = parent::getData();
        $data['plan'] = $this->getAmount();
        $data['adhoc'] = $this->adhoc;
        $data['calendar_accepted'] = $this->calendarAccepted;
        $data['calendar_future'] = $this->calendarFuture;
        $data['mean'] = $this->mean;
        return $data;
    }
}
