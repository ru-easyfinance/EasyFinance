<?php
/**
 * Бюджет за период
 */
class Budget
{
    /**
     * Фактический расход по категориям
     * @var array
     */
    private $_fact = array();
    /**
     * Средний расход по категориям
     * @var array
     */
    private $_threeMonthMean = array();
    /**
     * Запланированный расход по категориям
     * @var array
     */
    private $_calendarPlan = array();
    /**
     * Незапланированный расход по категориям
     * @var array
     */
    private $_notCalendarPlan = array();

    /**
     * Загружает список статей бюджета
     * @param User $user
     * @param string $startDate
     * @param float $rate курс пользовательской валюты
     */
    public function load($user, $startDate, $rate = 1)
    {
        $plan = Doctrine::getTable('BudgetCategory')
            ->getBudget($user, $startDate);

        $this->_threeMonthMean = Doctrine::getTable('Operation')
            ->getMeanByCategory($user, $startDate, $rate);

        $this->_fact = Doctrine::getTable('Operation')
            ->getFactByCategory($user, $startDate, $rate);

        $this->_calendarPlan = Doctrine::getTable('Operation')
            ->getCalendarPlanByCategory($user, $startDate, $rate);

        $this->_notCalendarPlan = Doctrine::getTable('Operation')
            ->getNotCalendarPlanByCategory($user, $startDate, $rate);

        $budgetCategories = array();

        foreach ($plan as &$budgetCategory) {
            $budgetCategory->setBudget($this);
            $budgetCategories[$budgetCategory->getCategoryId()]
                = $budgetCategory;
        }

        foreach ($this->_fact as $categoryId => $factValue) {
            if (!isset($budgetCategories[$categoryId])) {
                $budgetCategory = new BudgetCategory();
                $budgetCategory->setArray(
                    array(
                        'category_id' => $categoryId,
                        'amount'      => 0,
                        'user_id'     => $user->getId(),
                        'type'        => $factValue < 0 ? '1' : '0'
                    )
                );
                $budgetCategory->setBudget($this);
                $budgetCategories[$categoryId] = $budgetCategory;
            }
        }

        return $budgetCategories;
    }

    public function getFact($categoryId)
    {
        return isset($this->_fact[$categoryId]) ?
            abs($this->_fact[$categoryId]) : 0;
    }

    public function getCalendarPlan($categoryId)
    {
        return isset($this->_calendarPlan[$categoryId]) ?
            $this->_calendarPlan[$categoryId] : 0;
    }

    public function getNotCalendarPlan($categoryId)
    {
        return isset($this->_notCalendarPlan[$categoryId]) ?
            $this->_notCalendarPlan[$categoryId] : 0;
    }

    public function getThreeMonthMean($categoryId)
    {
        return isset($this->_threeMonthMean[$categoryId]) ?
            abs($this->_threeMonthMean[$categoryId]) : 0;
    }
}
?>