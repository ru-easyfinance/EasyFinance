<?php
/**
 * Будущие операции из календаря (из запланированных событий)
 *
 * @param array $futureOperations Инф. об операциях и связанных с ними событиями календаря
 */
?>

res.calendar.future = <?php echo json_encode($futureOperations) ?>;
