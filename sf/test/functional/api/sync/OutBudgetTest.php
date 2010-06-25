<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * Синхронизация: отдать бюджет
 */
class api_sync_OutBudgetTest extends myFunctionalTestCase
{
    protected $app = 'api';


    /**
     * Расходы на текущий месяц
     */
    public function testExpense()
    {
        $budget = $this->helper->makeBudgetCategory(null, array('drain' => 1));

        $this->browser
            ->getAndCheck('sync', 'syncOutBudget', $this->generateUrl('sync_get_budget', array(
                'user_id' => $budget->getUserId())), 200)
            ->with('response')->checkElement('budget expense', (string)$budget->getAmount());
    }

}
