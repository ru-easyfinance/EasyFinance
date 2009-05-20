<?
/**
* file: reg.php
* author: Roman Korostov
* date: 09/03/07
**/
echo "<!-- reg -->";
$tpl->assign('name_page', 'reg');

if (!empty($g_id))
{

	$id = html($g_id);

	$sql = "SELECT `user_id`, `reg_id` from `registration` WHERE `reg_id` = '".$id."'";
	$result = $db->sql_query($sql);

	$row = $db->sql_fetchrow($result);
	$user_id = $row['user_id'];

	if ($db->sql_numrows() == 1)
	{
		$sql = "DELETE FROM `registration` WHERE `reg_id` = '".$id."'";
		if (!$result = $db->sql_query($sql))
		{
			message_error(CRITICAL_ERROR, 'Ошибка в регистрации пользователя!', '', __LINE__, __FILE__, $sql);
		}

		$sql = "UPDATE `users` SET `user_active` = '1', `user_new` = '0' WHERE `user_id` = '".$user_id."'";
		if (!$result = $db->sql_query($sql))
		{
			message_error(CRITICAL_ERROR, 'Ошибка в регистрации пользователя!', '', __LINE__, __FILE__, $sql);
		}

		//FIXME
        $sql = "INSERT INTO forum_User (`RoleID`,`Name`,`Password`,`Email`,`user_id`)
            SELECT '3', `user_login`, `user_pass`, `user_mail`, `user_id` FROM users WHERE user_id='{$user_id}'";
	    if (!$result = $db->sql_query($sql))
        {
            message_error(CRITICAL_ERROR, 'Ошибка в регистрации пользователя!', '', __LINE__, __FILE__, $sql);
        }

        $last_id = mysql_insert_id();

		$sql = "INSERT INTO forum_UserRoleHistory VALUES('{$last_id}','3',NOW(),'0','auto','');";
	    if (!$result = $db->sql_query($sql))
        {
            message_error(CRITICAL_ERROR, 'Ошибка в регистрации пользователя!', '', __LINE__, __FILE__, $sql);
        }

		$tpl->assign('good_activation', 'Вы успешно активированы на сайте!');
	}
	else
	{
		message_error(GENERAL_MESSAGE, 'Ключ не верен, или он устарел!');
	}
}

$action = html($g_action);

if ($action == 'new_user')
{
	$registery['name'] = html($p_registery['name']);
	if (!empty($p_registery['pass']) && !empty($p_registery['pass_r']))
	{
		if ($p_registery['pass'] == $p_registery['pass_r'])
		{
			$pass = md5($p_registery['pass']);
		}else{
			$error_text['pass'] = "Пароли не совпадают!";
		}
	}else{
		$error_text['pass'] = "Введите пароль!";
	}

	if (validate_login($p_registery['login']))
	{
		$registery['login'] = $p_registery['login'];
	}else{
		$error_text['login'] = "Неверно введен логин! Логин может содержать только латинские буквы и цифры!";
		$registery['login'] = html($p_registery['login']);
	}

	if (!validate_email($p_registery['mail']))
	{
		$error_text['mail'] = "Неверно введен e-mail!";
		$registery['mail'] = html($p_registery['mail']);
	}else{
		$registery['mail'] = $p_registery['mail'];
	}

	$sql = "SELECT `user_login` from `users` WHERE `user_login` = '".$registery['login']."'";

	$result = $db->sql_query($sql);

	if ($db->sql_numrows() > 0)
	{
		$error_text['login'] = "Пользователь с таким логином уже существует!";
	}

	if (empty($error_text))
	{
		$id = md5($_SERVER['REMOTE_ADDR'].";".date("Y-m-d h-i-s").";");
		$datetime = date("Y-m-d");
		$reg = md5($registery['mail'].";".date("Y-m-d h-i-s").";");

			$sql = "
					INSERT INTO `registration`
						(`user_id`, `date`, `reg_id`)
					VALUES
						('".$id."', '".$datetime."', '".$reg."')
					";

			if ( !($result = $db->sql_query($sql)) )
			{
				message_error(CRITICAL_ERROR, 'Ошибка регистрации пользователя!', '', __LINE__, __FILE__, $sql);
			}
			else
			{

				$sql = "INSERT INTO `users`
									(`user_id`, `user_name`, `user_login`, `user_pass`, `user_mail`, `user_created`, `user_active`, `user_new`)
								VALUES
									('".$id."', '".$registery['name']."', '".$registery['login']."', '".$pass."', '".$registery['mail']."', '".$datetime."', '0', '1')";

				if ( !($result = $db->sql_query($sql)) )
				{
					message_error(CRITICAL_ERROR, 'Ошибка регистрации пользователя!', '', __LINE__, __FILE__, $sql);
				}
				else
				{
					$tpl->assign('good_text', 'На почту было отправлено письмо с кодом для подтверждения регистрации!');

					/*$body = "<html><head><title>From home-money.ru</title></head>
								<body>Здравствуйте, ".$registery['name']."!<p>Поздравляем! Вы зарегистрированы в системе <a href=http://home-money.ru>Home-money</a>.</p><p>Для входа в систему используйте:<br>
Логин: ".$registery['login']."<br>
Пароль: ".$p_registery['pass']."</p>
<p>Чтобы активировать после регистрации свою учетную запись, перейдите по ссылке:
<a href=".URL_ROOT."/index.php?modules=reg&id=".$reg.">".URL_ROOT."/index.php?modules=reg&id=$reg</a></p></body>
								</html>";*/

					$body = "<html><head><title>Подтверждение регистрации на сайте домашней бухгалтерии Home-Money.ru</title></head>
								<body><p>Здравствуйте, ".$registery['name']."!</p>
								<p>Ваш e-mail был введен при регистрации в системе.<br>
								Чтобы завершить регистрацию и активировать свою учетную запись, перейдите по ссылке:</p>
								<p><a href=".URL_ROOT."/index.php?modules=reg&id=".$reg.">".URL_ROOT."/index.php?modules=reg&id=$reg</a></p>

								<p>Для входа в систему используйте:<br>
								Логин: ".$registery['login']."<br>
								Пароль: ".$p_registery['pass']."</p>

								<p>C уважением,<br>
								Администрация системы <a href=".URL_ROOT."/>Home-money.ru</a>
								</body>
								</html>";

					$subject = "Подтверждение регистрации на сайте домашней бухгалтерии Home-Money.ru";
					$message = "<html><head><title>From home-money.ru</title></head>
								<body>
									<a href=".URL_ROOT."/index.php?modules=reg&id=".$reg.">".URL_ROOT."/index.php?modules=reg&id=$reg</a>
								</body>
								</html>";
					$headers = "Content-type: text/html; charset=utf-8\n";
					$headers .= "From: info@home-money.ru\n";
					mail($registery['mail'], $subject, $body, $headers);
				}
			}
	}else{
		$tpl->assign('error_text', $error_text);
	}
	$tpl->assign('registery', $registery);
}

?>