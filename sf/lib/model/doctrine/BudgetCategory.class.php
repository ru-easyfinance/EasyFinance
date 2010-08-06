<?php

/**
 * BudgetCategory
 */
class BudgetCategory extends BaseBudgetCategory
{
    /**
     * Фактический расход
     * @var float
     */
    private $_fact;
    /**
     * Средний расход за последние 3 месяца
     * @var float
     */
    private $_threeMonthMean;
    /**
     * Бюджет
     * @var Budget
     */
    private $_budget = null;

    public function getPlan()
    {
        return $this->getAmount();
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
    
    public function setBudget(Budget $budget)
    {
        if (!$this->_budget)
            $this->_budget = $budget;
    }
}
