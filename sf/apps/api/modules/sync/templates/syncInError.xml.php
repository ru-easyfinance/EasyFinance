<?php
/**
 * Шаблон для вывода ошибок
 *
 * @param  int    $code
 * @param  string $message
 */
decorate_with('layout.xml.php');

$xmlCode = isset($code) ? sprintf(' code="%d"', $code) : '';
?>

<error<?php echo $xmlCode; ?>><?php echo $message; ?></error>
