<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>
<?php if ($form->hasGlobalErrors()) : ?>
    <?php echo $form->renderGlobalErrors(); ?>
<?php endif; ?>
<form action="<?php echo url_for('login') ?>" method="post">
    <table cellspacing="0" cellpadding="2" class="wide">
        <tbody>
        <?php echo $form; ?>
        </tbody>
        <tfoot>
            <td colspan="2">
                <input type="submit" value="Войти" />
            </td>
        </tfoot>
    </table>
</form>
