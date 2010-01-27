		<table cellspacing="0" cellpadding="0" class="wide"><tbody>
			<tr>
				<td class="wide"><a href="/">Система управления<br>личными финансами</a></td>
				<td class="logo"><a href="/"><img src="/img/logo.gif" alt="EasyFinance.ru" border="0" /></a></td>
			</tr>
		</tbody></table>
		<br />
		<?php if( isset($errorMessage) && $errorMessage ):?>
		<b style="color:red"><?=$errorMessage?></b>
		<?php endif; ?>
		<form action="/login/" method="post" >
		<table cellspacing="0" cellpadding="2" class="wide"><tbody>
			<tr>
				<td>Логин:</td>
				<td class="wide"><input id="txtlogin" name="login" class="wide" /></td>
			</tr>
			<tr>
				<td>Пароль:</td>
				<td class="wide"><input type="password" id="txtPassword" name="pass" class="wide" /></td>
			</tr>
		</tbody></table>
		<br>
		<table cellspacing="0" cellpadding="2" class="wide"><tbody>
			<tr>
				<td class="wide">
					<input id="checkRemember" name="autoLogin" type="checkbox"/>
					<label for="checkRemember">Запомнить&nbsp;меня</label>
				</td>
				<td>
					<input id="btnLogin" type="submit" value="Вход" />
				</td>
			</tr>
		</tbody></table>
		</form><br/>
		<table cellspacing="0" cellpadding="2" class="wide"><tbody>
			<tr>
				<td width="100%"><a href="/registration">Регистрация</a></td>
				<td><a href="/registration">Забыли&nbsp;пароль?</a></td>
			</tr>
		</tbody></table>