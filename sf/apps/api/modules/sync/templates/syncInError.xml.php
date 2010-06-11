<?php
/**
 * Ошибки валидации
 *
 * @param  array $error    массив ошибка и код ошибки
 */
?>
<error>
    <code><?php echo $error['code'] ?></code>
    <message><?php echo $error['message'] ?></message>
</error>
