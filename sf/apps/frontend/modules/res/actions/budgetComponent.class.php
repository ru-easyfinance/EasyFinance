<?php
/**
 * Готовит js объект res.budget
 */
class budgetComponent extends sfComponent
{
    /**
     * Execute
     *
     * @param sfRequest $request A request object
     */
    public function execute($request)
    {
        $user = $this->getUser()->getUserRecord();
        $start = $this->getVar('start') ? $this->getVar('start')
            : $request->getParameter('start', date('Y-m-01'));
        $start = preg_replace("/(\d{2})\.(\d{2})\.(\d{4})/", "$3-$2-$1", $start);

        $returnJSON = $this->getVar('returnJSON') ? true : false;

        $budget = new Budget;
        $rate = $this->getContext()
            ->getMyCurrencyExchange()->getRate($user->getCurrencyId());
        $budgetCategories = $budget->load($user, $start);

        $this->setVar('budgetCategories', $budgetCategories, $noEscape = true);
        $this->setVar('returnJSON', $returnJSON);
    }

}
