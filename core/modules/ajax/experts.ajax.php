<?php
/**
 * Модуль системы экспертов для аякса
 */
 
// подключаем все необходимые библиотеки
require_once (SYS_DIR_LIBS . "classes/hmExpertSystem.class.php");
require_once (SYS_DIR_LIBS . "external/DBSimple/Mysql.php");

// если пользователь не авторизован, фигачим его на главную страницу
if (empty($_SESSION['user'])) {
    header("Location: index.php");
}

// получаем действие
$action = html($g_action);

switch ($action)
{
	case "voice_rank":
		if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') exit;
		
		$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		$exps = new hmExpertSystem(&$dbs);
		
		$id = html($g_id);
		$exp_id = html($g_exp_id);
		$q_id = html($g_q_id);
		
		if ($exps->VoiceRank($id, $exp_id, $q_id, $_SESSION['user']['user_id']))
		{
			echo "Спасибо за оценку!";
		}else{
			echo "Ошибка: оценка не установлена!";
		}
		
		exit;
		break;
	default:
		break;
}
exit;
?>