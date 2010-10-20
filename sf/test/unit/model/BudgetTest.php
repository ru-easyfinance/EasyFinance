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


        $account = $this->helper->makeAccount($user);
        $cc      = $this->helper->makeCalendarChain($account);
        // Запланировали в календаре, потратили
        $op1     = $this->helper->makeCalendarOperation(
            $cc,
            $account,
            '',
            0,
            array(
                'amount'   => 100,
                'accepted' => 1,
                'category_id' => $category1->getId()
            )
        );
        // Потратили без календаря
        $op2      = $this->helper->makeOperation(
            $account,
            array(
                'amount'   => 200,
                'accepted' => 1,
                'category_id' => $category1->getId()
            )
        );
        // Запланировали в календаре, не потратили
        $op3      = $this->helper->makeCalendarOperation(
            $cc,
            $account,
            '',
            0,
            array(
                'amount'   => 50,
                'accepted' => 0,
                'category_id' => $category1->getId()
            )
        );

        $budget = new Budget();
        $data = $budget->load($user, $dateStart);

        $this->assertEquals(
            $budget1->getAmount(),
            $data[$budget1->getCategoryId()]->getAmount()
        );

        $this->assertEquals(
            150,
            $data[$budget1->getCategoryId()]->getCalendarPlan(),
            'Сумма по запланированным в календаре операциям'
        );

        $this->assertEquals(
            200,
            $data[$budget1->getCategoryId()]->getNotCalendarPlan(),
            'Сумма по незапланированным, но подтверждённым операциям'
        );

        $this->assertEquals($budget2->getAmount(),
            $data[$budget2->getCategoryId()]->getAmount());
    }
}
