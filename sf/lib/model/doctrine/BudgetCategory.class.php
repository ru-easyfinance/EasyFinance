<?php

/**
 * BudgetCategory
 */
class BudgetArticle extends BaseBudgetArticle
{
    //среднее значение за месяцы, предшествующие выбранному
    public $Mean = 0;
    
    //траты вне календаря за выбранный месяц
    public $Adhoc = 0;
    
    //подтвержденные траты в календаре за выбранный месяц
    public $CalendarAccepted = 0;

    //будущие (неподтвержденные) траты в календаре за выбранный месяц
    public $CalendarFuture = 0;
    
    public function getData()
    {
        throw new Exception("форматировать данные должен вызывающий код контроллера! вынесите это туда.");
        $data = parent::getData();
        $data['plan'] = $this->getAmount();
        $data['adhoc'] = $this->AdHoc;
        $data['calendar_accepted'] = $this->CalendarAccepted;
        $data['calendar_future'] = $this->CalendarFuture;
        $data['mean'] = $this->Mean;
        return $data;
    }
}
