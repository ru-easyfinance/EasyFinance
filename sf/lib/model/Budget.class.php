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
        $budgetArticles = Doctrine::getTable('BudgetCategory')
            ->getBudget($user, $startDate);

        foreach ($budgetArticles as $budgetArticle)
            $this->_budgetArticles[$budgetArticle->getCategoryId()] =
                $budgetArticle;
    }


    public function getStartDate()
    {
        return $this->_startDate;
    }


    public function getBudgetArticleByCategory(Category $category)
    {
        if (isset($this->_budgetArticles[$category->getId()]))
            return $this->_budgetArticles[$category->getId()];

        $budgetArticle = new BudgetCategory();

        $budgetArticle->setCategory($category);
        $budgetArticle->setType(
            $category->getType() == Category::TYPE_PROFIT ?
            BudgetCategory::TYPE_PROFIT :
            BudgetCategory::TYPE_EXPENCE
        );
        $budgetArticle->setUser($category->getUser());
        $budgetArticle->setDateStart($this->_startDate->format('Y-m-d'));

        return $budgetArticle;
    }
}