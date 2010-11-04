<?php
/**
 * Данные для вывода виджета бюджета
 *
 * @param  array $budgetCategories список статей бюджета
 * @param  array $returnJSON флаг JSON или res
 */
$budget = array();

foreach ($budgetCategories as $budgetArticle) {
    $drainOrProfit = $budgetArticle->getType() == BudgetCategory::TYPE_EXPENSE ? 'd' : 'p';

    foreach(array('mean', 'plan', 'adhoc', 'calendarAccepted', 'calendarFuture') as $field) {
        $budget[$budgetArticle->getCategoryId()][$field] = floatval($budgetArticle->$field);
    }
    
    $budget[$budgetArticle->getCategoryId()]['type'] = $drainOrProfit;
}

echo json_encode($budget);
