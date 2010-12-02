<form method="post" action="<?php echo URL_ROOT ?>login/">
    <div class="b-login-form">
        <div class="b-login-form-row l-buttons-inline">
            <?php include_partial('global/common/ui/textfield', array('name' => 'login', 'placeholder' => 'логин/почта')) ?>
            <?php include_partial('global/common/ui/textfield', array('type' => 'password', 'name' => 'pass', 'placeholder' => 'пароль')) ?>
            <input class="b-button-login" type="submit" value="" title="Войти"/>
        </div>
        <div class="b-login-form-row second">
            <label><input type="checkbox" name="autoLogin" value="1" checked="checked" /> <span>Запомнить</span></label>
            <a href="<?php echo URL_ROOT ?>restore/">Забыли пароль?</a>
        </div>
        <div class="b-login-form-bg">
            <i class="l"></i>
            <i class="r"></i>
        </div>
    </div>
</form>