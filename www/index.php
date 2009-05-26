<?php
/**
* file: index.php
* author: Roman Korostov
* date: 23/01/07
**/

if ($_GET['et']=='on')
{
echo "На сайте проводятся технические работы. Зайдите позже.";
exit;
}

global $modules;

require_once ("../include/common.hm.php");

//если в гете пришел модуль
if (!empty($g_modules))
{
	//проверим его
	$is_module = html($g_modules);

	//если модуль проверен и существует, то загружаем его
	if (isset($is_module) && $is_module != "")
	{

		if (checkModules($is_module))
		{
			require_once (SYS_DIR_MOD."/".$is_module.".php");
		}
		else
		{
			message_error(GENERAL_ERROR, "ModFile '".$is_module."' is not exists!");
		}
	}
	else
	{
		message_error(GENERAL_ERROR, "ModFile '".$is_module."' is not exists!");
	}
}else{
	if (checkModules(DEFAULT_MODULE))
	{
		require_once (SYS_DIR_MOD."/".DEFAULT_MODULE.".php");
	}
	else
	{
		message_error(GENERAL_ERROR, "ModFile '".DEFAULT_MODULE."' is not exists!");
	}
}

if (!empty($_SESSION['user']))
{
	$tpl->assign("user", $_SESSION['user']);
	$tpl->display("index.hm.html");
}
else{
	$tpl->display("index.hm.html");
	//$tpl->display("auth2.html");
	//$tpl->display("welcam.html");
}

?>