<?
/**
 * Модуль "Мастер отчетов"
 *
 * @author   Евгений Панин <varenich@gmail.com> Люберцы, Россия, 2008
 * @package  home-money
 * @version  1.0
 */

// Получает текущий путь include_path и дописываем туда место, где будут лежать наши библиотеки PEAR
$cp = ini_get('include_path');
$sep = (preg_match('/WIN/',PHP_OS))?';':':';
ini_set('include_path',$cp.$sep.SYS_DIR_INC.$sep.SYS_DIR_INC_PEAR);
//$cp = ini_get('include_path');
//echo "-> $cp <-";

// Если пользователь не авторизован, фигачим его на главную страницу
if (empty($_SESSION['user']))
{
	header("Location: index.php");
}

// Говорим, какую страницу будем показывать
$tpl->assign('name_page', 'report_wizard');
$tpl->assign('reportTpl','default');

// Подключаем все необходимые библиотеки и инициируем объекты
require_once SYS_DIR_LIBS.'/ReportHandler.php';
require_once SYS_DIR_LIBS.'/money.class.php';

// Инициируем контроллер - объект, выполняющий всё, что связано с отчетами. Всё в нём.
try {
	$conf['account'] = $acc;
	$conf['money'] = new Money($db, $user);
	$conf['category'] = $cat;
	
	$rh = new ReportHandler($conf);
} catch (Exception $e) {
	message_error(CRITICAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
}


// Для начала выведем список счетов пользовател. Вдруг он захочет что-нибудь поэкспортировать?
$userID = $_SESSION['user']['user_id'];

// Получаем полный список категорий и их родителей
try {
	$catPaths = $rh->getCategoryPaths($userID);
	$tpl->assign('parents',$catPaths);
	//pre($catPaths);
}
catch (Exception $e) {
	if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
	message_error(GENERAL_MESSAGE,$e->getMessage(),'',0,'','');
}

// Выводим список счетов пользователя, показывающийся на всех отчетах
try {
	$userAccounts = $rh->getUserAccounts($userID);
} catch (Exception $e) {
	if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
	message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
}
// Выводим список счетов на экран
$tpl->assign('userAccounts',$userAccounts);


// Что делать будем?
$action = (isset($g_action))?@html($g_action):@html($p_action);
$reportType = (isset($g_reportType))?@html($g_reportType):@html($p_reportType);
//pre($reportType);

// Формируем параметры отчета
$p_account = @html($p_account);
$p_dateFrom = @html($p_dateFrom);
$p_dateTo = @html($p_dateTo);
$p_dateFrom2 = @html($p_dateFrom2);
$p_dateTo2 = @html($p_dateTo2);
$p_currency = @html($p_currency);
$rpd = array(
	// Начало диапазона отбора
	'dateFrom' => $p_dateFrom,
	// Окончание диапазона отбора
	'dateTo' => $p_dateTo,
	// Начало диапазона отбора 2
	'dateFrom2' => $p_dateFrom2,
	// Окончание диапазона отбора 2
	'dateTo2' => $p_dateTo2,
	// Код пользователя
	'userID' => $userID,
	// Код счета пользователя, по которому отбираются данные
	'account' => $p_account, // Не забывать проверять при выборках, чтобы счета принадлежали указанному пользователю, иначе можно будет смотреть информацию других людей
	// Курсы валют по отношению к рублю
	'currency_rates' => $sys_currency,
	// Валюта, в которой показывать суммы. Выбрана пользователем
	'currency' => $p_currency,
	// Название месяцев
	'months' => $sys_month,
);

switch( $action )
{
	// Показать оформление выбранного пользователем отчета
	case "display_report": {
		try {
			// Смотрим, есть ли выбранный отчет в списке разрешенных. Защита от долбанных хакеров
			if (in_array($reportType,array('graph_profit','graph_loss','graph_profit_loss'))) {
				// Вывод оформления графического отчета
				
				// Если установлена галка "Не использоваться Flash", подключаем шаблон отчета в виде картинки
				$p_plainGraph = @html($p_plainGraph);
				if ($p_plainGraph) {
					$tpl->assign('reportTpl','graph');
				} else {
					// Иначе подключаем шаблон Flash-отчета
					$tpl->assign('reportTpl','flash');
					
					// В flash-отчете выводится не только графика, но и таблица с данными - получаем ее

					switch ($reportType) {
						// Графический отчет о доходах
						case 'graph_profit': {
							try {
								// Получаем данные о доходах за период с разбивкой по категориям, чтобы вывести их в таблице под отчетом
								$res = $rh->getProfit($rpd);
								$tpl->assign('result',$res);
								
							} catch (Exception $e) {
								if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
								message_error(GENERAL_MESSAGE,$e->getMessage(),'',0,'','');
							}
							break;
						} // caseъ
						// Графический отчет о расходах
						case 'graph_loss': {
							try {
								// Получаем данные о расходах за период с разбивкой по категориям, чтобы вывести их в таблице под отчетом
								$res = $rh->getLoss($rpd);
								$tpl->assign('result',$res);
								
							} catch (Exception $e) {
								if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
								message_error(GENERAL_MESSAGE,$e->getMessage(),'',0,'','');
							}
							break;
						} // case
						// Графический отчет сравнения доходов и расходов
						case 'graph_profit_loss': {
								// В сравнении доходов и расходов нет таблицы с данными, поэтому выставляем признак, что показывать её не надо
								$tpl->assign('doNotShowResult',1);
								try {
								// Получаем данные о расходах за период с разбивкой по категориям, чтобы вывести их в таблице под отчетом
								$res = $rh->getProfitAndLoss($rpd);
								//$tpl->assign('result',$res);
								
								// Если не выставить хоть какойто результат, страница выведет надпись "Нет данных", поэтому назначаем что-нибудь
								$tpl->assign('result',1);
								
							} catch (Exception $e) {
								if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
								message_error(GENERAL_MESSAGE,$e->getMessage(),'',0,'','');
							}
							break;
						} // case
					} // switch

					
				} // if p_plainGraph
			}
			elseif (in_array($reportType,array('txt_profit','txt_loss','txt_loss_difference','txt_profit_difference','txt_profit_avg_difference','txt_loss_avg_difference'))) {
				// Вывод текстового отчета
				switch ($reportType) {
					// Графический отчет о доходах
					case 'txt_profit': {
						try {
							// Получаем данные о доходах за период с разбивкой по категориям
							$res = $rh->getDetailedProfit($rpd);
							//pre($res);
							$tpl->assign('result',$res);
							$tpl->assign('profit',1);
							$tpl->assign('reportTpl','txt');
						} catch (Exception $e) {
							if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
							message_error(GENERAL_MESSAGE,$e->getMessage(),'',0,'','');
						}
						break;
					} // caseъ
					// Графический отчет о расходах
					case 'txt_loss': {
						try {
							// Получаем данные о расходах за период с разбивкой по категориям
							$res = $rh->getDetailedLoss($rpd);
							$tpl->assign('result',$res);
							$tpl->assign('reportTpl','txt');
						} catch (Exception $e) {
							if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
							message_error(GENERAL_MESSAGE,$e->getMessage(),'',0,'','');
						}
						break;
					} // case
					// Графический отчет сравнения доходов и расходов
					case 'txt_loss_difference': {
						try {
							// Получаем данные сравнение расходов за период с разбивкой по категориям
							$res = $rh->getLossDifference($rpd);
							$tpl->assign('result',$res);
							$tpl->assign('reportTpl','txt_difference');
						} catch (Exception $e) {
							if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
							message_error(GENERAL_MESSAGE,$e->getMessage(),'',0,'','');
						}
						break;
					} // case
					case 'txt_profit_difference': {
						try {
							// Получаем данные сравнение доходов за период с разбивкой по категориям
							$res = $rh->getProfitDifference($rpd);
							$tpl->assign('result',$res);
							$tpl->assign('profit',1);
							$tpl->assign('reportTpl','txt_difference');
						} catch (Exception $e) {
							if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
							message_error(GENERAL_MESSAGE,$e->getMessage(),'',0,'','');
						}
						break;
					} // case
					case 'txt_profit_avg_difference': {
						try {
							// Получаем данные сравнение доходов со средним за период с разбивкой по категориям
							$res = $rh->getProfitAvgDifference($rpd);
							$tpl->assign('result',$res);
							$tpl->assign('profit_avg',1);
							$tpl->assign('reportTpl','txt_difference');
						} catch (Exception $e) {
							if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
							message_error(GENERAL_MESSAGE,$e->getMessage(),'',0,'','');
						}
						break;
					} // case
					case 'txt_loss_avg_difference': {
						try {
							// Получаем данные сравнение расходов со средним за период с разбивкой по категориям
							$res = $rh->getLossAvgDifference($rpd);
							$tpl->assign('result',$res);
							$tpl->assign('loss_avg',1);
							$tpl->assign('reportTpl','txt_difference');
						} catch (Exception $e) {
							if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
							message_error(GENERAL_MESSAGE,$e->getMessage(),'',0,'','');
						}
						break;
					} // case
				} // switch
			}
			else {
				message_error(GENERAL_MESSAGE,'Указан неверный вид отчета','',0,'','');
				$action = '';
			}
			
			
		} catch (Exception $e) {
			if ($e->getCode()==1) message_error(GENERAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
			message_error(GENERAL_MESSAGE,$e->getMessage(),'',$e->getLine(),$e->getFile(),'');
		}
		break;
	} // case
	// Показать граф без вывода оформления (чисто картинку)
	case "show_graph": {
		try {
			if (in_array($reportType,array('graph_profit'))) {
				// Вывод графического отчета
				// Строим отчет
				$img = $rh->displayGraphReport($reportType,$rpd);
			}
			else {
				$img = file_get_contents('./img/failed.jpg');
			}
		} catch (Exception $e) {
			$img = file_get_contents('./img/failed.jpg');
		}
		exit;
		break;
	} // case
	// Ничего не делаем
	default: {
		

		break;
	}
} // switch

if (empty($p_dateFrom)) $p_dateFrom = date("01.m.Y");
if (empty($p_dateTo)) $p_dateTo = date("d.m.Y");
if (empty($p_dateFrom2)) $p_dateFrom2 = date("01.m.Y", strtotime("-1 month", strtotime(date("d.m.Y"))));
if (empty($p_dateTo2)) $p_dateTo2 = date("d.m.Y", strtotime("-1 day", strtotime(date("01.m.Y"))));


$tpl->assign('dateFrom',$p_dateFrom);
$tpl->assign('dateTo',$p_dateTo);
$tpl->assign('dateFrom2',$p_dateFrom2);
$tpl->assign('dateTo2',$p_dateTo2);
$tpl->assign('account',$p_account);
$tpl->assign('currency',$p_currency);
$tpl->assign('reportType',$reportType);
// Берется из системного конфига
$tpl->assign('currencies',$sys_currency_name);
$tpl->assign('reports',$sys_reports);
?>