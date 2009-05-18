<?
/**
 * Модуль, экспортирующий транзакции по выбранному счету в один из выбранных пользователем форматов
 *
 * @author   Евгений Панин <varenich@gmail.com> Люберцы, Россия, 2008
 * @package  home-money
 * @version  1.0
 */

// Если пользователь не авторизован, фигачим его на главную страницу
if (empty($_SESSION['user']))
{
	header("Location: index.php");
}

// Говорим, какую страницу будем показывать
$tpl->assign('name_page', 'export');


// Подключаем все необходимые библиотеки и инициируем объекты
require_once SYS_DIR_LIBS.'/ExportHandler.php';
require_once SYS_DIR_LIBS.'/money.class.php';

// Инициируем контроллер операций экспорта - объект, выполняющий всё, что связано с экспортом. Всё в нём.
try {
	$conf['account'] = $acc;
	$conf['money'] = new Money($db, $user);
	$conf['category'] = $cat;
	
	$eh = new ExportHandler($conf);
} catch (Exception $e) {
	message_error(CRITICAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
}


// Для начала выведем список счетов пользовател. Вдруг он захочет что-нибудь поэкспортировать?
$userID = $_SESSION['user']['user_id'];
try {
	$userAccounts = $eh->getUserAccounts($userID);
} catch (Exception $e) {
	if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
	message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
}
// Выводим список счетов на экран
$tpl->assign('userAccounts',$userAccounts);


// Что делать будем?
$action = html($p_action);

switch( $action )
{
	// начинать экспортировать
	case "startExport": {
		try {
			$p_exportFormat = html($p_exportFormat);
			$p_accounts = html($p_accounts);
			$p_dateFrom = html($p_dateFrom);
			$p_dateTo = html($p_dateTo);
			
			$eh->export($p_exportFormat,$userID,$p_accounts,$p_dateFrom,$p_dateTo,$p_delimiter);
			message_error(GENERAL_MESSAGE,'Данные экспортированы','','','','');
			
		} catch (Exception $e) {
			if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
			message_error(GENERAL_MESSAGE,$e->getMessage(),'',$e->getLine(),$e->getFile(),'');
		}
		break;
	}
	// Ничего не делаем
	default: {
		

		break;
	}
} // switch

if (!empty($p_accounts))
{
	$tpl->assign('accounts',$p_accounts);
}else{
	$tpl->assign('accounts',html($g_a));
}
$tpl->assign('dateFrom',$p_dateFrom);
$tpl->assign('dateTo',$p_dateTo);
$tpl->assign('delimiter',$p_delimiter);
?>