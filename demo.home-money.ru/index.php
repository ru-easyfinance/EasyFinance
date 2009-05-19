<?php
/**
* file: index.php
* author: Roman Korostov
* date: 23/01/07	
**/



global $modules;

require_once ("../include/common.demo.php");

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
	
	list($day,$month,$year) = explode(".", $_SESSION['user']['user_created']);
	
	$finish_reg  = mktime(0, 0, 0, $month, $day+6, $year);

	$finish_reg = date("d.m.Y", $finish_reg);
	$today = date("d.m.Y");
	$all_work_day = $finish_reg - $today +1;
	
	if ($all_work_day == 1)
	{
		$finish_reg_day['all_work_day_text'] = "сегодня";
	}
	if ($all_work_day == 2)
	{
		$finish_reg_day['all_work_day_text'] = "завтра";
	}
	if ($all_work_day == 3)
	{
		$finish_reg_day['all_work_day_text'] = "послезавтра";
	}
	
	$finish_reg_day['today'] = $today;
	$finish_reg_day['finish_reg_day'] = $finish_reg;
	$finish_reg_day['all_work_day'] = $finish_reg;
	
	$tpl->assign('finish_reg_day', $finish_reg_day);
	
	$tpl->display("index.demo.html");
}
else{
	$tpl->assign("link_login", "login.demo");
	$tpl->display("index.demo.html");
	//$tpl->display("auth2.html");
	//$tpl->display("welcam.html");
}

?>