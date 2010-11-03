<?php
/**
 * Данные для вывода виджета счетов res.budget
 *
 * @param  array $budgetCategories список статей бюджета
 * @param  array $returnJSON флаг JSON или res
 */

$budget = array(
    'list' => array(
        'd' => array(),
        'p' => array()
    )
);

foreach ($budgetCategories as $budgetArticle) {
    $drainOrProfit = $budgetArticle->getType() == BudgetCategory::TYPE_EXPENCE
        ? 'd' : 'p';
    $budget['list'][$drainOrProfit][$budgetArticle->getCategoryId()] = array(
        'mean' => $budgetArticle->mean,
        'plan' => $budgetArticle->plan,
        'adhoc' => $budgetArticle->adhoc,
        'calendar_accepted' => $budgetArticle->calendarAccepted,
        'calendar_future' => $budgetArticle->calendarFuture,
    );
}

?>
<?php if (!$returnJSON) : ?>
    res.budget = <?php echo json_encode($budget) ?>;
<?php else : ?>
    <?php echo json_encode($budget) ?>
<?php endif; ?>
