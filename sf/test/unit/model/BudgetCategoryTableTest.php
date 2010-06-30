<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Таблица бюджета
 */
class model_BudgetCategoryTableTest extends myUnitTestCase
{
    /**
     * Посчитать итоговую сумму расходов на текущий месяц
     */
    public function testBudgetExpenseTotal()
    {
        $user = $this->helper->makeUser();

        // OK
        $budget1  = $this->helper->makeBudgetCategory($user, array('drain' => 1));
        $budget2  = $this->helper->makeBudgetCategory($user, array('drain' => 1));

        // No
        // доход
        $budget3 = $this->helper->makeBudgetCategory($user, array('drain' => 0));
        // другой месяц
        $budget4 = $this->helper->makeBudgetCategory($user, array('drain' => 1, 'date_start' => date('Y-m-01', time()-40*24*60*60)));
        // другой пользователь
        $budget5 = $this->helper->makeBudgetCategory(null, array('drain' => 1));


        $result = Doctrine::getTable('BudgetCategory')->countTotalExpense($user->getId());
        $this->assertEquals($budget1->getAmount() + $budget2->getAmount(), $result, 'Count budget', $delta = 0.01);

        // Нет бюджета
        $result = Doctrine::getTable('BudgetCategory')->countTotalExpense(999);
        $this->assertEquals(0, $result, 'No budget');
    }
}
