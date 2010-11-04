<?php

/**
 * BudgetCategory
 */
class BudgetCategory extends BaseBudgetCategory
{
    const TYPE_EXPENSE = 1;
    const TYPE_PROFIT  = 0;
    //среднее значение за месяцы, предшествующие выбранному
    public $mean = 0;

    //траты вне календаря за выбранный месяц
    public $adhoc = 0;

    //подтвержденные траты в календаре за выбранный месяц
    public $calendarAccepted = 0;

    //будущие (неподтвержденные) траты в календаре за выбранный месяц
    public $calendarFuture = 0;

    public function getPlan()
    {
        return $this->getAmount();
    }
}
