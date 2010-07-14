<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>
<?php if ($form->hasGlobalErrors()) : ?>
    <?php $errors = $form->getGlobalErrors(); ?>
    <?php foreach ($errors as $error) : ?>
        <b style="color:red"><?php echo $error; ?></b>
    <?php endforeach; ?>
<?php endif; ?>
<?php if ($form->hasErrors()) : ?>
    <b style="color:red">Ошибка авторизации</b>
<?php endif; ?>
<form action="<?php echo url_for('login') ?>" method="post">
    <table cellspacing="0" cellpadding="2" class="wide">
        <tbody>
        <?php foreach ($form as $k => $widget) : ?>
        <?php if ($k == 'remember') : ?>
           <?php continue; ?>
        <?php endif; ?>
            <tr>
                <td><?php echo $widget->renderLabel(); ?>:</td>
                <td class="wide">
                    <?php echo $widget->render(array('class' => 'wide')); ?>
                </td>
            </tr>
            <?php if ($form->hasErrors()) : ?>
                <tr>
                    <td>&nbsp;</td>
                    <td class="wide">
                        <b style="color:red"><?php echo $widget->renderError(); ?></b>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
    <table cellspacing="0" cellpadding="2" class="wide">
        <td class="wide">
            <?php echo $form['remember']->render(); ?>
            <?php echo $form['remember']->renderLabel(); ?>
        </td>
        <td>
            <input type="submit" value="Войти" />
        </td>
    </table>
</form>
