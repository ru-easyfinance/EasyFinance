<?
/**
* file: reg.php
* author: Roman Korostov
* date: 09/03/07	
**/

$tpl->assign('name_page', 'registration.demo');


if (html($_GET['action']) == 'new_user')
{	
	if (!empty($_POST['login']))
	{
		$login = html($_POST['login']);
		$pass = html($_POST['pass']);
		$mail = $_POST['mail'];

		$id = md5($_SERVER['REMOTE_ADDR'].";".date("Y-m-d h-i-s").";");
		
		$sql = "INSERT INTO `users` 
						(`user_id`, `user_name`, `user_login`, `user_pass`, `user_mail`, `user_created`, `user_active`, `user_new`)
						VALUES
						('".$id."', 'DEMO', '".$login."', '".$pass."', '".$mail."', '".date("Y-m-d")."', '1', '0')";
	
		if ( !($result = $db->sql_query($sql)) )
		{
			message_error(CRITICAL_ERROR, 'Ошибка регистрации пользователя!', '', __LINE__, __FILE__, $sql);
		}
		else
		{			
			$user->getDemoOperations($id);
			
			$body = "<html><head><title>From home-money.ru</title></head>
					 <body>
					 <p>Уважаемый пользователь!</p>

<p>Вы зарегистрировали демонстрационный аккаунт в домашней бухгалтерии <a href='http://www.home-money.ru'>Home-money</a>.</p>

<p>Наш сервис <a href='http://www.home-money.ru'>Home-money</a> позволит Вам вести учет Ваших личных финансов или финансов Вашего малого бизнеса.
Также сервис <a href='http://www.home-money.ru'>Home-money.ru</a>  доступен с мобильных телефонов и коммуникаторов (специальная PDA версия).
Для доступа в PDA версию просто наберите <a href='http://www.home-money.ru'>Home-money.ru</a> в браузере Вашего коммуникатора или телефона (сервис распознает PDA режим автоматически). Внимание! Сервис доступен только зарегистрированным пользователям.
</p>
<p>
Получить постоянный бесплатный доступ к системе для финансового планирования Home-Money.ru можно в любое время, пройдя несложную <a href='http://www.home-money.ru/index.php?modules=reg'>процедуру регистрации.</a>
</p>
<p>
Надеемся наш сервис принесет Вам пользу!
</p>
<p>
Администрация<br>
<a href='http://www.home-money.ru'>Home-money.ru</a></p>
					 </body>
					 </html>";
			
			$subject = "Демо-регистрация в home-money.ru";
			$message = "<html><head><title>From home-money.ru</title></head>
						<body>
							".$body."
						</body>
						</html>";
			$headers = "Content-type: text/html; charset=utf-8\n";
			$headers .= "From: reg@home-money.ru\n";
			mail($mail, $subject, $body, $headers);
			
			if ($user->initUser($login,$pass))
			{
				header("Location: index.php?modules=account");	
			}
		}
	}
	
	$new_account['login'] = $user->demoNewUser();
	$new_account['pass'] = substr(md5(microtime()), 0, 6);
	
	$tpl->assign('new_account', $new_account);
}

?> 