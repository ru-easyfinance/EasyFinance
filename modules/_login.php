<?
/**
* file: login.php
* author: Roman Korostov
* date: 23/01/07	
**/

$tpl->assign('name_page', 'login');


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
		$pass = md5($p_pass);		
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
				header("Location: index.php?modules=account");
			}
		}
	}
}
?>