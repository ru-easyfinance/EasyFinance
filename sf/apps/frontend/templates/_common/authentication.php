<div id="authentication">
    <form action="/login/" method="post" onSubmit="return checkLogin()">
        <table cellpadding="0" cellspacing="0">
            <tr>
                <td class="name"><label for="flogin" class="efTooltip" title="Имя Вашего аккаунта на сайте">Логин или e-mail:</label></td>
                <td><input id="flogin" type="text" name="login" class="efTooltip" title="Имя Вашего аккаунта на сайте"/></td>
            </tr>
            <tr>
                <td class="name"><label for="pass">Пароль:</label></td>
                <td><input type="password" id="pass" name="pass" /></td>
            </tr>
            <tr>
                <td class="name"><label for="autoLogin">Запомнить меня:</label></td>
                <td><input type="checkbox" class="forget" value="1" name="autoLogin" id="autoLogin" checked="checked"/></td>
            </tr>
            <tr>
                <td></td>
                <td><input id="btnLogin" type="submit" value="Войти" class="but"/></td>
            </tr>
            <tr id="authRegisterRestore">
                <td class="name"></td>
                <td>
                    <a href="/registration/">Регистрация</a><br>
                    <a href="/restore/">Восстановление пароля</a>
                </td>
            </tr>
        </table>
    </form>
</div>
