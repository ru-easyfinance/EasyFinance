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
     * Загружает список статей бюджета
     * @param User $user 
     * @param string $startDate 
     */
    public function load($user, $startDate) 
    {        
        $plan = Doctrine::getTable('BudgetCategory')
            ->getBudget($user, $startDate);

        $this->_threeMonthMean = Doctrine::getTable('Operation')
            ->getMeanByCategory($user, $startDate);

        $this->_fact = Doctrine::getTable('Operation')
            ->getFactByCategory($user, $startDate);

        $budgetCategories = array();

        foreach ($plan as &$budgetCategory) {
            $budgetCategory->setBudget($this);
            $budgetCategories[$budgetCategory->getCategoryId()]
                = $budgetCategory;
        }
        
        foreach ($this->_fact as $categoryId => $factValue) {
            if (isset($budgetCategories[$categoryId])) {
                $budgetCategory = new BudgetCategory();
                $budgetCategory->setArray(
                    array(
                        'category_id' => $categoryId,
                        'amount'      => 0,
                        'user_id'     => $user->getId(),
                        'drain'       => $factValue < 0 ? '1' : '0'
                    )
                );
                $budgetCategories[$categoryId] = $budgetCategory;
            }
        }
        
        return $budgetCategories;
    }
    
    public function getFact($categoryId)
    {
        return isset($this->_fact[$categoryId]) ?
            $this->_fact[$categoryId] : 0;
    }
    
    public function getThreeMonthMean($categoryId)
    {
        return isset($this->_threeMonthMean[$categoryId]) ?
            $this->_threeMonthsMean[$categoryId] : 0;
    }
}
?>