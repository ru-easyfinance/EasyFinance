<?php

class BudgetArticleIncrementCalculator {

    /**
     * @var DateTime
     */
    private $_startDate;

    /**
     * @var Currency
     */
    private $_currency;

    private $_meanRateMonthsAmount;

    /**
     *
     * @param DateTime $startDate
     * @param Currency $mainCurrency валюта к которой приводим суммы
     * @param int $meanRateMonthsAmount
     */
    public function __construct(
        DateTime $startDate,
        Currency $currency,
        $meanRateMonthsAmount
    )
    {
        $this->_startDate = $startDate;
        $this->_currency = $currency;
        $this->_meanRateMonthsAmount = $meanRateMonthsAmount;
    }

    /**
     * Вычисляет вклад операции в статью бюджета
     * @param Operation $operation
     * @return BudgetArticleIncrement
     */
    public function calculate(Operation $operation)
    {
        $increment = new BudgetArticleIncrement();

        //получаем модуль вклада операции в бюджет:
        //получаем без знака
        $signed = false;
        $operationContributionAmount =
            $operation->getAmountForBudget($this->_currency, $signed);

        if ($operation->getDate() < $this->_startDate->format('Y-m-d'))
            $increment->mean +=
                $operationContributionAmount / $this->_meanRateMonthsAmount;

        //иначе учитываем операцию в текущем бюджете:
        //операцию не из календаря учтем как ad hoc
        else if (!$operation->isFromCalendar())
            $increment->adhoc += $operationContributionAmount;

        //подтвержденные и неподтвержденные учтем отдельно
        else if ($operation->isAccepted())
            $increment->calendarAccepted += $operationContributionAmount;

        else
            $increment->calendarFuture += $operationContributionAmount;

        return $increment;
    }
}

class BudgetArticleIncrement {
    public $mean = 0;
    public $adhoc = 0;
    public $calendarAccepted = 0;
    public $calendarFuture = 0;


    /**
     * Прибавляет к статье бюджета самого себя
     * @param BudgetCategory $budgetArticle
     */
    public function apply(BudgetCategory $budgetArticle)
    {
        foreach (array('mean', 'adhoc', 'calendarAccepted', 'calendarFuture') as $field)
            $budgetArticle->$field += $this->$field;
    }
}