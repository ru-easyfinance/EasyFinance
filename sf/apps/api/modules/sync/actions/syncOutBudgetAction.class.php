<?php

/**
 * Sync: отдать бюджет на текущий месяц
 */
class syncOutBudgetAction extends sfAction
{
    /**
     * Execute
     */
    public function execute($request)
    {
        // Явно указать layout для всех форматов
        $this->setLayout('layout');

        $userId = $this->getUser()->getId();
        $total = Doctrine::getTable('BudgetCategory')->countTotalExpense($userId);

        $this->setVar('total', $total, $noEsc = true);
    }

}
