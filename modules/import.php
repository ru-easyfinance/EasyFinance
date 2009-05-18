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



// Подключаем необходимые библиотеки и инициируем объекты
require_once SYS_DIR_LIBS.'/ImportHandler.php';
require_once SYS_DIR_LIBS.'/money.class.php';

// Инициируем контроллер операций импорта
try {
	$conf['account'] = $acc;
	$conf['money'] = new Money($db, $user);
	$conf['category'] = $cat;
	
	$ih = new ImportHandler($conf);
} catch (Exception $e) {
	message_error(CRITICAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
}


// Для начала выведем список счетов пользовател
$userID = $_SESSION['user']['user_id'];
try {
	$userAccounts = $ih->getUserAccounts($userID);
} catch (Exception $e) {
	if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
	message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
}
// Выводим список счетов на экран
$tpl->assign('userAccounts',$userAccounts);

// Делаем параметры запроса безопасными
$p_importFormat = html($p_importFormat);
$p_accountSelected = html($p_accountSelected);
$p_importParams = (isset($p_importParams))?html($p_importParams):'';
$file2Import = base64_decode($p_file2Import);
$p_impID = html($p_impID);
$p_delimiter = html($p_delimiter);

// Что делать будем?
$action = html($p_action);



switch( $action )
{
	// Показать шаг 2
	case "displayScreen2": {
		// Проверяем, загрузили файл или нет. Если не загрузили, то и не делаем ничего
		if ($_FILES['file2Import']['error'] == UPLOAD_ERR_NO_FILE) {
			message_error(GENERAL_MESSAGE,'Файл с данными для импорта не загружен. Загрузите его, пожалуйста','',0,'','');
			$action = '';
		} else {
			// Если еще какая-то ошибка с файлом произошла, то говорим об этом и тоже не производим никаких действий
			if ($_FILES['file2Import']['error'] > 0) {
				message_error(GENERAL_MESSAGE,'При загрузке файла с данными произошла ошибка. Повторите попытку снова, пожалуйста','',0,'','');
				$action = '';
			}
			else {
				// Если никаких ошибок при заказчике нет, зачитываем файл в строку, чтобы потом приделать его в форму
				$fn = $_FILES['file2Import']['tmp_name'];
				// Доп защита от гребанных хакеров
				if (!is_uploaded_file($fn) || !file_exists($fn)) {
					message_error(GENERAL_MESSAGE,'При загрузке файла произошла ошибка. Не пытайтесь повторять, пожалуйста','',0,'','');
					$action = '';
				}
				else {
					if (!preg_match('/http/',$fn)) {
						$file2Import = file_get_contents($fn);
					}
					else {
						message_error(GENERAL_MESSAGE,'При загрузке файла произошла ошибка. Не пытайтесь повторять, пожалуйста','',0,'','');
						$action = '';
					}
				}
			}
		}
		
		// Выбираем шаблон страницы для шага 2 взависимости от выбранного формата импорта
		if ($p_importFormat == 'csv') {
			// На странице выводится список полей CSV-файла. Создаем список.
			try {
				if (!$p_delimiter) $p_delimiter=';';
				$headers = $ih->getFields($file2Import,$p_delimiter);
				$tpl->assign('headers', $headers);
			} catch (Exception $e) {
				if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
				message_error(GENERAL_MESSAGE,$e->getMessage(),'',$e->getLine(),$e->getFile(),'');
			}
			// Выводим страницу
			$tpl->assign('name_page', 'import.2.csv');
		}
		else {
			// qif импортируется без параметров
			$tpl->assign('name_page', 'import.2.qif');
		}
		break;
	} // displayScreen2
	
	// Проводим импорт
	case 'startImport': {
		try {
			if ($p_delimiter) $p_importParams['delimiter'] = $p_delimiter;
			$ih->import($userID,$p_importFormat,$p_accountSelected,$file2Import,$p_importParams);
			message_error(GENERAL_MESSAGE,'Данные импортированы','','','','');
		} catch (Exception $e) {
			if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
			message_error(GENERAL_MESSAGE,$e->getMessage(),'',$e->getLine(),$e->getFile(),'');
		}
		$tpl->assign('name_page', 'import.finished');
		break;
	} // startImport
	
	// Выводим список проведенных импортов (какой импорт будем откатывать?)
	case 'rollbackImport': {
		try {
			// Получаем список импортов
			$iList = $ih->getImportsList($userID);
			$tpl->assign('iList',$iList);
		} catch (Exception $e) {
			if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
			message_error(GENERAL_MESSAGE,$e->getMessage(),'',$e->getLine(),$e->getFile(),'');
		}
		$tpl->assign('name_page', 'import.rollback');
		break;
	} // rollbackImport
	
	// Производим откат конкретного импорта
	case 'doRollback': {
		try {
			// Откатываем
			$ih->rollbackImport($p_impID,$userID);
		} catch (Exception $e) {
			if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
			message_error(GENERAL_MESSAGE,$e->getMessage(),'',$e->getLine(),$e->getFile(),'');
		}
		$tpl->assign('name_page', 'import.rollback.finished');
		break;
	} // rollbackImport
	
	// Ничего не делаем. Выводим шаг 1
	default: {
		// По умолчанию показываем первую страницу
		$tpl->assign('name_page', 'import');
		break;
	}
} // switch

$tpl->assign('accountSelected',$p_accountSelected);
$tpl->assign('importFormat',$p_importFormat);
$tpl->assign('importParams',$p_importParams);
$tpl->assign('delimiter',$p_delimiter);
$tpl->assign('file2Import',base64_encode($file2Import));
?>