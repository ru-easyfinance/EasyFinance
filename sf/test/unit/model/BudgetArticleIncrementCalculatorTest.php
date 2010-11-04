<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Калькулятор прибавки к статье бюджета
 */
class model_BudgetArticleIncrementCalculatorTest extends myUnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        $yamlPath = dirname(__FILE__) . '/fixtures/budget.yml';
        Doctrine::loadData($yamlPath);
    }

    /**
     * Прибавление к статье бюджета
     */
    public function testApplyIncrement()
    {
        $increment = new BudgetArticleIncrement();
        $budgetArticle = new BudgetCategory();

        $budgetArticle->mean = 1;
        $budgetArticle->adhoc = 2;
        $budgetArticle->calendarFuture = 0.4;
        $budgetArticle->calendarAccepted = 0.8;
        $budgetArticle->amount = 16;

        $increment->mean = 0.1;
        $increment->adhoc = 0.2;
        $increment->calendarFuture = 4;
        $increment->calendarAccepted = 8;

        $increment->apply($budgetArticle);

        $this->assertEquals(1.1, $budgetArticle->mean);
        $this->assertEquals(2.2, $budgetArticle->adhoc);
        $this->assertEquals(4.4, $budgetArticle->calendarFuture);
        $this->assertEquals(8.8, $budgetArticle->calendarAccepted);
        $this->assertEquals(16,  $budgetArticle->amount);
    }
}
