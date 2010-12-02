<div id="registration" class="formRegister" >
    <h2>Регистрация</h2>
    <form id="formRegister">
        <table cellpadding="0" cellspacing="0">
        <tr>
            <td colspan="2" style="color:#5e5e5e;padding-top: 5px">
                <em>Все поля обязательны для заполнения.</em>
            </td>
        </tr>
        <tr>
            <td class="name">Имя:</td>
            <td><input id="name" type="text" name="name" /></td>
        </tr>
        <tr>
            <td colspan="2" style="color:#5e5e5e;padding-top: 5px">
                <br/><i><b>Поля ниже заполняются латинскими буквами и цифрами без пробелов.</b></i>
            </td>
        </tr>
        <tr>
            <td class="name">Логин:</td>
            <td><input id="log" type="text" name="login" /></td>
        </tr>
        <tr>
            <td class="name">Пароль:</td>
            <td><input type="password" id="passw" name="passw" /></td>
        </tr>
        <tr>
            <td class="name">Повтор пароля:</td>
            <td><input id="confirm_password" type="password" name="confirm_password" /></td>
        </tr>
        <tr>
            <td colspan="2" style="color:#5e5e5e;padding-top: 5px">
                <br />
                <em><span style="color:red">Внимание!</span> Очень важно указать правильный, работающий email - он используется для восстановления пароля и обратной связи с Вами. Поэтому мы просим Вас ввести email дважды и проверить его правильность.</em>
            </td>
        </tr>
        <tr>
            <td class="name">E-mail:</td>
            <td><input id="mail" type="text" name="mail" /></td>
        </tr>
        <tr>
            <td class="name">Повтор e-mail`a:</td>
            <td><input id="mail_confirm" type="text" name="mail" /></td>
        </tr>
        <tr>
            <td></td>
            <td><br/>Нажимая на кнопку &laquo;Отправить&raquo; Вы подтверждаете свое согласие с условиями <a href="{$smarty.const.URL_ROOT_WIKI}tiki-view_blog_post.php?postId=41">договора оферты</a>.<br/>
            <input id="butt" class="butt" type="button" value="" /></td>
        </tr>
        </table>
    </form>
    <div id="lblRegistrationStatus" class="hidden">
        <strong>Ваша учётная запись создаётся - пожалуйста, подождите.</strong>
    </div>
</div>