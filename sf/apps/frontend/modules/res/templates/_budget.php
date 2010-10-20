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

foreach ($budgetCategories as $budgetEntry) {
    $drainOrProfit = ($budgetEntry->getType()) ? 'd' : 'p';
    $budget['list'][$drainOrProfit][$budgetEntry->getCategoryId()] = array(
        'amount' => $budgetEntry->getAmount(),
        'money'  => $budgetEntry->getFact(),
        'mean'   => $budgetEntry->getThreeMonthMean(),
        'calendar_plan' => $budgetEntry->getCalendarPlan(),
        'not_calendar_plan' => $budgetEntry->getNotCalendarPlan()
    );
}

?>
<?php if (!$returnJSON) : ?>
    res.budget = <?php echo json_encode($budget) ?>;
<?php else : ?>
    <?php echo json_encode($budget) ?>
<?php endif; ?>
