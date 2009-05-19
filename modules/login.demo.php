<?
/**
* file: login.php
* author: Roman Korostov
* date: 23/01/07	
**/

$tpl->assign('name_page', 'login.demo');


//$user->deleteOldRegisterRecord();

if (!empty($_SESSION['user']))
{
	header("Location: index.php?modules=account");
}
else
{
	if (!empty($p_login) && !empty($p_pass))
	{
		$login = html($p_login);
		$pass = html($p_pass);
		
		if ($_POST['autoLogin'])
		{
			setcookie("autoLogin", $login, time() + 1209600);
			setcookie("autoPass", $pass, time() + 1209600);
		}

		if ($user->initUser($login,$pass))
		{
			if (empty($_SESSION['user_category']))
			{
				if($user->getCategory($user->getId()))
				{
					header("Location: index.php?modules=category");
				}	
				else
				{
					message_error(GENERAL_ERROR, "Справочник не загружен!");
				}
			}else{
				$prt->getInsertPeriodic($user->getId());
				$user->init($user->getId());
				$user->save($user->getId());			
				if ($_SESSION['template_new'] == 'on')
				{
					header("Location: index.php?modules=accounts");
				}else{
					header("Location: index.php?modules=account");
				}
			}
		}
	}
}
?>