<?php
/**
 *
 */
class Budget {

    /**
     * @var DateTime
     */
    protected $_startDate;

    /**
     * @var array
     */
    protected $_budgetArticles;

    /**
     * Наполняет статьи бюджета на указанный месяц
     * @param DateTime $startDate
     * @return void
     */
    public function fill(DateTime $startDate)
    {
        $this->_startDate = $startDate;
    }


    public function getStartDate()
    {
        return $this->_startDate;
    }


    public function getBudgetArticleByCategory(Category $category)
    {
        Doctrine::getTable('BudgetCategory')
-            ->getBudget($user, $startDate);
    }
}