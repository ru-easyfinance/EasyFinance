<script type="text/javascript">
  res.profile.integration.email = "<?php
    // TODO как-то это не кошерно, надо придумать как делать res динамическим
    echo str_replace('@mail.easyfinance.ru', '', $sf_user->getUserRecord()->getUserServiceMail());
    ?>";
</script>

<ul class="menuProfile">
    <li id="i3" class="act" block="#profile">
        <div><a>Профиль</a></div>
    </li>
    <li id="i4" block="#currency">
        <div><a>Настройка валют</a></div>
    </li>
    <li id="i6" block="#reminders">
        <div><a>Уведомления</a></div>
    </li>
</ul>

<?php // форма профиля ?>
<div class="ramka3 profile" id="profile">
    <div class="ct">
        <div class="head">
            <h2>Личные данные</h2>
            <ul class="action"><li class="over3"> </li></ul>
        </div>
    </div>
    <div id="content" class="inside form">
        <div class="formRegister">
            <table>
                <tr>
                    <td><label for="login">Логин:</label></td>
                    <td><input id="login" name="login" class="disabled" type="text" value="" disabled="disabled" /></td>
                </tr>
                <tr>
                    <td><label for="mailIntegration">E-Mail для интеграции:</label></td>
                    <td><input id="mailIntegration" name="integration" type="text" value="" />@mail.easyfinance.ru</td>
                </tr>
                <tr>
                    <td><label for="name">Псевдоним:</label></td>
                    <td><input id="name" name="name" type="text" value="<?php echo $sf_user->getName() ?>" /></td>
                </tr>
                <tr>
                    <td colspan="2"><h3>Смена пароля</h3></td>
                </tr>
                <tr>
                    <td><label for="newpass">Новый пароль:</label></td>
                    <td><input id="newpass" name="newpass" type="password" value="" /><em class="red">*</em></td>
                </tr>
                <tr>
                    <td><label for="newpass2">Повтор пароля:</label></td>
                    <td><input id="newpass2" type="password" value="" /></td>
                </tr>
                <tr>
                    <td colspan="2"><h3>Смена e-mail</h3></td>
                </tr>
                <tr>
                    <td><label for="mail">e-mail:</label></td>
                    <td><input id="mail" type="text" name="mail" value="" /><em class="red">*</em></td>
                </tr>
                <tr>
                    <td><label for="guide">Включить гид</label></td>
                    <td><input id="guide" name="guide" class="chek" type="checkbox"<?php echo $sf_request->getCookie('guide', false) ? ' checked="checked"' : '' ?> /></td>
                </tr>
                <tr>
                    <td><label for="getNotify">Получать уведомления об изменениях сервиса на электронную почту</label></td>
                    <td><input id="getNotify" name="getNotify" class="chek" type="checkbox" checked="checked" /></td>
                </tr>
                <tr>
                    <td><label for="help">Включить подсказки</label></td>
                    <td><input id="help" name="help" class="chek" type="checkbox" checked="checked" /></td>
                </tr>
                <tr>
                    <td colspan="2"><h3><div> </div></h3></td>
                </tr>
                <tr>
                    <td><label for="pass">Текущий пароль:</label></td>
                    <td><input id="pass" name="pass" type="password" value="" title="Введите пароль, если Вам нужно сменить старый пароль, или почтовый адрес." /></td>
                </tr>
                <tr>
                    <td colspan="2"><h6>* Введите пароль, если Вам нужно сменить старый пароль, или почтовый адрес.</h6></td>
                </tr>
                <tr>
                    <td> </td>
                    <td><button id="save_info">Сохранить</button></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="cb">
        <div> </div>
    </div>
</div>


<?php // валюты ?>
<div id="currency" class="ramka3 profile" style="display:none">
    <div class="ct">
        <div class="head">
            <h2>Валюты</h2>
            <ul class="action"><li class="over3"> </li></ul>
        </div>
    </div>
    <div id="content" class="inside form">
        <div class="col second">
            <h4>Все валюты</h4>
            <ul class="currency all"> </ul>
        </div>
        <div class="col first">
            <h4>Валюты пользователя</h4>
            <ul class="currency user"> </ul>
        </div>
        <br class="clr" />
        <div class="formRegister">
            <table>
                <tr>
                    <td><label>Валюта по умолчанию:</label></td>
                    <td><select id="def_cur"></select></td>
                </tr>
                <tr>
                    <td> </td>
                    <td><button id="save_cur">Сохранить</button></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="cb">
        <div> </div>
    </div>
</div>


<?php // напоминания ?>
<div id="reminders" class="ramka3 profile" style="display:none">
    <div class="ct">
        <div class="head">
            <h2>Напоминания для запланированных операций</h2>
            <ul class="action"><li class="over3"> </li></ul>
        </div>
    </div>
    <?php include_partial('reminders') ?>
    <div class="cb">
        <div> </div>
    </div>
</div>
