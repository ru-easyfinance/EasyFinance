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

    /**
     * Посчитать получить бюджет на текущий месяц
     */
    public function testQueryLoadBudget()
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

        $data = Doctrine::getTable('BudgetCategory')->getBudget($user, $dateStart);

        $expected = array($budget1->getAmount(), $budget2->getAmount());
        $actual   = array($data[0]->getAmount(), $data[1]->getAmount());

        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Итоговая сумма планируемых расходов на текущий месяц - всегда число
     */
    public function testBudgetExpenseTotalIsFloat()
    {
        $user = $this->helper->makeUser();
        $result = Doctrine::getTable('BudgetCategory')->countTotalExpense($user->getId());

        $this->assertNotNull($result, 'Budget is not empty');
        $this->assertType('float', $result);
    }

}
