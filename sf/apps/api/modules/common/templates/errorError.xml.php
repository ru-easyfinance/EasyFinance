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
<?php if (isset($subscribedTill)) : ?>
<paidUntil><?php echo date(DATE_ISO8601, strtotime($subscribedTill)); ?></paidUntil>
<paid_until><?php echo date(DATE_ISO8601, strtotime($subscribedTill)); ?></paid_until>
<?php endif; ?>
<error<?php echo $xmlCode; ?>><?php echo $message; ?></error>
