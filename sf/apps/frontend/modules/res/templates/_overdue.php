<?php
/**
 * Просроченные операции из календаря (из запланированных событий)
 *
 * @param array $overdueOperations Инф. об операциях и связанных с ними событиями календаря
 */
?>

res.calendar.overdue = <?php echo json_encode($overdueOperations) ?>;
