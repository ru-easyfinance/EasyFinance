<?
error_reporting(0);
/**
 * Модуль "Мастер отчетов"
 *
 * @author   Евгений Панин <varenich@gmail.com> Люберцы, Россия, 2008
 * @package  home-money
 * @version  1.0
 */

// Максимальная длина названия категории
$maxChars = 30;

// Получает текущий путь include_path и дописываем туда место, где будут лежать наши библиотеки PEAR
$cp = ini_get('include_path');
$sep = (preg_match('/WIN/',PHP_OS))?';':':';
ini_set('include_path',$cp.$sep.SYS_DIR_INC.$sep.SYS_DIR_INC_PEAR);

include_once( 'ofc-library/open-flash-chart.php' );

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

// Инициируем контроллер операций экспорта - объект, выполняющий всё, что связано с экспортом. Всё в нём.
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

// Что делать будем?
$action = (isset($g_action))?@html($g_action):@html($p_action);

$reportType = (isset($g_reportType))?@html($g_reportType):@html($p_reportType);
//pre($reportType);

// Формируем параметры отчета
$g_dateFrom = @html($g_dateFrom);
$g_dateTo = @html($g_dateTo);
$g_account = @html($g_account);
$g_currency = @html($g_currency);
$rpd = array(
	'dateFrom' => $g_dateFrom,
	'dateTo' => $g_dateTo,
	'userID' => $userID,
	// Код счета пользователя, по которому отбираются данные
	'account' => $g_account, // Не забывать проверять при выборках, чтобы счета принадлежали указанному пользователю, иначе можно будет смотреть информацию других людей
	// Курсы валют по отношению к рублю
	'currency_rates' => $sys_currency,
	// Валюта, в которой показывать суммы. Выбрана пользователем
	'currency' => $g_currency,
	// Название месяцев
	'months' => $sys_month,
);


$g = new graph();

switch ($reportType) {
	// Графический отчет о доходах
	case 'graph_profit': {
		
		try {
			// Получаем данные о доходах за период с разбивкой по категориям
			$profits = $rh->getProfit($rpd);
			//pre($profits);
		} catch (Exception $e) {
			message_error(CRITICAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
		}

		//
		// PIE chart, 60% alpha
		//
		$g->pie(60,'#505050','{font-size: 12px; color: #404040;');
		
		// Подсчитываем общую сумму
		$s = 0;
		foreach ($profits as $p) $s+=$p['sum'];
		
		//pre($s);
		$rest = 0;

		foreach ($profits as $pp)
		{
			$value = $pp['sum'];
			$key = $pp['catName'];
			// Форматируем значения до целой части
			// Не включаем в отчет величины меньше 2%
			$v = round(($value*100)/$s,1);
			if ($v>=2) {
				$data[] = $v;
				
				$putCommas = (strlen($key)>$maxChars)?true:false;
				$key = substr($key,0,$maxChars);
				if ($putCommas) $key .= '...';
				//$values[] = mb_convert_encoding($key, "UTF-8", "Windows-1251");
				$values[] = $key;
			}
			else {
				// Суммируем маленькие части, чтобы вывести их как Другое
				$rest += $v;
			}
		}
		
		if ($rest > 0) {
			// Выводим Другое
			$data[] = $rest;
			//$values[] = mb_convert_encoding("Другое", "UTF-8", "Windows-1251");
			$values[] = "Другое";
		}
		
		$g->pie_values( $data, $values );
		//
		// Colours for each slice, in this case some of the colours
		// will be re-used (3 colurs for 5 slices means the last two
		// slices will have colours colour[0] and colour[1]):
		//
		//старый вариант: $g->pie_slice_colours( array('#d01f3c','#356aa0','#C79810') );
		$g->pie_slice_colours(array('#76b900','#ff6002','#0033FF', '#9933CC', '#FF0000', '#FF8000', '#33FF99'));

		$g->set_tool_tip( '#val#%' );

		//$title = mb_convert_encoding("Доходы", "UTF-8", "Windows-1251");
		$title = "Доходы";
		$g->title( $title, '{font-size:20px; margin: 5px; padding:5px; padding-left: 20px; padding-right: 20px;}' );
		//$g->title( $title, '{font-size:20px; color: #FFFFFF; margin: 5px; background-color: #505050; padding:5px; padding-left: 20px; padding-right: 20px;}' );


		break;
	} // case
	
	// Графический отчет о расходах
	case 'graph_loss': {
		//
		// PIE chart, 60% alpha
		//
		$g->pie(60,'#505050','{font-size: 12px; color: #404040;');
		
		try {
			// Получаем данные о расходах за период с разбивкой по категориям
			//pre($rpd);
			$profits = $rh->getLoss($rpd);
			//pre($profits);
		} catch (Exception $e) {
			message_error(CRITICAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
		}

		$s = 0;
		foreach ($profits as $p) $s+=$p['sum'];
		$rest = 0;

		foreach ($profits as $pp)
		{
			$value = $pp['sum'];
			$key = $pp['catName'];
			// Форматируем значения до целой части
			// Не включаем в отчет величины меньше 2%
			$v = round(($value*100)/$s,1);
			if ($v>=2) {
				$data[] = $v;
				$key = preg_replace('/,/','',$key);
				//pre($key);
				
				$putCommas = (strlen($key)>$maxChars)?true:false;
				$key = substr($key,0,$maxChars);
				if ($putCommas) $key .= '...';
				//$values[] = mb_convert_encoding($key, "UTF-8", "Windows-1251");
				$values[] = $key;
			}
			else {
				// Суммируем маленькие части, чтобы вывести их как Другое
				$rest += $v;
			}
		}
		
		if ($rest > 0) {
			// Выводим Другое
			$data[] = $rest;
			//$values[] = mb_convert_encoding("Другое", "UTF-8", "Windows-1251");
			$values[] = "Другое";
		}
		
		$g->pie_values( $data, $values );
		
		//
		// Colours for each slice, in this case some of the colours
		// will be re-used (3 colurs for 5 slices means the last two
		// slices will have colours colour[0] and colour[1]):
		//
		//$g->pie_slice_colours( array('#d01f3c','#356aa0','#C79810') );
		$g->pie_slice_colours(array('#76b900','#ff6002','#0033FF', '#9933CC', '#FF0000', '#FF8000', '#33FF99'));

		$g->set_tool_tip( '#val#%' );

		//$title = mb_convert_encoding("Расходы", "UTF-8", "Windows-1251");
		$title = "Расходы";
		$g->title( $title, '{font-size:20px; margin: 5px; padding:5px; padding-left: 20px; padding-right: 20px;}' );
		//$g->title( $title, '{font-size:20px; color: #FFFFFF; margin: 5px; background-color: #505050; padding:5px; padding-left: 20px; padding-right: 20px;}' );
		break;
	} // case
	
	// Графический отчет о расходах и доходах
	case 'graph_profit_loss': {
		
			
		try {
			// Получаем данные о доходах за период с разбивкой по категориям
			$finData = $rh->getProfitAndLoss($rpd);
		} catch (Exception $e) {
			message_error(CRITICAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
		}

		

		// Красные столбцы - расходы по месяцам за период
		$bar_red = new bar_3d( 75, '#ff6002' );
		//$redKey = mb_convert_encoding("Расходы", "UTF-8", "Windows-1251");
		$redKey = "Расходы";
		$bar_red->key( $redKey, 10 );
	
		$bar_red->data = $finData['loss'];
		$maxLoss = max($finData['loss']);

		//
		// Синие столбцы - доходы по месяцам за период
		//
		$bar_blue = new bar_3d( 75, '#76b900' );
		//$blueKey = mb_convert_encoding("Доходы", "UTF-8", "Windows-1251");
		$blueKey = "Доходы";
		$bar_blue->key( $blueKey, 10 );
		
		$bar_blue->data = $finData['profit'];
		$maxProfit = max($finData['profit']);

		// create the graph object:
		$g = new graph();
		
		//$title = mb_convert_encoding("Сравнение расходов и доходов", "UTF-8", "Windows-1251");
		$title = "Сравнение расходов и доходов";
//		$g->title( $title, '{font-size:20px; color: #FFFFFF; margin: 5px; background-color: #505050; padding:5px; padding-left: 20px; padding-right: 20px;}' );
		$g->title( $title, '{font-size:20px; margin: 5px; padding:5px; padding-left: 20px; padding-right: 20px;}' );

		//$g->set_data( $data_1 );
		//$g->bar_3D( 75, '#D54C78', '2006', 10 );

		//$g->set_data( $data_2 );
		//$g->bar_3D( 75, '#3334AD', '2007', 10 );

		$g->data_sets[] = $bar_red;
		$g->data_sets[] = $bar_blue;

		$g->set_x_axis_3d( 12 );
		$g->x_axis_colour( '#909090', '#ADB5C7' );
		$g->y_axis_colour( '#909090', '#ADB5C7' );

		// Формируем метки оси X
		// Пример:
		// Январь,2007 Февраль,2007 ... Январь 2008 Февраль,2008
		$labels = array_keys($finData['profit']);
		// Конвертируем в UTF-8
		//foreach ($labels as $k => $v) $labels[$k] = mb_convert_encoding($v, "UTF-8", "Windows-1251");
		foreach ($labels as $k => $v) $labels[$k] = $v;
		
		$g->set_x_labels( $labels );
		// Максимальное значение по оси Y
		$g->set_y_max( max(array($maxLoss,$maxProfit))+10);
		$g->y_label_steps( 5 );

		break;
	} // case
} // switch
echo $g->render();
exit;
?>