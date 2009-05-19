<?
/**
* file: profile.php
* author: Roman Korostov
* date: 13/04/07	
**/

if (empty($_SESSION['user']))
{
	header("Location: index.php");
	exit;
}

$tpl->assign('name_page', 'profile');

$action = html($g_action);

switch( $action )
{
	case "edit":
		if (!empty($p_profile))
		{
			if (validate_email($p_profile['mail']))
			{
				$profile['user_mail'] = $p_profile['mail'];
			}else{
				$error_text['mail'] = "E-mail введен неверно!";
			}
			$profile['user_login'] = html($p_profile['login']);
			$profile['user_name'] = html($p_profile['name']);
		}
		
		$new_passwd = false;
		
		if (!empty($p_pass) || !empty($p_pass_r))
		{
			if ($p_pass == $p_pass_r)
			{
				$profile['pass'] = $p_pass;
				$new_passwd = md5($p_pass);
			}else{
				$error_text['pass'] = "Пароли не совпадают!";
			}
		}
		
		if (empty($error_text))
		{				
			if($user->updateProfile($new_passwd, $profile['user_name'], $profile['user_mail'], $profile['user_login']))
			{
				$tpl->assign('good_text', "Профиль изменен!");
			}
			$tpl->assign('good_text', "Профиль изменен!");
		}else{
			$tpl->assign('error_text', $error_text);				
		}
		
		$tpl->assign("profile",$profile);
		break;
	default:
		$tpl->assign("page_title","profile view");		
		$profile = $user->getProfile($user->getId());
		$tpl->assign("profile",$profile);
		break;
}
?>