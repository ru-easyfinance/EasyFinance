<?php

/**
 * BudgetCategory
 */
class BudgetCategory extends BaseBudgetCategory
{
    /**
     * Бюджет
     * @var Budget
     */
    private $_budget = null;

    public function getPlan()
    {
        return $this->getAmount();
    }

    public function getCalendarPlan()
    {
        return isset($this->_budget) ?
            $this->_budget->getCalendarPlan($this->getCategoryId()) : 0;
    }

    public function getNotCalendarPlan()
    {
        return isset($this->_budget) ?
            $this->_budget->getNotCalendarPlan($this->getCategoryId()) : 0;
    }

    public function getFact()
    {
        return isset($this->_budget) ?
            $this->_budget->getFact($this->getCategoryId()) : 0;
    }

    public function getThreeMonthMean()
    {
        return isset($this->_budget) ?
            $this->_budget->getThreeMonthMean($this->getCategoryId()) : 0;
    }

    public function getData()
    {
        $data = parent::getData();
        $data['plan'] = $this->getPlan();
        $data['fact'] = $this->getFact();
        $data['calendar_plan'] = $this->getCalendarPlan();
        $data['not_calendar_plan'] = $this->getNotCalendarPlan();
        $data['three_month_mean'] = $this->getThreeMonthMean();
        return $data;
    }

    public function setBudget(Budget $budget)
    {
        if (!$this->_budget)
            $this->_budget = $budget;
    }
}
