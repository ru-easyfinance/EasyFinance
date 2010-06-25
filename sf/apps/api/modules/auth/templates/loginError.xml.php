<?php
/**
 * Ошибки валидации формы
 *
 * @param sfForm $form
 */
?>

<error>
    <message><?php echo $form ? $form->getErrorSchema() : 'Authentification required'; ?></message>
</error>
