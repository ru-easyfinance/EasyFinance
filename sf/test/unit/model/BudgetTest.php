<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Бюджет за период
 */
class model_BudgetTest extends myUnitTestCase
{
    /**
     * Посчитать бюджет на текущий месяц
     */
    public function testLoadBudget()
    {
        $user = $this->helper->makeUser();
        $dateStart = date('Y-m-01');
        //$this->helper = new myTestObjectHelper();

        $category1 = $this->helper->makeCategory($user);
        $category2 = $this->helper->makeCategory($user);
        // OK
        $budget1  = array(
            'drain' => 1,
            'category_id' => $category1->getId(),
            'date_start' => $dateStart
        );
        $budget2  = array(
            'drain' => 1,
            'category_id' => $category2->getId(),
            'date_start' => $dateStart
        );

        $budget1  = $this->helper->makeBudgetCategory($user, $budget1);
        $budget2  = $this->helper->makeBudgetCategory($user, $budget2);

        $budget = new Budget();
        $data = $budget->load($user, $dateStart);

        $this->assertEquals($budget1->getAmount(), 
            $data[$budget1->getCategoryId()]->getAmount());
        $this->assertEquals($budget2->getAmount(), 
            $data[$budget2->getCategoryId()]->getAmount());
    }
}
